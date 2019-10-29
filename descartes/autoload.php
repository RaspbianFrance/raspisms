<?php
	/**
	 * Cette fonction permet le chargement automatique des classes. Cela permet de ne pas avoir à instancier chaque classe.
	 * Dans l'ordre, on essaye de charger les classes suivantes :
	 * 	- Classes du framework (app)
	 * 	- Classes personnalisées (toutes depuis la racine du framework)
	 */
	
	/**
	 * Cette fonction inclus le fichier de class
	 * @param string $class = Nom de la classe a aller chercher
	 */
	function autoloader($class)
	{
		$class = str_replace('\\', '/', $class); #Gestion des namespaces

		if (file_exists(PWD . '/' . $class . '.php'))
		{
			require_once(PWD . '/' . $class . '.php');
        }

	}
	
	spl_autoload_register('autoloader');
