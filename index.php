<?php
    ###############
    # ENVIRONMENT #
    ###############
    require_once(__DIR__ . '/descartes/load-environment.php');

    ###########
    # ROUTING #
    ###########
    require_once(PWD . '/routes.php'); //Include routes

    ############
    # SESSIONS #
    ############
    session_name(SESSION_NAME);
    session_start();

    //Create csrf token if it didn't exist
    if (!isset($_SESSION['csrf']))
    {
        $_SESSION['csrf'] = str_shuffle(uniqid().uniqid());
    }

    ##############
    # INCLUDE    #
    ##############
    //Use autoload
    require_once(PWD . '/descartes/autoload.php');
    require_once(PWD . '/vendor/autoload.php');

    #Define raspisms settings
    $bdd = Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    $internal_setting = new \controllers\internals\Setting($bdd);
    
    $settings = $internal_setting->all();
	foreach ($settings as $setting)
	{
		define('RASPISMS_SETTINGS_' . mb_convert_case($setting['name'],  MB_CASE_UPPER), $setting['value']);
	}

    //Routing current query
    Router::route(ROUTES, $_SERVER['REQUEST_URI']);
