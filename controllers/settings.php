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
		 * Cette fonction permet de mettre à jour un réglage
		 * @param string $settingName : Le nom du réglage à modifier
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['settingValue'] : La nouvelle valeur du réglage
		 * @return boolean
		 */
		public function change($settingName, $csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			//On vérifie que la valeur est définie
			if (!isset($_POST['settingValue']))
			{
				$_SESSION['errormessage'] = 'Vous devez fournir une valeur pour le réglage !';
				header('Location: ' . $this->generateUrl('settings'));
				return false;
			}

			global $db;

			if (!$db->updateTableWhere('settings', ['value' => $_POST['settingValue']], ['name' => $settingName]))
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
