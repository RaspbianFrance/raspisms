<?php
	/**
	 * Page des SMS STOP
	 */
	class smsstop extends Controller
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
		 * Cette fonction retourne tous les numéros sous sms stop, sous forme d'un tableau permettant l'administration de ces numéros
		 * @param int $page : La page à consulter. Par défaut 0
		 * @return void;
		 */
		public function showAll($page = 0)
		{
			//Creation de l'object de base de données
			global $db;

			$page = (int)($page < 0 ? $page = 0 : $page);
			$limit = 25;
			$offset = $limit * $page;
			
			//Récupération des sms-stop, par paquets de $limit, en ignorant les $offset premiers
			$smsStops = $db->getFromTableWhere('sms_stop', [], false, true, $limit, $offset);

			$this->render('smsstop/showAll', array(
				'smsStops' => $smsStops,
				'page' => $page,
				'limit' => $limit,
				'nbResults' => count($smsStops),
			));
		}

		/**
		 * Cette fonction supprimer une liste de sms stop
		 * @param $csrf : Le jeton CSRF
		 * @param int... $ids : Les id des sms à supprimer
		 * @return boolean;
		 */
		public function delete($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('smsstop'));
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
				header('Location: ' . $this->generateUrl('smsstop'));
				return false;
			}

			$db->deleteSmsStopsIn($ids);
			header('Location: ' . $this->generateUrl('smsstop'));
			return true;
		}
	}
