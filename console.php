#!/usr/bin/php
<?php
	/**
	 *	Cette page gère les scripts appelés en ligne de commande
	 */

	##############
	# INCLUSIONS #
	##############
	require_once(__DIR__ . '/mvc/constants.php');
	require_once(PWD . 'mvc/autoload.php');
	require_once(PWD . 'mvc/conn_bdd.php');
	require_once(PWD . 'mvc/secho.php');
	require_once(PWD . 'mvc/Controller.php');
	require_once(PWD . 'mvc/ApiController.php');
	require_once(PWD . 'mvc/Router.php');
	require_once(PWD . 'mvc/Model.php');
	require_once(PWD . 'mvc/Console.php');

	#########
	# MODEL #
	#########
	//On va appeler un modèle, est l'initialiser
	$db = new DataBase($bdd);

	//On va ajouter les réglages globaux de RaspiSMS modifiables via l'interface
	$settings = $db->getFromTableWhere('settings');
	foreach ($settings as $setting)
	{
		define('RASPISMS_SETTINGS_' . mb_convert_case($setting['name'],  MB_CASE_UPPER), $setting['value']);
	}
	
	###########
	# ROUTAGE #
	###########
	//Partie gérant l'appel des controlleurs
	$console = new Console($argv);
	$console->executeCommand($console->getCommand());
