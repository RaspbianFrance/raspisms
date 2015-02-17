<?php
	/**
	 * Cette classe gère l'appel des ressources
	 */
	class Router
	{
		public $route; //Route actuelle
		public $controllerName; //Nom du controleur obtenu depuis la route de création
		public $methodName; //Nom de la méthode obtenu depuis la route de création
		public $params; //Tableau des paramètres GET passé dans la route

		public function __construct($route = '')
		{
			if ($route)
			{
				$this->route = $route;
				$this->controllerName = $this->parseController($route);
				$this->methodName = $this->parseMethod($route);
				$this->params = $this->parseParams($route);
			}
			else
			{
				$this->route = '';
				$this->controllerName = '';
				$this->methodName = '';
				$this->params = '';
			}
		}

		//Getters et setters
		public function getRoute()
		{
			return $this->route;
		}

		public function setRoute($value)
		{
			$this->route = $route;
		}

		public function getControllerName()
		{
			return $this->controllerName;
		}

		public function setControllerName($value)
		{
			$this->controllerName = $value;
		}

		public function getMethodName()
		{
			return $this->methodName;
		}

		public function setMethodName($value)
		{
			$this->methodName = $value;
		}

		public function getParams()
		{
			return $this->params;
		}

		public function setParams($value)
		{
			$this->params = $value;
		}

		/**
		 * Retourne une route avec seulement l'url parsée comme il faut
		 * @param string $route : La route à analyser
		 * @return array : Le tableau de la route parsée
		 */
		public function parseRoute($route)
		{
			$directory_to_remove = strstr(preg_replace('#http(s)?://#', '', HTTP_PWD), '/'); //On retire la partie protocole, et l'adresse du serveur de la partie de la route à ignorer
			$route = mb_strcut($route, mb_strlen($directory_to_remove)); //On retire la partie à ignorer de la route

			$route = preg_split('#[/?]#', $route); //On explose la route

			foreach($route as $key => $val) //On garde seulement les repertoires non vides
			{
				if(empty($val))
				{
					unset($route[$key]);
				}
			}

			$route = array_values($route); //On remet à 0 les index pour obtenir un tableau propre
			return $route;
		}

		/**
		 * Retrouve le controlleur dans un URL
		 * @param string $route : la route à analyser
		 * @return string : Le nom du controleur
		 */
		public function parseController($route)
		{
			$route = $this->parseRoute($route); //On récupère la route parsé
			//On lie le bon controlleur
			if (empty($route[0]) || !file_exists(PWD_CONTROLLER . $route[0] . '.php') || mb_strpos($route[0], 'internal') !== false) //Si on a pas de premier parametre, ou qu'il ne correspond à aucun controlleur
			{
				$controllerName = DEFAULT_CONTROLLER; //On défini le nom du controlleur par défaut
			}
			else //Sinon, si tout est bon
			{
				$controllerName = $route[0]; //On défini le nom du controlleur
			}
			
			return $controllerName;
		}

		/**
		 * Retrouve la méthode dans un URL
		 * @param string $route : la route à analyser
		 * @return string : Le nom de la methode
		 */
		public function parseMethod($route)
		{
			$controllerName = $this->parseController($route);
			require_once(PWD_CONTROLLER . $controllerName . '.php'); //On inclus le controlleur
			$controller = new $controllerName();
			$route = $this->parseRoute($route); //On récupère la route parsé
			//On lie la bonne méthode
			if (empty($route[1]) || !method_exists($controller, $route[1])) //Si on a pas de second parametre, ou qu'il ne correspond à aucune méthode du controlleur
			{
				$method = DEFAULT_METHOD; //On prend la méthode par défaut
			}
			else //Sinon, si tout est bon
			{
				$method = $route[1]; //On défini la méthode appelée
			}
			
			return $method;
		}

		/**
		 * Retrouve les paramètres dans un URL
		 * @param string $route : la route à analyser
		 * @return array : La liste des paramètres au format $clef => $valeur
		 */
		public function parseParams($route)
		{
			$route = $this->parseRoute($route); //On récupère la route parsé
			$params = array();
			$already_use_params = array();
			//On transforme les paramètres $_GET passés par l'url au format clef_value. Ex : prenom_pierre-lin = $_GET['prenom'] => 'pierre-lin'
			if (count($route) > 2) //Si on a plus de deux paramètres qui ont été passé
			{
				unset($route[0], $route[1]); //On supprime le controlleur, et la route, des paramètres, il ne reste que les parametres a passer en GET
				foreach($route as $param) //On passe sur chaque paramètre a transformer en GET
				{
					$param = explode('_', $param, 2); //On récupère le paramètre, via la délimiteur '_', en s'arretant au premier

					//Si on a déjà utilisé cette variable GET
					if (in_array($param[0], $already_use_params))
					{
						if (isset($params[$param[0]]))
						{
							$tmp_value = $params[$param[0]];
							$params[$param[0]] = array($tmp_value);
							unset($tmp_value);
						}

						$params[$param[0]][] = (isset($param[1])) ? rawurldecode($param[1]) : NULL;
					}
					else
					{
						$params[$param[0]] = (isset($param[1])) ? rawurldecode($param[1]) : NULL; //On définit la propriétée GET correspondante
						$already_use_params[] = $param[0];
					}
				}
			}

			return $params;
		}

		/**
		 * Cette fonction permet de charger la page adaptée a partir d'une url
		 * @param string $route : Route à analyser pour charger une page
		 * @return void
		 */
		public function loadRoute($route)
		{
			$_GET = array_merge($_GET, $this->parseParams($route)); //On fusionne les paramètres GET et ceux passés dans la route du controller
			$controllerName = $this->parseController($route); //On récupère le nom du controleur à appeler
			$controller = new $controllerName(); //On créer le controleur
			$beforeMethodName = DEFAULT_BEFORE; //On défini le nom de la fonction before
			if (method_exists($controller, $beforeMethodName)) //Si une fonction before existe pour ce controller, on l'appel
			{
				$controller->$beforeMethodName(); //On appel la fonction before
			}

			$methodName = $this->parseMethod($route); //On récupère le nom de la méthode
			$controller->$methodName(); //On appel la méthode	
		}

	} 
