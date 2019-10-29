<?php
    namespace descartes;
    /**
     * Class to route console query
	 */
	class Console
	{
		/**
         * Check if a command explicitly ask for help
         * @param array $command : Command called
         * @param boolean : True if need help, false else
		 */
		private static function is_asking_for_help (array $command) : bool
		{
			return (isset($command[1]) && $command[1] == '--help');
		}


        /**
         * Search name of controller to call and verify if it exist
		 * @param array $command : Command called
         * @return string | bool : False if controller didn't exist. Name of controller if find
		 */
		private static function extract_controller (array $command)
        {
            //If we need help, we remove help flag
			if (self::is_asking_for_help($command))
			{
				unset($command[1]);
				$command = array_values($command);	
            }
            
            //If no controller found
			if (!isset($command[1]))
			{
				return false;
			}

			$controller = $command[1];
            $controller = str_replace('/', '\\', $controller);

            $ends_with = mb_substr($controller, -4);
            if ($ends_with === '.php')
            {
                $controller = mb_substr($controller, 0, mb_strlen($controller) - 4);
            }


			if (!class_exists($controller))
			{
				return false;
			}

			return $controller;
		}


        /**
         * Search name of the method to call, an verify it exist and is available
         * @param array $command : Command called
         * @param string $controller : Name of controller of the method
         * @return string | bool : False if method not found, not exist or not available, method name else
		 */
		private static function extract_method (array $command, string $controller)
		{
            //Remove help flag if needed
            if (self::is_asking_for_help($command))
			{
				unset($command[1]);
				$command = array_values($command);	
			}

            //If no method passed
			if (!isset($command[2]))
			{
				return false;
			}

			$method = $command[2];

			if (!method_exists($controller, $method))
			{
				return false;
			}

			return $method;
		}


        /**
         * Extract params from the command
		 * @param array $command : La commande à analyser
         * @return mixed : Array of params (format name => value).
         * @return array | bool : An array with parameters in order we want theme to be passed to method. False if a need parameter is missing
		 */
		private static function extract_params (array $command, string $controller, string $method)
		{
            //Remove invocation, controller and method from command
			unset($command[0], $command[1], $command[2]);
            
            $command = array_values($command);
			$params = [];

			foreach ($command as $param)
			{
				$param = explode('=', $param, 2);
				$name = str_replace('--', '', $param[0]);
				$value = $param[1];
				$params[$name] = $value;
			}

			$reflection = new \ReflectionMethod($controller, $method);
			$method_arguments = [];

			foreach ($reflection->getParameters() as $parameter)
			{
				if (!array_key_exists($parameter->getName(), $params) && !$parameter->isDefaultValueAvailable())
				{
					return false;
				}

				if ($parameter->isDefaultValueAvailable())
				{
					$method_arguments[$parameter->getName()] = $parameter->getDefaultValue();
				}

				if (!array_key_exists($parameter->getName(), $params))
				{
					continue;
				}

				//On ajoute la variable dans le tableau des arguments de la méthode	
				$method_arguments[$parameter->getName()] = $params[$parameter->getName()];
			}

			return $method_arguments;
		}


        /**
         * Generate help text
		 * @param array $command : Called command
         * @param ?string $controller : Name of the controller we want help for, null if not given
         * @param ?string $method : Name of the method we want help for, null if not giver
         * @param boolean $missing_arguments : If there is required arguments missing, false by default
		 * @param string : Le texte d'aide à afficher
		 */
		private static function generate_help_text (array $command, ?string $controller = null, ?string $method = null, bool $missing_arguments = false) : string
        {

			$retour = '';

			$retour .= "Help : \n";

			//Si pas de controlleur, on sort l'aide par défaut
			if (!$controller)
			{
				$retour .= "You havn't supplied a Controller to call. To see help of a Controller : " . $command[0] . " --help <name controller> <name method>\n";
				return $retour;
			}

			if ($missing_arguments)
			{
				$retour .= "Some required arguments are missing. \n";
			}

			if (!$method)
			{
                $retour .= 'Help of Controller ' . $controller . "\n" . 
                           "Methods : \n";

				$reflection = new \ReflectionClass($controller);
				$reflection_methods = $reflection->getMethods();
			}
			else
			{
				$reflection_methods = [new \ReflectionMethod($controller, $method)];
				$retour .= 'Help of Controller ' . $controller . ' and method ' . $method . "\n";
			}

			foreach ($reflection_methods as $reflection_method)
			{
				$retour .= "    " . $reflection_method->getName();

				foreach ($reflection_method->getParameters() as $parameter)
				{
					$retour .= ' --' . $parameter->getName() . "=<value" . ($parameter->isDefaultValueAvailable() ? ' (by default, ' . gettype($parameter->getDefaultValue()) . ':' . str_replace(PHP_EOL, '', print_r($parameter->getDefaultValue(), true)) . ')': '') . ">";
				}

				$retour .= "\n";
			}

			return $retour;
		}

        /**
         * This method call controller and method from command
         * @param array $command : Command to call
         * @param $args : Arguments to pass to Controller constructor
         * @return bool : False on error, true else
		 */
		public static function execute_command (array $command, ...$args)
        {
            $controller = self::extract_controller($command);
            if (!$controller)
            {
                echo self::generate_help_text($command);
                return true;
            }

            $method = self::extract_method($command, $controller);
            if (!$method)
            {
                echo self::generate_help_text($command, $controller);
                return true;
            }
            
            $params = self::extract_params($command, $controller, $method);
            if ($params === false)
            {
                echo self::generate_help_text($command, $controller, $method, true);
                return true;
            }

            $asking_for_help = self::is_asking_for_help($command);
            if ($asking_for_help)
            {
                echo self::generate_help_text($command, $controller, $method);
                return true;
            }

            $reflection = new \ReflectionClass($controller);
            $reflection_method = $reflection->getMethod($method);

            if (!$reflection_method->isStatic())
            {
            	$controller = new $controller(...$args);
            }
			
			return call_user_func_array([$controller, $method], $params);
		}

	} 
