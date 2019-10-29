<?php

    function load_env ()
    {
        $environment = [];
        $env = [];

        //Load descartes global env
        require_once(__DIR__ . '/env.php');
        $environment = array_merge($environment, $env);

        //Load descartes override env
        if (file_exists(__DIR__ . '/../env.descartes.php'))
        {
            require_once(__DIR__ . '/../env.descartes.php');
            $environment = array_merge($environment, $env);
        }
        
        //Load user defined global env
        if (file_exists(__DIR__ . '/../env.php'))
        {
            require_once(__DIR__ . '/../env.php');
            $environment = array_merge($environment, $env);
        }

        //Define all constants 
        foreach ($environment as $name => $value)
        {
            define(mb_strtoupper($name), $value);
        }

        //Load user defined env specific env
        $environment = [];
        $env = [];
        
        if (defined('ENV') && file_exists(__DIR__ . '/../env.' . ENV . '.php'))
        {
            require_once(__DIR__ . '/../env.' . ENV . '.php');
            $environment = array_merge($environment, $env);
        }

        //Define env specific constants
        foreach ($environment as $name => $value)
        {
            define(mb_strtoupper($name), $value);
        }
    }

    load_env();
