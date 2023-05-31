<?php
    namespace descartes;

    /**
	 * Cette classe gère l'appel des ressources
	 */
	class Router
    {
        /**
         * Generate an url for a page of the app
         * @param string $controller : Name of controller we want url for
         * @param string $method : Name of method we want url for
         * @param array $params : Parameters we want to transmit to controller method
         * @param array $get_params : Get parameters we want to add to url
         * @return string : the generated url
         */
        public static function url (string $controller, string $method, array $params = [], array $get_params = []) : string
        {
            $url = HTTP_PWD;

            if (!array_key_exists($controller, ROUTES))
            {
                throw new \descartes\exceptions\DescartesExceptionRouterUrlGenerationError('Try to generate url for controller ' . $controller . ' that did not exist.');
            }

            if (!array_key_exists($method, ROUTES[$controller]))
            {
                throw new \descartes\exceptions\DescartesExceptionRouterUrlGenerationError('Try to generate url for method ' . $controller . '::' . $method . ' that did not exist.');
            }

            $get_params = http_build_query($get_params);

            $routes = ROUTES[$controller][$method];

            if (!is_array($routes))
            {
                $routes = [$routes];
            }

            foreach ($routes as $route)
            {
                foreach ($params as $name => $value)
                {
                    $find_flag = mb_strpos($route, '{' . $name . '}');
                    if ($find_flag === false)
                    {
                        continue 2;
                    }

                    $route = str_replace('{' . $name . '}', $value, $route);
                }

                $remain_flag = mb_strpos($route, '{');
                if ($remain_flag)
                {
                    continue;
                }

                return $url . $route . ($get_params ? '?' . $get_params : '');
            }

            throw new \descartes\exceptions\DescartesExceptionRouterUrlGenerationError('Cannot find any route for ' . $controller . '::' . $method . ' with parameters ' . print_r($params, true));
        }



		/**
         * Clean url to remove anything before app root, and ancre, and parameters
         * @param string $url : url to clean
         * @return string : cleaned url
		 */
		protected static function clean_url (string $url)
        {
            $to_remove = parse_url(HTTP_PWD, PHP_URL_PATH);
            
            $url = mb_strcut($url, mb_strlen($to_remove));
            $url = parse_url($url, PHP_URL_PATH);

            return $url;
		}


        /**
         * Find controller and method to call for an url
         * @param array $routes : Routes of the app
         * @param string $url : url to find route for
         * @return array|bool : An array, ['controller' => name of controller to call, 'method' => name of method to call, 'route' => 'route matching the url', 'route_regex' => 'the regex to extract data from url'], false on error
         */
        protected static function map_url (array $routes, string $url)
        {
            foreach ($routes as $controller => $controller_routes)
            {
                foreach ($controller_routes as $method => $method_routes)
                {
                    if (!is_array($method_routes))
                    {
                        $method_routes = [$method_routes];
                    }

                    foreach ($method_routes as $route)
                    {
                        $route_regex = preg_replace('#\\\{(.+)\\\}#iU', '([^/]+)', preg_quote($route, '#'));
                        $route_regex = preg_replace('#/$#', '/?', $route_regex);

                        $match = preg_match('#^' . $route_regex . '$#U', $url);
                        if (!$match)
                        {
                            continue;
                        }
                        
                        return [
                            'controller' => $controller,
                            'method' => $method,
                            'route' => $route,
                            'route_regex' => $route_regex,
                        ];
                    }
                }
            }

            return false;
        }


        /**
         * Get data from url and map it with route flags
         * @param string $url : A clean url to extract data from (see clean_url)
         * @param string $route : Route we must extract flag from
         * @param string $route_regex : Regex to extract data from url
         * @return array : An array with flagname and values, flag => value
         */
        protected static function map_params_from_url (string $url, string $route, string $route_regex)
        {
			$flags = [];
			preg_match_all('#\\\{(.+)\\\}#iU', preg_quote($route, '#'), $flags);
            $flags = $flags[1];

			$values = [];
			if (!preg_match('#^' . $route_regex . '$#U', $url, $values))
			{
				return false;
			}
			unset($values[0]);

			$values = array_map('rawurldecode', $values);

			//On retourne les valeurs associées aux flags
			return array_combine($flags, $values);
        }


        /**
         * Compute a controller name to return is real path with namespace
         * @param string $controller : The controller name
         * @return string|bool : False if controller does not exist, controller name with namespace else
         */
        protected static function compute_controller (string $controller)
        {
            $controller = str_replace('/', '\\', PWD_CONTROLLER . '/publics/') . $controller;
            $controller = mb_strcut($controller, mb_strlen(PWD));
            
            if (!class_exists($controller))
            {
                return false;

            }

            return $controller;
        }


        /**
         * Compute a method to find his real name and check its available
         * @param string $controller : Full namespace of controller to call
         * @param string $method : The method to call
         * @return string | bool : False if method unavaible, its realname else
         */
        protected static function compute_method (string $controller, string $method)
        {
            if (is_subclass_of($controller, 'descartes\ApiController'))
            {
				//On va choisir le type à employer
				$http_method = $_SERVER['REQUEST_METHOD'];
				switch (mb_convert_case($http_method, MB_CASE_LOWER))
				{
					case 'delete' :
						$prefix_method = 'delete_';
						break;
					case 'patch' :
						$prefix_method = 'patch_';
						break;
					case 'post' :
						$prefix_method = 'post_';
						break;
					case 'put' :
						$prefix_method = 'put_';
						break;
					default :
						$prefix_method = 'get_';
                }

                //If we dont match prefix with request method, return error
                if (mb_substr($method, 0, mb_strlen($prefix_method)) != $prefix_method)
                {
                    return false;
                }
            }

            if (!method_exists($controller, $method))
            {
                return false;
            }

            if (!is_callable($controller, $method))
            {
                return false;
            }

            return $method;
        }


        /**
         * Type, order and check params we must pass to method
         * @param string $controller : Full namespace of controller
         * @param string $method : Name of method
         * @param array $params : Parameters to compute, format name => value
         * @return array : Array ['success' => false, 'message' => error message] on error, and ['success' => true, 'method_arguments' => array of method arguments key=>val] on success
         */
        protected static function compute_params (string $controller, string $method, array $params) : array
        {
            $reflection = new \ReflectionMethod($controller, $method);
            $method_arguments = [];

			foreach ($reflection->getParameters() as $parameter)
			{
				if (!array_key_exists($parameter->getName(), $params) && !$parameter->isDefaultValueAvailable())
                {
                    return ['success' => false, 'message' => 'Try to call ' . $controller . '::' . $method . ' but ' . $parameter->getName() . ' is missing.'];
				}

				if ($parameter->isDefaultValueAvailable())
				{
					$method_arguments[$parameter->getName()] = $parameter->getDefaultValue();
				}

				if (!array_key_exists($parameter->getName(), $params))
				{
					continue;
                }


                $type = $parameter->getType();
                $type = $type ?? false;
                $type = ($type instanceof \ReflectionNamedType) ? $type->getName() : $type;

                if ($type)
                {
                    switch ($type)
                    {
                        case 'bool' :
                            $params[$parameter->getName()] = (bool) $params[$parameter->getName()];
                            break;

                        case 'int' :
                            $params[$parameter->getName()] = (int) $params[$parameter->getName()];
                            break;

                        case 'float' :
                            $params[$parameter->getName()] = (float) $params[$parameter->getName()];
                            break;

                        case 'string' :
                            $params[$parameter->getName()] = (string) $params[$parameter->getName()];
                            break;

                        default :
                            return ['success' => false, 'message' => 'Method ' . $controller . '::' . $method . ' use an invalid type for param ' . $parameter->getName() . '. Only bool, int float and string are supported.']; 
                            break;
                    }
                }
                
                $method_arguments[$parameter->getName()] = $params[$parameter->getName()];
            }

            return ['success' => true, 'method_arguments' => $method_arguments];
        }


        /**
         * Throw a 404 exception
         */
        public static function error_404 () : void
        {
            throw new \descartes\exceptions\DescartesException404();
        }


        /**
         * Route a query
         * @param array $routes : Routes of app
         * @param string $url : Url call
         * @param mixed $args : Args we want to pass to Controller constructor
         */
        public static function route (array $routes, string $url, ...$args) : void
        {
            $url = static::clean_url($url);

            $computed_url = static::map_url($routes, $url);
            if (!$computed_url)
            {
                static::error_404();
            }


            $params = static::map_params_from_url($url, $computed_url['route'], $computed_url['route_regex']);

            $controller = static::compute_controller($computed_url['controller']);
            if (!$controller)
            {
                throw new \descartes\exceptions\DescartesExceptionRouterInvocationError('Try to call controller ' . $computed_url['controller'] . ' that did not exists.');
            }

            $method = static::compute_method($controller, $computed_url['method']);
            if (!$method)
            {
                throw new \descartes\exceptions\DescartesExceptionRouterInvocationError('Try to call the method ' . $computed_url['method'] . ' that did not exists from controller ' . $controller . '.');
            }

            $compute_params_result = static::compute_params($controller, $method, $params);
            if (!$compute_params_result['success'])
            {
                throw new \descartes\exceptions\DescartesExceptionRouterInvocationError($compute_params_result['message']);
            }

            $method_arguments = $compute_params_result['method_arguments'];

            $controller = new $controller(...$args);
            call_user_func_array([$controller, $method], $method_arguments);
        }
	} 
