<?php
	/**
	 * Page des utilisateurs
	 */
	class users extends Controller
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
		 * Cette fonction retourne tous les utilisateurs, sous forme d'un tableau permettant l'administration de ces utilisateurs
		 * @return void;
		 */
		public function showAll()
		{
			//Creation de l'object de base de données
			global $db;
			
			
			//Récupération des utilisateurs
			$users = $db->getAll('users');

			$this->render('users', array(
				'users' => $users,
			));
		}
		
		/**
		 * Cette fonction retourne la page d'ajout d'un utilisateur
		 */
		public function add()
		{
			$this->render('addUser');
		}

		/**
		 * Cette fonction insert un nouvel utilisateur
		 */
		public function create()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('users', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			

			if (!isset($_POST['email']) || !isset($_POST['email_confirm']) || $_POST['email'] != $_POST['email_confirm'])
			{
				header('Location: ' . $this->generateUrl('users', 'add', array(
					'errormessage' => 'Les e-mails fournis ne correspondent pas.'
				)));
				return false;
			}

			$email = $_POST['email'];
			$no_crypt_password = internalTools::generatePassword(rand(8,12));
			$password = sha1($no_crypt_password);

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				header('Location: ' . $this->generateUrl('users', 'add', array(
					'errormessage' => 'L\'e-mail fourni présente un format incorrect.'
				)));
				return false;
			}

			$admin = false;

			if (isset($_SESSION['admin']) && $_SESSION['admin'])
			{
				if (isset($_POST['admin']) && $_POST['admin'])
				{
					$admin = true;
				}
			}

			$message  = "Votre compte a été créé sur le site " . HTTP_PWD . " avec les identifiants suivants : \n";
			$message .= "Adresse e-mail : " . $email . "\n";
			$message .= "Mot de passe : " . $no_crypt_password . "\n\n";
			$message .= "-------------------------------------\n";
			$message .= "Pour plus d'informations sur le système RaspiSMS, rendez-vous sur le site http://raspbian-france.fr\n";

			if (mail($email, 'Identifiants RaspiSMS', $message))
			{
				if ($db->createUser($email, $password, $admin))
				{
						$db->createEvent('USER_ADD', 'Ajout de l\'utilisateur : ' . $email);
						header('Location: ' . $this->generateUrl('users', 'showAll', array(
							'successmessage' => 'L\'utilisateur a bien été créé.'
						)));
						return true;
				}

				header('Location: ' . $this->generateUrl('users', 'add', array(
					'errormessage' => 'Impossible de créer cet utilisateur.'
				)));
				return false;
			}
			else
			{
				header('Location: ' . $this->generateUrl('users', 'showAll', array(
					'errormessage' => 'Impossible d\'envoyer le mail d\'inscription à l\'utilisateur. Le compte n\'a donc pas été créé.'
				)));
				return false;
			}
		}

		/**
		 * Cette fonction supprimer une liste d'utilisateur
		 * @return void;
		 */
		public function delete()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('users', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Create de l'object de base de données
			global $db;
			
			//Si on est pas admin
			if (!$_SESSION['admin'])
			{
				header('Location: ' . $this->generateUrl('users', 'showAll', array(
					'errormessage' => 'Vous devez être administrateur pour effectuer cette action.'
				)));
				return false;
			}

			$users_ids = $_GET;
			$db->deleteUsersIn($users_ids);
			header('Location: ' . $this->generateUrl('users'));		
		}
	}
