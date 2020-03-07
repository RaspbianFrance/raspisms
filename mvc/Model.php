<?php
	/**
	 * Cette classe sert de mère à tous les modèles, elle permet de gérer l'ensemble des fonction necessaires aux requetes en base de données
	 * @param $bdd : Une instance de PDO
	 */
	class Model
	{
		//Les constantes des différents types de retours possibles
		const NO = 0; //Pas de retour
		const FETCH = 1; //Retour de type fetch
		const FETCHALL = 2; //Retour de type fetchall
		const ROWCOUNT = 3; //Retour de type rowCount()

		protected $bdd; //L'instance de PDO à employer	
	
		public function __construct(PDO $bdd)
		{
			//Si $bdd est bien une instance de PDO
			$this->bdd = $bdd;
		}

		public function getBdd()
		{
			return $this->bdd;	
		}

		public function setBdd(PDO $bdd)
		{
			$this->bdd = $bdd;
		}

		/*
			Fonctions relatives aux informations de la base
		*/

		/**
		* Cette fonction vérifie si une table existe
		* @param string $table : Nom de la table
		* @return mixed : Vrai si la table existe, faux sinon
		*/
		public function tableExist($table)
		{
				$tables = $this->getAllTables();
				return in_array($table, $tables);
		}

		/**
		* Cette fonction vérifie si un champs existe dans une table
		* @param string $field : Nom du champ
		* @param string $table : Nom de la table
		* @return mixed : Vrai si le champs existe, faux, si le champs ou la table n'existe pas
		*/
		public function fieldExist($field, $table)
		{
			$fields = $this->getColumnsForTable($table);	
			$fields = $fields ? explode(', ', $fields) : array();
			return in_array($field, $fields);
		}

		/**
		 * Cette requete retourne le dernier id inséré
		 * return int : le dernier id inséré
		 */
		public function lastId()
		{
			return	$this->bdd->lastInsertId();
		}

		/**
		 * Cette fonction permet de retourner toutes les tables de la base
		 * @return array : La liste des tables
		 */
		public function getAllTables()
		{
			$query = 'SHOW TABLES';
			$tables = $this->runQuery($query);
			$tablesNames = array();

			foreach ($tables as $table)
			{
				$tablesNames[] = array_values($table)[0];
			}

			return $tablesNames;
		}

		/**
		 * Cette fonction permet de récupérer la liste de toutes les colonnes d'une table de façon propre, appelable via MySQL. Cela permet de faire des requetes plus optimisée qu'avec "*"
		 * @param string $table : Nom de la table pour laquelle on veux les colonnes
		 * @param string $prefix : Le prefix que l'on veux devant les champs, utile pour les requetes avec jointures. Par défaut null => pas de prefix. A noter, en cas d'utilisation de prefix, les champs aurons un alias de la forme $prefix_$nom_champ
		 * @return boolean string : Tous les noms des colonnes liées par des ", ". Ex : 'id, nom, prenom". Si la table n'existe pas, on retourne false.
		 */
		public function getColumnsForTable($table, $prefix = null)
		{
			if ($this->tableExist($table))
			{
				$query = 'SHOW COLUMNS FROM ' . $table;
				
				$datas = array();
				
				$fields = $this->runQuery($query, $datas, self::FETCHALL);
				$fieldsName = array();
				foreach ($fields as $field)
				{
					$fieldsName[] = $prefix ? $prefix . '.' . $field['Field'] . ' AS ' . $prefix . '_' . $field['Field'] : $field['Field'];
				}

				return implode(', ', $fieldsName);
			}

			return false;
		}

		/**
		 * Cette fonction décrit une table et retourne un tableau sur cette description
		 * @param string $table : Le nom de la table a analyser
		 * @return mixed : Si la table existe un tableau la décrivant, sinon false
		 */
		public function describeTable($table)
		{
			if (!$this->tableExist($table))
			{
				return false;
			}

			//On recupere tous les champs pour pouvoir apres les analyser
			$query = 'DESCRIBE ' . $table;
			$fields = $this->runQuery($query);

			$return = array();
			foreach ($fields as $field)
			{
				$fieldInfo = array();
				$fieldInfo['NAME'] = $field['Field'];
				$fieldInfo['NULL'] = $field['Null'] == 'NO' ? false : true;
				$fieldInfo['AUTO_INCREMENT'] = $field['Extra'] == 'auto_increment' ? true : false;
				$fieldInfo['PRIMARY'] = $field['Key'] == 'PRI' ? true : false;
				$fieldInfo['FOREIGN'] = $field['Key'] == 'MUL' ? true : false;
				$fieldInfo['UNIQUE'] = $field['Key'] == 'UNI' ? true : false;
				$fieldInfo['TYPE'] = mb_convert_case(preg_replace('#[^a-z]#ui', '', $field['Type']), MB_CASE_UPPER);
				$fieldInfo['SIZE'] = filter_var($field['Type'], FILTER_SANITIZE_NUMBER_INT);
				$fieldInfo['HAS_DEFAULT'] = $field['Default'] !== NULL ? true : false;
				$fieldInfo['DEFAULT'] = $field['Default'];
				$return[$field['Field']] = $fieldInfo;
			}

			return $return;
		}

		/**
		 * Cette finction retourne la table et le champs référent pour un champ avec une foreign key
		 * @param string $table : Le nom de la table qui contient le champ
		 * @param string $field : Le nom du champ
		 * @return mixed : False en cas d'erreur, un tableau avec 'table' en index pour la table et 'field' pour le champ
		 */
		public function getReferenceForForeign ($table, $field)
		{
			if (!$this->fieldExist($field, $table))
			{
				return false;
			}

			$query = 'SELECT referenced_table_name as table_name, referenced_column_name as field_name FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE table_name = :table AND column_name = :field AND referenced_table_name IS NOT NULL';
			
			$params = array(
				'table' => $table,
				'field' => $field,
			);

			return $this->runQuery($query, $params, self::FETCH);
		}

		/**
		 * Cette fonction retourne les valeurs possibles pour un champ muni d'une clef étrangère
		 * @param string $table : Le nom de la table qui contient le champ
		 * @param string $field : Le nom du champ
		 * @return mixed : Retourne les valeurs possible sous forme d'un tableau
		 */
		public function getPossibleValuesForForeign ($table, $field)
		{
			if (!$this->fieldExist($field, $table))
			{
				return false;
			}

			//On recupère le champs référence pour la foreign key
			if (!$reference = $this->getReferenceForForeign($table, $field))
			{
				return false;
			}

			//On recupère les valeurs possible de la table
			$query = 'SELECT DISTINCT ' . $reference['field_name'] . ' as possible_value FROM ' . $reference['table_name'];
			return $this->runQuery($query);
		}

		/**
		 * Cette fonction permet de compter le nombre de ligne d'une table
		 * @param string $table : Le nom de la table à compter
		 * @return mixed : Le nombre de ligne dans la table ou false si la table n'existe pas
		 */
		public function countTable ($table)
		{
			if (!$this->tableExist($table))
			{
				return false;
			}

			$query = "SELECT COUNT(*) as nb_lignes FROM " . $table;
			
			$return = $this->runQuery($query, array(), self::FETCH);
			return $return['nb_lignes'];
		}	 

		/*
			Fonctions d'execution des requetes ou de génération
		*/

		/**
		 * Cette fonction joue une requete depuis une requete et un tableau d'argument
		 * @param string $query : Requete à jouer
		 * @param array $datas : Les données pour la requete. Si non fourni, vide par défaut.
		 * @param const $return_type : Type de retour à utiliser. (Voir les constantes de la classe Model ici présente). Par défaut FETCHALL
		 * @param const $fetch_mode : Le type de récupération a effectuer. Par défaut FETCH_ASSOC
		 * @param boolean $debug : Par défaut à faux, si vrai retourne les infos de débug de la requete
		 * @return mixed : Dépend du type spécifié dans $return_type
		 */
		public function runQuery($query, $datas = array(), $return_type = self::FETCHALL, $fetch_mode = PDO::FETCH_ASSOC, $debug = false)
		{
			$req = $this->bdd->prepare($query);
			$req->setFetchMode($return_type);
			$req->execute($datas);

			if ($debug)
			{
				return $req->errorInfo();
			}

			switch ($return_type)
			{
				case self::NO :
					$return = NULL;
					break; 

				case self::FETCH :
					$return = $req->fetch();
					break; 

				case self::FETCHALL :
					$return = $req->fetchAll();
					break; 
				
				case self::ROWCOUNT : 
					$return = $req->rowCount();
					break;
			
				default : //Par défaut on récupère via fetchAll
					$return = $req->fetchAll();
			}

			return $return;
		}
		
		/**
		* Cette fonction permet de récupérer les éléments necessaires à une requete 'IN' depuis un tableau php
		* @param string $values : Tableau PHP des valeurs
		* @return array : Tableau des éléments nécessaires ('QUERY' => clause 'IN(...)' à ajouter à la query. 'DATAS' => tableau des valeurs à ajouter à celles passées en paramètre à l'execution de la requete
		*/
		public function generateInFromArray($values)
		{
			$return = array(
				'QUERY' => '',
				'PARAMS' => array(),
			);
			
			$flags = array();

			$values = count($values) ? $values : array();
			
			foreach ($values as $clef => $value)
			{
				$return['PARAMS']['in_value_' . $clef] = $value;
				$flags[] = ':in_value_' . $clef;
			}		
				
			$return['QUERY'] .= ' IN(' . implode(', ', $flags) . ')';
			return $return;
		}


		/*
			Fonctions de manipulations basiques des données
		*/

		/**
		 * Cette fonction permet de récupérer des lignes en fonction de restrictions
		 * @param string $table : Le nom de la table dans laquelle on veux recuperer la ligne
		 * @param array $restrictions : Les restrictions sous la forme "label" => "valeur". Un operateur '<, >, <=, >=, !' peux précder le label pour modifier l'opérateur par défaut (=)
		 * @param mixed $order_by : Le nom de la colonne par laquelle on veux trier les résultats ou son numero. Si non fourni, tri automatique
		 * @param string $desc : L'ordre de tri (asc ou desc). Si non défini, ordre par défaut (ASC)
		 * @param string $limit : Le nombre maximum de résultats à récupérer (par défaut pas le limite)
		 * @param string $offset : Le nombre de résultats à ignorer (par défaut pas de résultats ignorés)
		 * @return mixed : False en cas d'erreur, sinon les lignes retournées
		 */
		public function getFromTableWhere($table, $restrictions = array(), $order_by = '', $desc = false, $limit = false, $offset = false)
		{
			$restrictions = !is_array($restrictions) ? array() : $restrictions;

			$fields = $this->describeTable($table);
			if (!$fields)
			{
				return false;
			}

			//On gère les restrictions
			$wheres = array();
			$params = array();
			$i = 0;
			foreach ($restrictions as $label => $value)
			{
				//Pour chaque restriction, on essaye de detecter un "! ou < ou > ou <= ou >="
				$first_char = mb_substr($label, 0, 1);
				$second_char = mb_substr($label, 1, 1);

				switch(true)
				{
					//Important de traiter <= & >= avant < & >
					case ('<=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '<=';
						break;

					case ('>=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '>=';
						break;

					case ('!' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '!=';
						break;

					case ('<' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '<';
						break;

					case ('>' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '>';
						break;

					default :
						$trueLabel = $label;
						$operator = '=';
				}

				//Si le champs pour la restriction n'existe pas on retourne false
				if (!array_key_exists($trueLabel, $fields))
				{
					return false;
				}

				//On ajoute la restriction au WHERE
				$params['where_' . $trueLabel . $i] = $value;
				$wheres[] = $trueLabel . ' ' . $operator . ' :where_' . $trueLabel . $i . ' ';
				$i++;
			}

			$query = "SELECT * FROM " . $table . " WHERE 1 " . (count($wheres) ? 'AND ' : '') . implode('AND ', $wheres);

			if ($order_by)
			{
				//Si le champs existe ou si c'est un numeric inférieur ou egale au nombre  de champs dispo
				if (array_key_exists($order_by, $fields) || (is_numeric($order_by) && $order_by <= count($fields)))
				{
					$query .= ' ORDER BY ' . $order_by;
					if ($desc) 
					{
						$query .= ' DESC';
					}
				}
			}

			if ($limit !== false)
			{
				$query .= ' LIMIT :limit';
				if ($offset !== false)
				{
					$query .= ' OFFSET :offset';
				}
			}

			$req = $this->bdd->prepare($query);

			if ($limit !== false)
			{
				$limit = (int)$limit;
				$req->bindParam(':limit', $limit, PDO::PARAM_INT);
				if ($offset !== false)
				{
					$offset = (int)$offset;
					$req->bindParam(':offset', $offset, PDO::PARAM_INT);
				}
			}

			//On associe les paramètres
			foreach ($params as $label => &$param)
			{
				$req->bindParam(':' . $label, $param);
			}

			$req->setFetchMode(PDO::FETCH_ASSOC);
			$req->execute();

			return $req->fetchAll();
		}

		/**
		 * Cette fonction permet de modifier les données d'une table pour un la clef primaire
		 * @param string $table : Le nom de la table dans laquelle on veux insérer des données
		 * @param string $primary : La clef primaire qui sert à identifier la ligne a modifier
		 * @param array $datas : Les données à insérer au format "champ" => "valeur"
		 * @param array $restrictions : Les restrictions pour la mise à jour sous la forme "label" => "valeur". Un operateur '<, >, <=, >=, !' peux précder le label pour modifier l'opérateur par défaut (=)
		 * @return mixed : False en cas d'erreur, sinon le nombre de lignes modifiées
		 */
		public function updateTableWhere ($table, $datas, $restrictions = array())
		{
			$fields = $this->describeTable($table);
			if (!$fields)
			{
				return false;
			}
			
			$params = array();
			$sets = array();

			//On gère les set
			foreach ($datas as $label => $value)
			{
				//Si le champs pour la nouvelle valeur n'existe pas on retourne false
				if (!array_key_exists($label, $fields))
				{
					return false;
				}
				
				//Si le champs est Nullable est qu'on à reçu une chaine vide, on passe à null plutot qu'à chaine vide
				if ($fields[$label]['NULL'] && $value === '')
				{
					$value = null;
				}

				$params['set_' . $label] = $value;
				$sets[] = $label . ' = :set_' . $label . ' ';
			}

			//On gère les restrictions
			$wheres = array();
			$i = 0;
			foreach ($restrictions as $label => $value)
			{
				//Pour chaque restriction, on essaye de detecter un "! ou < ou > ou <= ou >="
				$first_char = mb_substr($label, 0, 1);
				$second_char = mb_substr($label, 1, 1);

				switch(true)
				{
					//Important de traiter <= & >= avant < & >
					case ('<=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '<=';
						break;

					case ('>=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '>=';
						break;

					case ('!' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '!=';
						break;

					case ('<' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '<';
						break;

					case ('>' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '>';
						break;

					default :
						$trueLabel = $label;
						$operator = '=';
				}

				//Si le champs pour la restriction n'existe pas on retourne false
				if (!array_key_exists($trueLabel, $fields))
				{
					return false;
				}

				//On ajoute la restriction au WHERE
				$params['where_' . $trueLabel . $i] = $value;
				$wheres[] = $trueLabel . ' ' . $operator . ' :where_' . $trueLabel . $i . ' ';
				$i++;
			}

			//On fabrique la requete
			$query = "UPDATE " . $table . " SET " . implode(', ', $sets) . " WHERE 1 AND " . implode('AND ', $wheres);

			//On retourne le nombre de lignes insérées
			return $this->runQuery($query, $params, self::ROWCOUNT);
		}

		/**
		 * Cette fonction permet de supprimer des lignes d'une table en fonctions de restrictions
		 * @param string $table : Le nom de la table dans laquelle on veux supprimer la ligne
		 * @param array $restrictions : Les restrictions pour la suppression sous la forme "label" => "valeur". Un operateur '<, >, <=, >=, !' peux précder le label pour modifier l'opérateur par défaut (=)
		 * @return mixed : False en cas d'erreur, sinon le nombre de lignes supprimées
		 */
		public function deleteFromTableWhere($table, $restrictions = array())
		{

			$fields = $this->describeTable($table);
			if (!$fields)
			{
				return false;
			}

			//On gère les restrictions
			$wheres = array();
			$params = array();
			$i = 0;
			foreach ($restrictions as $label => $value)
			{
				//Pour chaque restriction, on essaye de detecter un "! ou < ou > ou <= ou >="
				$first_char = mb_substr($label, 0, 1);
				$second_char = mb_substr($label, 1, 1);

				switch(true)
				{
					//Important de traiter <= & >= avant < & >
					case ('<=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '<=';
						break;

					case ('>=' == $first_char . $second_char) :
						$trueLabel = mb_substr($label, 2);
						$operator = '>=';
						break;

					case ('!' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '!=';
						break;

					case ('<' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '<';
						break;

					case ('>' == $first_char) :
						$trueLabel = mb_substr($label, 1);
						$operator = '>';
						break;

					default :
						$trueLabel = $label;
						$operator = '=';
				}

				//Si le champs pour la restriction n'existe pas on retourne false
				if (!array_key_exists($trueLabel, $fields))
				{
					return false;
				}

				//On ajoute la restriction au WHERE
				$params['where_' . $trueLabel . $i] = $value;
				$wheres[] = $trueLabel . ' ' . $operator . ' :where_' . $trueLabel . $i . ' ';
				$i++;
			}

			$query = "DELETE FROM " . $table . " WHERE 1 AND " . implode('AND ', $wheres);
			return $this->runQuery($query, $params, self::ROWCOUNT);
		}

		/**
		 * Cette fonction permet d'insérer des données dans une table
		 * @param string $table : Le nom de la table dans laquelle on veux insérer des données
		 * @param array $datas : Les données à insérer
		 * @return mixed : False en cas d'erreur, et le nombre de lignes insérées sinon
		 */
		public function insertIntoTable($table, $datas)
		{
			$fields = $this->describeTable($table);
			if (!$fields)
			{
				return false;
			}
			
			$params = array();
			$fieldNames = array();

			//On s'assure davoir toutes les données, on evite les auto increment, on casse en cas de donnée absente
			foreach ($fields as $nom => $field)
			{
				if ($field['AUTO_INCREMENT'])
				{
					continue;
				}

				//Si il manque un champs qui peux être NULL ou qu'il est rempli avec une chaine vide ou null, on passe au suivant				
				if ((!isset($datas[$nom]) || $datas[$nom] === NULL || $datas[$nom] === '') && $field['NULL'])
				{
					continue;
				}
				
				//Si il manque un champs qui a une valeur par défaut
				if (!isset($datas[$nom]) && $field['HAS_DEFAULT'])
				{
					continue;
				}

				//Si il nous manque un champs
				if (!isset($datas[$nom]))
				{
					return false;
				}

				//Gestion des booléan à false 
				if ($field['TYPE'] == "TINYINT"){
					if ($datas[$nom] == false)
						$params[$nom] = 0;
					else 
						$params[$nom] = 1;
				}else{
					$params[$nom] = $datas[$nom];
				}
				$fieldNames[] = $nom;
			}

			//On fabrique la requete
			$query = "INSERT INTO " . $table . "(" . implode(', ', $fieldNames) . ") VALUES(:" . implode(', :', $fieldNames) . ")";

			//On retourne le nombre de lignes insérées
			return $this->runQuery($query, $params, self::ROWCOUNT);
		}

	} 
