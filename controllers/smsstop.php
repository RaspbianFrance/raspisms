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

			$this->render('smsstop/default', array(
				'smsStops' => $smsStops,
				'page' => $page,
				'limit' => $limit,
				'nbResults' => count($smsStops),
			));
		}
	}
