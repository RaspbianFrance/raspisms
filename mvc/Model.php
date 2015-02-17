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

		/**
		 * Cette fonction joue une requete depuis une requete et un tableau d'argument
		 * @param string $query : Requete à jouer
		 * @param array $datas : Les données pour la requete. Si non fourni, vide par défaut.
		 * @param const $return_type : Type de retour à utiliser. (Voir les constantes de la classe Model ici présente). Par défaut FETCHALL
		 * @param const $fetch_mode : Le type de récupération a effectuer. Par défaut FETCH_ASSOC
		 * @return mixed : Dépend du type spécifié dans $return_type
		 */
		public function runQuery($query, $datas = array(), $return_type = self::FETCHALL, $fetch_mode = PDO::FETCH_ASSOC)
		{
			$req = $this->bdd->prepare($query);
			$req->setFetchMode($return_type);
			$req->execute($datas);

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
		* Cette fonction vérifie si une table existe
		* @param string $table : Nom de la table
		* @return mixed : Vrai si la table existe, faux sinon
		*/
		public function tableExist($table)
		{
				$query = '
					SHOW TABLES LIKE :table
				';
				
				$query_datas = array(
					'table' => $table
				);
				
				$req = $this->bdd->prepare($query);
				$req->execute($query_datas);
				$result = $req->fetch();
				if(count($result))
				{
					return true;
				}

				return false;
		}

		/**
		* Cette fonction vérifie si un champs existe dans une table
		* @param string $field : Nom du champ
		* @param string $table : Nom de la table
		* @return mixed : Vrai si le champs existe, faux, si le champs ou la table n'existe pas
		*/
		public function fieldExist($field, $table)
		{
			if($this->tableExist($table))
			{
				$query = '
					SHOW COLUMNS FROM ' . $table . ' LIKE :field
				';
				
				$query_datas = array(
					'field' => $field
				);
				
				$req = $this->bdd->prepare($query);
				$req->execute($query_datas);
				$result = $req->fetch();
				if(count($result))
				{
					return true;
				}
			}

			return false;
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

		/**
		 * Cette requete retourne le dernier id inséré
		 * return int : le dernier id inséré
		 */
		public function lastId()
		{
			return	$this->bdd->lastInsertId();
		}

		/**
		 * Cette fonction permet de récupérer la liste de toutes les colonnes d'une table de façon propre, appelable via MySQL. Cela permet de faire des requetes plus optimisée qu'avec "*"
		 * @param string $table : Nom de la table pour laquelle on veux les colonnes
		 * @return boolean string : Tous les noms des colonnes liées par des ", ". Ex : 'id, nom, prenom". Si la table n'existe pas, on retourne false.
		 */
		public function getColumnsForTable($table)
		{
			if ($this->tableExist($table))
			{
				$query = 'SHOW COLUMNS FROM ' . $table;
				
				$datas = array();
				
				$fields = $this->runQuery($query, $datas, self::FETCHALL);
				$fieldsName = array();
				foreach ($fields as $field)
				{
					$fieldsName[] = $field['Field'];
				}

				return implode(', ', $fieldsName);
			}

			return false;
		}

		/**
		 * Cette fonction permet de récupérer une table complète, éventuellement en la triant par une colonne, éventuellement en limitant le nombre de résultat, ou en sautant certains (notamment pour de la pagination)
		 * @param string $table : Le nom de la table a récupérer
		 * @param string $order_by : Le nom de la colonne par laquelle on veux trier les résultats. Si non fourni, tri automatique
		 * @param string $desc : L'ordre de tri (asc ou desc). Si non défini, ordre par défaut (ASC)
		 * @param string $limit : Le nombre maximum de résultats à récupérer (par défaut pas le limite)
		 * @param string $offset : Le nombre de résultats à ignorer (par défaut pas de résultats ignorés)
		 * @return array : Tableau avec dans chaque case une ligne de la base
		 */
		public function getAll($table, $order_by = '', $desc = false, $limit = false, $offset = false)
		{
			if ($this->tableExist($table))
			{
				$query = "SELECT " . $this->getColumnsForTable($table) . " FROM " . $table;

				if ($order_by)
				{
					if ($this->fieldExist($order_by, $table))
					{
						$query .= ' ORDER BY '. $order_by;
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
					$req->bindParam(':limit', $limit, PDO::PARAM_INT);
					if ($offset !== false)
					{
						$req->bindParam(':offset', $offset, PDO::PARAM_INT);
					}
				}

				$req->setFetchMode(PDO::FETCH_ASSOC);
				$req->execute();
				return $req->fetchAll();
			}
		}
	} 
