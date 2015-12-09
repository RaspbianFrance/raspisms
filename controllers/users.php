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
		 * Cette fonction retourne tous les utilisateurs, sous forme d'un tableau permettant l'administration de ces utilisateurs
		 */	
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;
			
			
			//Récupération des utilisateurs
			$users = $db->getFromTableWhere('users');

			$this->render('users/default', array(
				'users' => $users,
			));
		}
		
		/**
		 * Cette fonction retourne la page d'ajout d'un utilisateur
		 */
		public function add()
		{
			$this->render('users/add');
		}

		/**
		 * Cette fonction insert un nouvel utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['email'] : L'email de l'utilisateur
		 * @param string $_POST['email_confirm'] : Confirmation de l'email de l'utilisateur
		 * @param string $_POST['password'] : Le mot de passe de l'utilisateur (si vide, générer automatiquement)
		 * @param string $_POST['password_confirm'] : Confirmation du mot de passe de l'utilisateur
		 * @param boolean $_POST['admin'] : Optionnel : Si l'utilisateur est admin. Par défaut non
		 */
		public function create($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('users', 'add'));
				return false;
			}

			global $db;
			
			if (!isset($_POST['email']) || !isset($_POST['email_confirm']) || ($_POST['email'] != $_POST['email_confirm']))
			{
				$_SESSION['errormessage'] = 'Les e-mails fournis ne correspondent pas.';
				header('Location: ' . $this->generateUrl('users', 'add'));
				return false;
			}

			$email = $_POST['email'];
			
			$no_crypt_password = internalTools::generatePassword(rand(8,12));

			if ($_POST['password'])
			{
				if ($_POST['password'] != $_POST['password_confirm'])
				{
					$_SESSION['errormessage'] = 'Les mots de passes fournis ne correspondent pas.';
					header('Location: ' . $this->generateUrl('users', 'add'));
					return false;
				}

				$no_crypt_password = $_POST['password'];
			}

			$password = sha1($no_crypt_password);

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$_SESSION['errormessage'] = 'L\'e-mail fourni présente un format incorrect.';
				header('Location: ' . $this->generateUrl('users', 'add'));
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

			if (!mail($email, 'Identifiants RaspiSMS', $message))
			{
				$_SESSION['errormessage'] = 'Impossible d\'envoyer le mail d\'inscription à l\'utilisateur. Le compte n\'a donc pas été créé.';
				header('Location: ' . $this->generateUrl('users', 'add'));
				return false;
			}

			if (!$db->insertIntoTable('users', ['email' => $email, 'password' => $password, 'admin' => $admin]))
			{
				$_SESSION['errormessage'] = 'Impossible de créer cet utilisateur.';
				header('Location: ' . $this->generateUrl('users', 'add'));
				return false;
			}

			$db->insertIntoTable('events', ['type' => 'USER_ADD', 'text' => 'Ajout de l\'utilisateur : ' . $email]);
			$_SESSION['successmessage'] = 'L\'utilisateur a bien été créé.';
			header('Location: ' . $this->generateUrl('users'));
			return true;
		}

		/**
		 * Cette fonction supprimer une liste d'utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param int... $ids : Les id des commandes à supprimer
		 * @return boolean;
		 */
		public function delete($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('users'));
				return false;
			}

			//On récupère les ids comme étant tous les arguments de la fonction et on supprime le premier (csrf)
			$ids = func_get_args();
			unset($ids[0]);

			//Create de l'object de base de données
			global $db;
			
			//Si on est pas admin
			if (!$_SESSION['admin'])
			{
				$_SESSION['errormessage'] = 'Vous devez être administrateur pour effectuer cette action.';
				header('Location: ' . $this->generateUrl('users'));
				return false;
			}

			$db->deleteUsersIn($ids);
			header('Location: ' . $this->generateUrl('users'));
			return true;
		}
	}
