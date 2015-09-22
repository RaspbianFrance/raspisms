<?php
	/**
	 * Page des réglages de RaspiSMS
	 */
	class settings extends Controller
	{
		/**
		 * Cette fonction est appelée avant toute les autres : 
		 * Elle vérifie que l'utilisateur est bien connecté && est admin
		 * @return void;
		 */
		public function before()
		{
			internalTools::verifyConnect();

			if (!$_SESSION['admin'])
			{
				die();
			}
		}

		/**
		 * Cette fonction retourne la page d'accueil des réglages de RaspiSMS
		 */	
		public function byDefault()
		{
			return $this->render('settings/default');
		}

		/**
		 * Cette fonction permet de mettre à jour l'activation ou la désactivation du transfer des SMS
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['transfer'] : Le nouveau transfer
		 * @return void;
		 */
		public function changeTransfer($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			if (!isset($_POST['transfer']))
			{
				$_SESSION['errormessage'] = 'Vous devez renseigner un valeur';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			$transfer = (int)$_POST['transfer'];

			if (!$db->updateTableWhere('settings', ['value' => $transfer], ['name' => 'transfer']))
			{
				$_SESSION['errormessage'] = 'Impossible de mettre les données à jour.';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			$_SESSION['successmessage'] = 'Les données ont été mises à jour.';
			header('Location: ' . $this->generateUrl('settings'));
			return true;
		}

		/**
		 * Cette fonction permet de mettre à jour l'activation ou la désactivation de SMS-STOP
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['stop'] : Le nouveau stop
		 * @return void;
		 */
		public function changeSmsStop($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			//Creation de l'object de base de données
			global $db;
			
			if (!isset($_POST['sms_stop']))
			{
				$_SESSION['errormessage'] = 'Vous devez renseigner un valeur';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			$stop = (int)$_POST['sms_stop'];

			if (!$db->updateTableWhere('settings', ['value' => $stop], ['name' => 'sms_stop']))
			{
				$_SESSION['errormessage'] = 'Impossible de mettre les données à jour.';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			$_SESSION['successmessage'] = 'Les données ont été mises à jour.';
			header('Location: ' . $this->generateUrl('settings'));
			return true;
		}
	}
