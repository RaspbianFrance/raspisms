<?php
    namespace descartes;

    function define_array ($array)
    {
        foreach ($array as $key => $value)
        {
            if (defined(mb_strtoupper($key)))
            {
                continue;
            }

            define(mb_strtoupper($key), $value);
        }
    }

    function load_env ()
    {

        ### DESCARTES ENV ###
        $environment = [];
        $env = [];
        
        // Load descartes base env
        require_once(__DIR__ . '/env.php');
        $environment = array_merge($environment, $env);

        //Load descartes override env
        if (file_exists(__DIR__ . '/../env.descartes.php'))
        {
            require_once(__DIR__ . '/../env.descartes.php');
            $environment = array_merge($environment, $env);
        }

        ### GLOBAL ENV ###
        //Load global app env
        $env = [];
        if (file_exists(__DIR__ . '/../env.php'))
        {
            require_once(__DIR__ . '/../env.php');
            $environment = array_merge($environment, $env);
        }


        ### SPECIFIC ENV ###
        // Load specific environment env
        $env = [];
        if (isset($environment['ENV']) && file_exists(__DIR__ . '/../env.' . $environment['ENV'] . '.php'))
        {
            require_once(__DIR__ . '/../env.' . $environment['ENV'] . '.php');
            $environment = array_merge($environment, $env);
        }

        ### BUILD HTTP PWD CONSTS ###
        // We compute http pwd at last minute to allow for simple overriding by user
        // by simply defining custom HTTP_* (PROTOCOL, SERVER_NAME, SERVER_PORT, DIR_PATH) 
        $http_pwd = $environment['HTTP_PROTOCOL'] . $environment['HTTP_SERVER_NAME'] . $environment['HTTP_SERVER_PORT'] . $environment['HTTP_DIR_PATH'];
        $env = [
            "HTTP_PWD" => $http_pwd,
            'HTTP_PWD_ASSETS' => $http_pwd . '/assets', //HTTP path of asset dir
            'HTTP_PWD_IMG' => $http_pwd . '/assets' . '/img', 
            'HTTP_PWD_CSS' => $http_pwd . '/assets' . '/css', 
            'HTTP_PWD_JS' => $http_pwd . '/assets' . '/js', 
            'HTTP_PWD_FONT' => $http_pwd . '/assets' . '/font',
        ];
        $environment = array_merge($environment, $env);

        define_array($environment);
    }

    load_env();
