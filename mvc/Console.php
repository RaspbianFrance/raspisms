<?php
	/**
	 * Cette classe gère l'appel d'une console
	 */
	class Console
	{
		public $command; //Commande invoquée complète (sous forme de tableau)

		/**
		 * Constructeur de la classe console
		 * @param mixed $command : Tableau de la commande appelée (voir $argv). Si non fourni la console est construite vide.
		 */
		public function __construct($command = false)
		{
			if ($command)
			{
				$this->command = $command;
			}
			else
			{
				$this->command = [];
			}
		}

		//Getters et setters
		public function getCommand()
		{
			return $this->command;
		}

		public function setCommand($value)
		{
			$this->command = $value;
		}

		/**
		 * Cette méthode vérifie si une commande demande explicitement d'appeler l'aide
		 * @param array $command : La commande appelée
		 * @param boolean : Vrai si on doit appeler l'aide, faux sinon
		 */
		public function parseForHelp ($command)
		{
			return (isset($command[1]) && $command[1] == '--help');
		}

		/**
		 * Retourne le controlleur à appeler d'après une commande
		 * @param array $command : La commande à analyser
		 * @return mixed : Le nom du controlleur à appeler si la commande en contient un, et faux sinon ou si le controlleur n'existe pas
		 */
		public function parseForController($command)
		{
			//Si on doit chercher de l'aide globale, on retire la demande d'aide
			if ($this->parseForHelp($command))
			{
				unset($command[1]);
				$command = array_values($command);	
			}

			//Pas de controlleur à appeler
			if (!isset($command[1]))
			{
				return false;
			}

			$controllerName = $command[1];

			if (!file_exists(PWD_CONTROLLER . $controllerName . '.php'))
			{
				return false;
			}

			return $controllerName;
		}

		/**
		 * Retourne la méthode à appeler d'après une commande
		 * @param array $command : La commande à analyser
		 * @return mixed : Le nom de la méthode à appeler si la commande en contient une, et faux sinon ou si la méthode n'existe pas pour le controlleur demandé
		 */
		public function parseForMethod($command)
		{
			//Si on doit chercher de l'aide globale, on retire la demande d'aide
			if ($this->parseForHelp($command))
			{
				unset($command[1]);
				$command = array_values($command);	
			}

			//Le controlleur n'existe pas			
			if (!$controllerName = $this->parseForController($command))
			{
				return false;
			}

			//Si on a pas fourni de méthode
			if (!isset($command[2]))
			{
				return false;
			}

			$methodName = $command[2];

			//La méthode n'existe pas
			if (!method_exists($controllerName, $methodName))
			{
				return false;
			}

			return $methodName;
		}

		/**
		 * Retourne les paramètres d'une méthode d'après une commande
		 * @param array $command : La commande à analyser
		 * @return mixed : Le tableau des paramètres fournis (au format 'name' => 'value') ou faux si la méthode ou le controlleur n'existe pas
		 */		 
		public function parseForParams($command)
		{
			//Si on doit chercher de l'aide globale, on retire la demande d'aide
			if ($this->parseForHelp($command))
			{
				unset($command[1]);
				$command = array_values($command);	
			}

			//Le controlleur n'existe pas
			if (!$controllerName = $this->parseForController($command))
			{
				return false;
			}

			//La méthode n'existe pas
			if (!$methodName = $this->parseForMethod($command))
			{
				return false;
			}

			//On construit la liste des arguments passés à la commande au format 'name' => 'value'
			unset($command[0], $command[1], $command[2]);
			$command = array_values($command);
			$params = [];

			foreach ($command as $param)
			{
				$param = explode('=', $param, 2);
				$paramName = str_replace('--', '', $param[0]);
				$paramValue = $param[1];
				$params[$paramName] = $paramValue;
			}

			return $params;
		}

		/**
		 * Vérifie si une commande contient tous les paramètres obligatoires d'une méthode et retourne un tableau des arguments remplis
		 * @param array $command : La commande à analyser
		 * @return mixed : Si tout est bon, un tableau un contenant les différents paramètres dans l'ordre où ils doivent êtres passé à la méthode. Sinon, si par exemple un paramètre obligatoire est manquant false.
		 */
		public function checkParametersValidityForCommand($command)
		{
			//Le controlleur n'existe pas
			if (!$controllerName = $this->parseForController($command))
			{
				return false;
			}

			//La méthode n'existe pas
			if (!$methodName = $this->parseForMethod($command))
			{
				return false;
			}

			//Les paramètres retournes une erreur
			$commandParams = $this->parseForParams($command);
			if ($commandParams === false)
			{
				return false;
			}

			//On construit la liste des arguments de la méthode, dans l'ordre
			$reflection = new ReflectionMethod($controllerName, $methodName);
			$methodArguments = [];

			foreach ($reflection->getParameters() as $parameter)
			{
				//Si le paramètre n'est pas fourni et n'as pas de valeur par défaut
				if (!array_key_exists($parameter->getName(), $commandParams) && !$parameter->isDefaultValueAvailable())
				{
					return false;
				}

				//Si on a une valeur par défaut dispo, on initialise la variable avec
				if ($parameter->isDefaultValueAvailable())
				{
					$methodArguments[$parameter->getName()] = $parameter->getDefaultValue();
				}

				//Si la variable n'existe pas, on passe
				if (!array_key_exists($parameter->getName(), $commandParams))
				{
					continue;
				}

				//On ajoute la variable dans le tableau des arguments de la méthode	
				$methodArguments[$parameter->getName()] = $commandParams[$parameter->getName()];
			}

			return $methodArguments;
		}

		/**
		 * Cette méthode retourne le texte d'aide d'un controller ou d'une de ses méthodes
		 * @param array $command : La commande appelée
		 * @param string $controller : Le nom du controller pour lequel on veux l'aide, ou false (par défaut)
		 * @param string $method : La nom de la méthode pour laquelle on veux de l'aide, ou false (par défaut)
		 * @param boolean $missingArguments : Si il manque des arguments obligatoires pour la méthode (par défaut faux)
		 * @param string : Le texte d'aide à afficher
		 */
		public function getHelp ($command, $controller = false, $method = false, $missingArguments = false)
		{
			$retour = '';

			$retour .= "Aide : \n";

			//Si pas de controlleur, on sort l'aide par défaut
			if (!$controller)
			{
				$retour .= "Vous n'avez pas fournit de controller à appeler. Pour voir l'aide : " . $command[0] . " --help <nom controller> <nom methode>\n";
				return $retour;
			}

			if ($missingArguments)
			{
				$retour .= "Vous n'avez pas fournis tous les arguments obligatoire pour cette fonction. Pour rappel : \n";
			}

			//Si on a pas définie la méthode, on les fait toutes, sinon juste celle définie
			if (!$method)
			{
				$retour .= 'Aide du controller ' . $controller . "\nMéthodes : \n";
				$reflection = new ReflectionClass($controller);
				$reflectionMethods = $reflection->getMethods();
			}
			else
			{
				$reflectionMethods = [new ReflectionMethod($controller, $method)];
				$retour .= 'Aide du controller ' . $controller . ' et de la méthode ' . $method . "\n";
			}

			//Pour chaque méthode, on affiche l'aide
			foreach ($reflectionMethods as $reflectionMethod)
			{
				$retour .= "    " . $reflectionMethod->getName();

				//On ajoute chaque paramètre au retour
				foreach ($reflectionMethod->getParameters() as $parameter)
				{
					$retour .= ' --' . $parameter->getName() . "=<value" . ($parameter->isDefaultValueAvailable() ? ' (par défaut, ' . gettype($parameter->getDefaultValue()) . ':' . str_replace(PHP_EOL, '', print_r($parameter->getDefaultValue(), true)) . ')': '') . ">";
				}

				$retour .= "\n";
			}

			return $retour;
		}

		/**
		 * Cette méthode tente d'appeler le controlleur et la méthode correspondant à la commande, avec les arguments demandés
		 * @param array $command : La commande appelée
		 */
		public function executeCommand ($command)
		{
			//Si on a pas de controller à appeler
			if (!$controller = $this->parseForController($command))
			{
				echo $this->getHelp($command);
				return false;
			}

			//Si on a pas de méthode à appeler
			if (!$method = $this->parseForMethod($command))
			{
				echo $this->getHelp($command, $controller);
				return false;
			}

			//Si on a pas tous les arguments necessaires à la méthode
			$params = $this->checkParametersValidityForCommand($command);
			if ($params === false)
			{
				echo $this->getHelp($command, $controller, $method, true);
				return false;
			}

			//Si on doit appeler l'aide
			if ($this->parseForHelp($command))
			{
				echo $this->getHelp($command, $controller, $method);
				return false;
			}

			//Si tout est bien ok, on appel la méthode
			return call_user_func_array([$controller, $method], $params);
		}

	} 
