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
		 * Cette fonction retourne la fenetre du profile
		 */	
		public function byDefault()
		{
			$this->render('profile/default');
		}

		/**
		 * Cette fonction change le mot de passe de l'utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['password'] : Le nouveau mot de passe de l'utilisateur
		 * @param string $_POST['verif_password'] : La vérification du nouveau mot de passe de l'utilisateur
		 * @return void;
		 */
		public function changePassword($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			if (empty($_POST['password']) || empty($_POST['verif_password']) || $_POST['password'] != $_POST['verif_password'])
			{
				$_SESSION['errormessage'] = 'Les mots de passe ne correspondent pas.';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$user = $db->getFromTableWhere('users', ['email' => $_SESSION['email']]);
			$password = sha1($_POST['password']);
			
			if (!$db->updateTableWhere('users', ['password' => $password], ['id' => $user[0]['id']]))
			{
				$_SESSION['errormessage'] = 'Impossible de mettre à jour le mot de passe.';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$_SESSION['successmessage'] = 'Les données ont été mises à jour.';
			header('Location: ' . $this->generateUrl('profile'));
			return true;
		}

		/**
		 * Cette fonction change la valeur du champ "transfer" de l'utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['transfer'] : Le nouveau transfer de l'utilisateur
		 * @return void;
		 */
		public function changeTransfer($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			if (!isset($_POST['transfer']))
			{
				$_SESSION['errormessage'] = 'Vous devez renseigner un valeur';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$transfer = (boolean)$_POST['transfer'];
			if (!$db->updateTableWhere('users', ['transfer' => $transfer], ['email' => $_SESSION['email']]))
			{
				$_SESSION['errormessage'] = 'Impossible de mettre les données à jour.';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$_SESSION['transfer'] = $transfer;
			$_SESSION['successmessage'] = 'Les données ont été mises à jour.';
			header('Location: ' . $this->generateUrl('profile'));
			return true;
		}

		/**
		 * Cette fonction change l'email de l'utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['email'] : Le nouvel email de l'utilisateur
		 * @param string $_POST['verif_email'] : La vérification du nouvel email de l'utilisateur
		 * @return void;
		 */
		public function changeEmail($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			
			if (empty($_POST['mail']) || empty($_POST['verif_mail']) || $_POST['mail'] != $_POST['verif_mail'])
			{
				$_SESSION['errormessage'] = 'Les e-mails ne correspondent pas.';
				header('Location: ' . $this->generateUrl('profile', array(
					'errormessage' => 'Les e-mails ne correspondent pas.'
				)));
				return false;
			}

			$email = $_POST['mail'];

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$_SESSION['errormessage'] = 'L\'adresse e-mail est invalide.';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$user = $db->getFromTableWhere('users', ['email' => $_SESSION['email']]);

			if (!$db->updateTableWhere('users', ['email' => $email], ['id' => $user[0]['id']]))
			{
				$_SESSION['errormessage'] = 'Cette adresse e-mail est déjà utilisée.';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$_SESSION['email'] = $email;
			$_SESSION['successmessage'] = 'Les données ont été mises à jour.';
			header('Location: ' . $this->generateUrl('profile'));
			return true;
		}

		/**
		 * Cette fonction supprime l'utilisateur
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['delete_account'] : La vérification que l'on veux bien supprimer l'utilisateur
		 * @return void;
		 */
		public function delete($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			//Si l'utilisateur veux vraiment supprimer son compte
			if (empty($_POST['delete_account']))
			{
				$_SESSION['errormessage'] = 'Le compte n\'a pas été supprimé';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			if (!$db->deleteFromTableWhere('users', ['email' => $_SESSION['email']]))
			{
				$_SESSION['errormessage'] = 'Impossible de supprime le compte';
				header('Location: ' . $this->generateUrl('profile'));
				return false;
			}

			$this->logout();
			return true;	

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
