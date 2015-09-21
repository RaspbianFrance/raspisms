<?php
	/**
	 * Cette classe sert de mère à tous les controlleurs, elle permet de gérer l'ensemble des fonction necessaires à l'affichage de template, à l'écriture de logs, etc.
	 */
	class Controller
	{
		protected $id; //Id unique du controller actuel
		protected $callDate; //Date ou l'on a appelé ce controller
		protected $userIp; //Adresse Ip de l'utilisateur qui demande l'appel de ce controller
		public function __construct()
		{
			$this->id = uniqid(); //On défini un id unique pour ce controller
			$this->callDate = (new DateTime())->format('Y-m-d H:i:s');
			$this->userIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		}

		/**
		 * Cette fonction permet d'ecrire des logs
		 * @param string $log = Log a ecrire
		 * @return booleen : Vrai si succes, faux sinon
		 */
		protected function wlog($log)
		{
			if(!LOG_ACTIVATED) //Si on a désactivé les logs
			{
				return false; //On retourne faux
			}

			//On forge le message à logger
			$message = 'FROM [Controller : ' . $this->id . ' - ' . get_called_class() . '] - ' . str_replace("\0", "", $log);

			if(error_log($message)) //On log, si succes
			{
				return true; //On retourne vrai
			}

			return false;
		}

		/**
		 * Cette fonction permet d'afficher un template
		 * @param string $template : Nom du template à afficher
		 * @param array $variables : Tableau des variables à passer au script, au format 'nom' => valeur (par défaut array())
		 * @return booleen, Vrai si possible, faux sinon
		 */
		protected function render($template, $variables = array())
		{
			foreach($variables as $clef => $value)
			{
				$$clef = $value;
			}

			$chemin_template = PWD_TEMPLATES . $template . '.php';
			if(file_exists($chemin_template))
			{
				require($chemin_template);
				unset($chemin_template);
				return true;
			}

			return false;
		}

		/**
		 * Cette fonction permet de générer une adresse URL interne au site
		 * @param string $controller : Nom du controleur à employer (par défaut vide)
		 * @param string $method : Nom de la méthode à employer (par défaut vide)
		 * @param string $params : Tableau des paramètres à passer à la fonction, sous forme de tableau, sans clefs
		 * @param string $getParams : Tableau des paramètres $_GET à employer au format 'nom' => valeur (par défaut array())
		 * @return string, Url générée
		 */ 
		protected function generateUrl($controller = '', $method = '', $params = array(), $getParams = array())
		{
			$url = HTTP_PWD;
			$url .= ($controller ? $controller . '/' : '');
			$url .= ($method ? $method . '/' : '');
		
			//On ajoute les paramètres framework	
			foreach ($params as $valeur)
			{
				$url .= rawurlencode($valeur) . '/';	
			}

			//On calcul puis ajoute les paramètres get
			$paramsToJoins = array();
			foreach ($getParams as $clef => $valeur)
			{
				$paramsToJoins[] = $clef . '=' . rawurlencode($valeur);
			}

			$url .= count($getParams) ? '?' . implode('&', $paramsToJoins) : '';

			return $url;
		}

		/**
		 * Cette fonction permet de récupérer le controleur actuel depuis l'URL
		 * @return string, nom du controleur actuel
		 */ 
		protected function getActualControllerName()
		{
			$router = new Router($_SERVER['REQUEST_URI']);
			return $router->getControllerName();
		}

		/**
		 * Cette fonction permet de récupérer la méthode actuel depuis l'URL
		 * @return string, nom de la méthode actuel
		 */ 
		protected function getActualMethodName()
		{
			$router = new Router($_SERVER['REQUEST_URI']);
			return $router->getMethodName();
		}
	} 
