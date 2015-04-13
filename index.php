<?php
	session_start();
	##############
	# INCLUSIONS #
	##############
	//récupération du  chemin absolu
        $filepath = realpath (dirname(__FILE__));
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

	###########
	# ROUTAGE #
	###########
	//Partie gérant l'appel des controlleurs
	$router = new Router($_SERVER['REQUEST_URI']);
	$router->loadRoute($_SERVER['REQUEST_URI']);


