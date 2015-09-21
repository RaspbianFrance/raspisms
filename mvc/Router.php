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
		 * Cette méthode retourne la page 404 par défaut
		 */
		public function return404 ()
		{
			http_response_code(404);
			include(PWD . 'mvc/404.php');
			die();
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
			$route = explode('?', $route)[0]; //on ne garde que ce qui précède les arguments
			$route = preg_split('#[/]#', $route); //On explose la route

			foreach($route as $key => $val) //On garde seulement les repertoires non vides
			{
				if(empty($val) && $val !== 0 && $val !== '0')
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

			//Si pas de controlleur, on prend celui par défaut
			if (empty($route[0]))
			{
				return DEFAULT_CONTROLLER;
			}

			//Sinon, si le controlleur n'existe pas ou est internal, on retourne une 404
			if (!file_exists(PWD_CONTROLLER . $route[0] . '.php') || mb_strpos($route[0], 'internal') !== false)
			{
				return $this->return404();
			}

			//On retourne le controlleur adapté
			return $route[0];
		}

		/**
		 * Retrouve la méthode dans un URL
		 * @param string $route : la route à analyser
		 * @return string : Le nom de la methode
		 */
		public function parseMethod($route)
		{
			//On instancie le controlleur
			$controllerName = $this->parseController($route);
			$controller = new $controllerName();
			$prefixMethod = '';

			//On recupère les paramètres dans l'url pour les utiliser un peu plus tard
			$params = $this->parseParams($route);


			//On vérifie si le controlleur est un controlleur API, si c'est le cas on le refais avec cette fois la bonne methode
			if ($controller instanceof ApiController)
			{
				//On va choisir le type à employer
				$method = $_SERVER['REQUEST_METHOD'];
				switch (mb_convert_case($method, MB_CASE_LOWER))
				{
					case 'delete' :
						$prefixMethod = 'delete';
						break;
					case 'patch' :
						$prefixMethod = 'patch';
						break;
					case 'post' :
						$prefixMethod = 'post';
						break;
					case 'put' :
						$prefixMethod = 'put';
						break;
					default :
						$prefixMethod = 'get';
				}
			}
			
			$route = $this->parseRoute($route); //On récupère la route parsé

			//On regarde quelle route il nous faut et on evite les routes qui commencent par '_', qui sont maintenant considérées comme privées
			if (empty($route[1]))
			{
				$method = DEFAULT_METHOD;
			}
			else
			{
				$method = $route[1];
			}

			if ($prefixMethod)
			{
				$method = $prefixMethod . mb_convert_case($method, MB_CASE_TITLE);
			}

			//Si la méthode à appeler n'existe pas ou si la route commencent par '_', signe qu'elle est un outils non accessibles
			if (!method_exists($controller, $method) || mb_substr($method, 0, 1) == '_')
			{
				return $this->return404();
			}

			//On instancie la classe reflection de PHP sur la méthode que l'on veux analyser pour l'objet controller
			$methodAnalyser = new ReflectionMethod($controller, $method);

			//Si la méthode à appeler demande plus de paramètres que fournis on retourne une 404
			if ($methodAnalyser->getNumberOfRequiredParameters() > count($params))
			{
				return $this->return404();
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

			//On transforme les paramètres $_GET passés par l'url au format clef-value. Ex : prenom-pierre-lin = $_GET['prenom'] => 'pierre-lin'
			if (count($route) > 2) //Si on a plus de deux paramètres qui ont été passé
			{
				unset($route[0], $route[1]); //On supprime le controlleur, et la route, des paramètres, il ne reste que les parametres a passer en GET
				foreach ($route as $param)
				{
					$params[] = rawurldecode($param);
				}
			}
			return $params;
		}

		/**
		 * Cette fonction permet de vérifier si le cache est activé pour une route, et si oui quel fichier utiliser
		 * @param string $route : Route à analyser
		 * @return mixed : Si pas de cache, faux. Sinon un tableau avec deux clefs, "state" => statut du nom de fichier retourné (true, le fichier doit être utilisé, false, le fichier doit être créé), "file" => Le nom du fcihier de cache
		 */
		public function verifyCache($route)
		{
			//On récupère le nom du controller et de la méthode
			$controllerName = $this->parseController($route);
			$methodName = $this->parseMethod($route);
			$params = $this->parseParams($route);

			$controller = new $controllerName();

			//Si on ne doit pas activer le cache ou si on na pas de cache pour ce fichier
			if (!ACTIVATING_CACHE || !property_exists($controller, 'cache_' . $methodName))
			{
				return false;
			}

			//Si on a du cache, on va déterminer le nom approprié
			//Format de nom = <hash:nom_router.nom_methode><hash_datas>
			$hashName = md5($controllerName . $methodName);
			$hashDatas = md5(json_encode($_GET) . json_encode($_POST) . json_encode($params));
			$fileName = $hashName . $hashDatas;

			//Si il n'existe pas de fichier de cache pour ce fichier
			if (!file_exists(PWD_CACHE . $fileName))
			{
				return array('state' => false, 'file' => $fileName);
			}

			//Sinon, si le fichier de cache existe
			$fileLastChange = filemtime(PWD_CACHE . $fileName);

			//On calcul la date de mise à jour valide la plus ancienne possible
			$now = new DateTime();
			$propertyName = 'cache_' . $methodName;
			$propertyValue = $controller->$propertyName;
			$now->sub(new DateInterval('PT' . $propertyValue . 'M'));
			$maxDate = $now->format('U');
			
			//Si le fichier de cache est trop vieux
			if ($fileLastChange < $maxDate)
			{
				return array('state' => false, 'file' => $fileName);
			}

			//Sinon, on retourne le fichier de cache en disant de l'utiliser
			return array('state' => true, 'file' => $fileName);
		}

		/**
		 * Cette fonction permet de charger la page adaptée a partir d'une url
		 * Elle gère également le cache
		 * @param string $route : Route à analyser pour charger une page
		 * @return void
		 */
		public function loadRoute($route)
		{
			$params = $this->parseParams($route); //On récupère les paramètres à passer à la fonction
			$controllerName = $this->parseController($route); //On récupère le nom du controleur à appeler
			$controller = new $controllerName(); //On créer le controleur

			$beforeMethodName = DEFAULT_BEFORE; //On défini le nom de la fonction before
			if (method_exists($controller, $beforeMethodName)) //Si une fonction before existe pour ce controller, on l'appel
			{
				$controller->$beforeMethodName(); //On appel la fonction before
			}

			$methodName = $this->parseMethod($route); //On récupère le nom de la méthode
			$verifyCache = $this->verifyCache($route);

			//Si on ne doit pas utiliser de cache
			if ($verifyCache === false)
			{
				call_user_func_array(array($controller, $methodName), $params); //On appel la methode, en lui passant les arguments necessaires
				return null;
			}

			//Si on doit utiliser un cache avec un nouveau fichier
			if ($verifyCache['state'] == false)
			{
				//On créer le fichier avec le contenu adapté
				ob_start();
				call_user_func_array(array($controller, $methodName), $params); //On appel la methode, en lui passant les arguments necessaires
				$content = ob_get_contents();
				file_put_contents(PWD_CACHE . $verifyCache['file'], $content);
				ob_end_clean();
			}

			//On utilise le fichier de cache
			readfile(PWD_CACHE . $verifyCache['file']);
		}
	} 
