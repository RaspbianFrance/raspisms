<?php
    require_once(__DIR__ . '/descartes/load.php');

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

    #####################
    # RASPISMS SETTINGS #
    #####################
    $bdd = descartes\Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    $internal_setting = new \controllers\internals\Setting($bdd);
    
    $settings = $internal_setting->all();
	foreach ($settings as $setting)
	{
		define('RASPISMS_SETTINGS_' . mb_convert_case($setting['name'],  MB_CASE_UPPER), $setting['value']);
	}

    //Routing current query
    descartes\Router::route(ROUTES, $_SERVER['REQUEST_URI']);
