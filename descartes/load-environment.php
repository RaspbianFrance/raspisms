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
        
        require_once(__DIR__ . '/env.php');
        $environment = array_merge($environment, $env);

        //Load descartes override env
        if (file_exists(__DIR__ . '/../env.descartes.php'))
        {
            require_once(__DIR__ . '/../env.descartes.php');
            $environment = array_merge($environment, $env);
        }

        //Define all Descartes constants 
        define_array($environment);

        ### GLOBAL ENV ###
        $environment = [];
        $env = [];
        if (file_exists(__DIR__ . '/../env.php'))
        {
            require_once(__DIR__ . '/../env.php');
            $environment = array_merge($environment, $env);
        }

        define_array($environment);


        ### SPECIFIC ENV ###
        $environment = [];
        $env = [];
        
        if (defined('ENV') && file_exists(__DIR__ . '/../env.' . ENV . '.php'))
        {
            require_once(__DIR__ . '/../env.' . ENV . '.php');
            $environment = array_merge($environment, $env);
        }

        define_array($environment);
    }

    load_env();
