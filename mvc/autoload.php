<?php
	/**
	 * Cette fonction permet le chargement automatique des classes. Cela permet de ne pas avoir à instancier chaque classe.
	 */
	
	/**
	 * Cette fonction inclus le fichier de class
	 * @param string $class = Nom de la classe a aller chercher
	 */
	function autoloader($class)
	{
		$class = str_replace('\\', '/', $class); #Gestion des namespaces

		if (file_exists(PWD_CONTROLLER . $class . '.php'))
		{
			require_once(PWD_CONTROLLER . $class . '.php');
		}
		else if (file_exists(PWD_MODEL . $class . '.php'))
		{
			require_once(PWD_MODEL . $class . '.php');
		}
		else if (file_exists(PWD . 'mvc/modules/' . $class . '.php'))
		{
			require_once(PWD . 'mvc/modules/' . $class . '.php');
		}
	}
	
	spl_autoload_register('autoloader');
