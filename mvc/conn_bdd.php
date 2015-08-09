<?php
	//CETTE PAGE GERE LA CONNEXION A LA BASE DE DONNEES
	$host = DATABASE_HOST;
	$dbname = DATABASE_NAME;
	$user = DATABASE_USER;
	$password = DATABASE_PASSWORD;

	try
	{
		// On se connecte à MySQL
		$bdd = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $user, $password, array(PDO::ATTR_PERSISTENT => TRUE));
		$bdd->exec("SET CHARACTER SET utf8");
	}
	catch(Exception $e)
	{
		// En cas d'erreur, on affiche un message et on arrête tout
		die('Erreur : '.$e->getMessage());
	}
?>
