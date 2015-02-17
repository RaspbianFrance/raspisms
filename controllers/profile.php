<?php
	/**
	 * Page de profile
	 */
	class profile extends Controller
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
		 * Cette fonction est alias de show()
		 */	
		public function byDefault()
		{
			$this->show();
		}
		
		/**
		 * Cette fonction retourne la fenetre du profile
		 * @return void;
		 */
		public function show()
		{
			//Creation de l'object de base de données
			global $db;
			
			
			$this->render('profile');
		}

		/**
		 * Cette fonction change le mot de passe de l'utilisateur
		 * @param string $_POST['password'] : Le nouveau mot de passe de l'utilisateur
		 * @param string $_POST['verif_password'] : La vérification du nouveau mot de passe de l'utilisateur
		 * @return void;
		 */
		public function changePassword()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('profile', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Creation de l'object de base de données
			global $db;
			
			
			if (empty($_POST['password']) || empty($_POST['verif_password']) || $_POST['password'] != $_POST['verif_password'])
			{
				header('Location: ' . $this->generateUrl('profile', 'show', array(
					'errormessage' => 'Les mots de passe ne correspondent pas.'
				)));
				return false;
			}

			$user = $db->getUserFromEmail($_SESSION['email']);

			$password = sha1($_POST['password']);
			
			if ($db->updateUser($user['id'], $user['email'], $password, $user['admin']))
			{
				header('Location: ' . $this->generateUrl('profile', 'show', array(
					'successmessage' => 'Les données ont été mises à jour.'
				)));
				return false;
			}
		}

		/**
		 * Cette fonction change l'email de l'utilisateur
		 * @param string $_POST['email'] : Le nouvel email de l'utilisateur
		 * @param string $_POST['verif_email'] : La vérification du nouvel email de l'utilisateur
		 * @return void;
		 */
		public function changeEmail()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('profile', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Creation de l'object de base de données
			global $db;
			
			
			if (empty($_POST['mail']) || empty($_POST['verif_mail']) || $_POST['mail'] != $_POST['verif_mail'])
			{
				header('Location: ' . $this->generateUrl('profile', 'show', array(
					'errormessage' => 'Les e-mails ne correspondent pas.'
				)));
				return false;
			}

			$email = $_POST['mail'];

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				header('Location: ' . $this->generateUrl('profile', 'show', array(
					'errormessage' => 'L\'adresse e-mail est invalide.'
				)));
				return false;
			}

			$user = $db->getUserFromEmail($_SESSION['email']);

			if ($db->updateUser($user['id'], $email, $user['password'], $user['admin']))
			{
				$_SESSION['email'] = $email;
				header('Location: ' . $this->generateUrl('profile', 'show', array(
					'successmessage' => 'Les données ont été mises à jour.'
				)));
				return false;
			}

			header('Location: ' . $this->generateUrl('profile', 'show', array(
				'errormessage' => 'Cette adresse e-mail est déjà utilisée.'
			)));
			return false;
		}

		/**
		 * Cette fonction supprime l'utilisateur
		 * @param string $_POST['delete_account'] : La vérification que l'on veux bien supprimer l'utilisateur
		 * @return void;
		 */
		public function delete()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('profile', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Creation de l'object de base de données
			global $db;
			
			
			//Si l'utilisateur veux vraiment supprimer son compte
			if (!empty($_POST['delete_account']))
			{
				$user = $db->getUserFromEmail($_SESSION['email']); //On récupère l'utilisateur en base
				$db->deleteUsersIn(array($user['id'])); //On supprime l'utilisateur
				$this->logout();
				return true;	
			}

			header('Location: ' . $this->generateUrl('profile', 'show', array(
				'errormessage' => 'Le compte n\'a pas été supprimé'
			)));
			return false;
		}

		/**
		 * Cette fonction déconnecte un utilisateur et le renvoie sur la page d'accueil
		 * @return void
		 */
		public function logout()
		{
			session_unset();
			session_destroy();
			header('Location: ' . $this->generateUrl(''));
			return true;
		}
	}
