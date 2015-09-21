<?php
	/**
	 * Page de connexion
	 */
	class connect extends Controller
	{
		/**
		 * Cette fonction retourne la fenetre de connexion
		 */	
		public function byDefault()
		{
			$this->render('connect/login');
		}

		/**
		 * Cette fonction retourne la fenetre de changement de password
		 * @return void;
		 */
		public function forgetPassword()
		{
			$this->render('forgetPassword');
		}
	
		/**
		 * Cette fonction connecte un utilisateur, et le redirige sur la page d'accueil
		 * @param string $_POST['mail'] : L'email de l'utilisateur
		 * @param string $_POST['password'] : Le mot de passe de l'utilisateur
		 * @return void
		 */
		public function connection()
		{
			
			//Creation de l'object de base de données
			global $db;
			
			$email = $_POST['mail'];
			$password = $_POST['password'];

			if (!$users = $db->getFromTableWhere('users', ['email' => $email]))
			{
				$_SESSION['errormessage'] = 'Identifiants incorrects.';
				header('Location: ' . $this->generateUrl('connect', 'login'));
				return false;
			}

			$user = $users[0];

			if (sha1($password) != $user['password'])
			{
				$_SESSION['errormessage'] = 'Cet e-mail n\'existe pas.';
				header('Location: ' . $this->generateUrl('connect', 'login'));
				return false;	
			}

			$_SESSION['connect'] = true;
			$_SESSION['admin'] = $user['admin'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['transfer'] = $user['transfer'];
			$_SESSION['csrf'] = str_shuffle(uniqid().uniqid());
			header('Location: ' . $this->generateUrl(''));
			return true;
		}

		/**
		 * Cette fonction change le mot de passe d'un utilisateur à partir de son email. Un mot de passe aléatoire et généré, et lui est envoyé
		 * @param string $_POST['mail'] : L'email de l'utilisateur cible
		 * @return void;
		 */
		public function changePassword()
		{
			//Creation de l'object de base de données
			global $db;
			
			$email = $_POST['mail'];

			if (!$users = $db->getFromTableWhere('users', ['email' => $email]))
			{
				$_SESSION['errormessage'] = 'Cet e-mail n\'existe pas.';
				header('Location: ' . $this->generateUrl('connect', 'forgetPassword'));
				return false;
			}

			$password = internalTools::generatePassword(rand(8,12));
			$message  = "Vous avez demandé un nouveau mot de passe pour le site " . HTTP_PWD . ".\n";
			$message  = "Votre nouveau mot de passe a été généré aléatoirement, et n'est connu que de vous. Le voici : \n";
			$message .= "Nouveau mot de passe : " . $password . "\n\n";
			$message .= "-------------------------------------\n";
			$message .= "Pour plus d'informations sur le système RaspiSMS, rendez-vous sur le site http://raspbian-france.fr\n";
			if (!mail($email, 'RaspiSMS - Recuperation de mot de passe', $message))
			{
				$_SESSION['errormessage'] = 'Impossible d\'envoyer les nouveaux identifiants.';
				header('Location: ' . $this->generateUrl('connect', 'forgetPassword'));
				return false;
			}

			$new_password = sha1($password);
			if (!$db->updateTableWhere('users', ['email' => $user['email'], 'password' => $new_password, 'admin' => $user['admin']], ['id' => $user['id']]))
			{
				$_SESSION['errormessage'] = 'Impossible de mettre à jour le mot de passe.';
				header('Location: ' . $this->generateUrl('connect', 'login'));
				return false;
			}

			$_SESSION['successmessage'] = 'Un nouveau mot de passe vous a été envoyé par mail.';
			header('Location: ' . $this->generateUrl('connect', 'login'));
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
		}
	}
