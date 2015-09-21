#!/usr/bin/php
<?php
	/**
	 *	Cette page gère les scripts appelés en ligne de commande
	 */

	##############
	# INCLUSIONS #
	##############
	//On va inclure l'ensemble des fichiers necessaires
	require_once('./mvc/constants.php');
	require_once('./mvc/autoload.php');
	require_once('./mvc/conn_bdd.php');
	require_once('./mvc/secho.php');
	require_once('./mvc/Controller.php');
	require_once('./mvc/Router.php');
	require_once('./mvc/Model.php');

	#########
	# MODEL #
	#########
	//On va appeler un modèle, est l'initialiser
	$db = new DataBase($bdd);;

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
	$controller = new internalConsole();

	$options = getopt('c:');

	if (!isset($options['c'])) //Si on a pas reçu de methode à appeler
	{
		echo "Vous devez précisez un script à appeler (-c 'nom du script').\n";
		echo "Pour plus d'infos, utilisez -c 'help'\n";
		exit(1); //Sorti avec erreur
	}
	
	if (!method_exists($controller, $options['c'])) //Si la méthode reçue est incorrect
	{
		echo "Vous avez appelé un script incorrect.\n";
		echo "Pour plus d'infos, utilisez -c 'help'\n";
		exit(2); //Sorti avec erreur
	}

	$controller->$options['c'](); //On appel la fonction

