<?php
	/**
	 * Page des commandes
	 */
	class commands extends Controller
	{
		/**
		 * Cette fonction est appelée avant toute les autres : 
		 * Elle vérifie que l'utilisateur est bien connecté
		 * @return void;
		 */
		public function before()
		{
			internalTools::verifyConnect();
		}

		/**
		 * Cette fonction est alias de showAll()
		 */	
		public function byDefault()
		{
			$this->showAll();
		}
		
		/**
		 * Cette fonction retourne toutes les commandes, sous forme d'un tableau permettant l'administration de ces commandess
		 * @return void;
		 */
		public function showAll()
		{
			//Creation de l'object de base de données
			global $db;
			
			//Recupération des commandes
			$commands = $db->getAll('commands');

			$this->render('commands', array(
				'commands' => $commands,
			));
			
		}

		/**
		 * Cette fonction va supprimer une liste de commands
		 * @return void;
		 */
		public function delete()
		{
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('commands', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Create de l'object de base de données
			global $db;
			
			$commands_ids = $_GET;
			$db->deleteCommandsIn($commands_ids);
			header('Location: ' . $this->generateUrl('commands'));		
		}

		/**
		 * Cette fonction retourne la page d'ajout d'une commande
		 */
		public function add()
		{
			$this->render('addCommand');
		}

		/**
		 * Cette fonction retourne la page d'édition des contacts
		 */
		public function edit()
		{
			global $db;
			

			$commands = $db->getCommandsIn($_GET);
			$this->render('editCommands', array(
				'commands' => $commands,
			));
		}

		/**
		 * Cette fonction insert une nouvelle commande
		 */
		public function create()
		{
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('commands', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			

			$nom = $_POST['name'];
			$script = $_POST['script'];
			$admin = (isset($_POST['admin']) ? $_POST['admin'] : false);
			if ($db->createCommand($nom, $script, $admin))
			{
				$db->createEvent('COMMAND_ADD', 'Ajout commande : ' . $nom . ' => ' . $script);
				header('Location: ' . $this->generateUrl('commands', 'showAll', array(
					'successmessage' => 'La commande a bien été créée.'
				)));

				return true;
			}

			header('Location: ' . $this->generateUrl('commands', 'add', array(
				'errormessage' => 'Impossible créer cette commande.'
			)));
			return false;
		}

		/**
		 * Cette fonction met à jour une commande
		 */
		public function update()
		{
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('commands', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			

			$errors = array(); //On initialise le tableau qui contiendra les erreurs rencontrés
			//Pour chaque commande reçu, on boucle en récupérant son id (la clef), et la commande elle-même (la value)


			foreach ($_POST['commands'] as $id => $command)
			{
				$db->updateCommand($id, $command['name'], $command['script'], $command['admin']);
			}

			$message = 'Toutes les commandes ont été modifiées avec succès.';
			header('Location: ' . $this->generateUrl('commands', 'showAll', array(
				'successmessage' => $message,
			)));
		}

		public function flagMeThat()
		{
			var_dump(internalTools::parseForFlag('[COMMAND:chauffer 35][PASSWORD:mon password qui rox][LOGin:monlogin]'));
		}
	}
