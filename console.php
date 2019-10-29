#!/usr/bin/php
<?php

    ###############
	# ENVIRONMENT #
	###############
	define('ENVIRONMENT', 'dev');
	define('FROM_WEB', true);
	require_once(__DIR__ . '/descartes/load-environment.php');

	##############
	# INCLUSIONS #
	##############
	require_once(PWD . '/descartes/autoload.php');
	require_once(PWD . '/vendor/autoload.php');
	require_once(PWD . '/descartes/Console.php');
	require_once(PWD . '/routes.php');

	#########
	# MODEL #
	#########
    #Define raspisms settings
    $bdd = Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    $model_setting = new \models\Setting($bdd);
    
    $settings = $model_setting->all();
	foreach ($settings as $setting)
	{
		define('RASPISMS_SETTINGS_' . mb_convert_case($setting['name'],  MB_CASE_UPPER), $setting['value']);
	}
    

	###########
	# ROUTAGE #
	###########
	//Partie gérant l'appel des controlleurs
    $console = new \Console($argv);
    $console->executeCommand($console->getCommand());

