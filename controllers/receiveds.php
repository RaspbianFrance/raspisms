<?php
	/**
	 * Page des SMS reçus
	 */
	class receiveds extends Controller
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
		 * Cette fonction retourne tous les SMS envoyés, sous forme d'un tableau
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
			

			//Récupération des SMS envoyés triés par date, du plus récent au plus ancien, par paquets de $limit, en ignorant les $offset premiers
			$receiveds = $db->getFromTableWhere('receiveds', [], 'at', true, $limit, $offset);

			foreach ($receiveds as $key => $received)
			{
				if (!$contacts = $db->getFromTableWhere('contacts', ['number' => $received['send_by']]))
				{
					continue;
				}	

				$receiveds[$key]['send_by'] = $contacts[0]['name'] . ' (' . $received['send_by'] . ')';
			}

			return $this->render('receiveds/showAll', array(
				'receiveds' => $receiveds,
				'page' => $page,
				'limit' => $limit,
				'nbResults' => count($receiveds),
			));
		}

		/**
		 * Cette fonction retourne tous les SMS reçus aujourd'hui pour la popup
		 * @return json : Un tableau des sms
		 */
		public function popup ()
		{
			global $db;
			$now = new DateTime();
			$receiveds = $db->getReceivedsSince($now->format('Y-m-d'));

			foreach ($receiveds as $key => $received)
			{
				if (!$contacts = $db->getFromTableWhere('contacts', ['number' => $received['send_by']]))
				{
					continue;
				}	

				$receiveds[$key]['send_by'] = $contacts[0]['name'] . ' (' . $received['send_by'] . ')';
			}
									   
			$nbReceiveds = count($receiveds);
			
			if (!isset($_SESSION['popup_nb_receiveds']) || ($_SESSION['popup_nb_receiveds'] > $nbReceiveds))
			{
				$_SESSION['popup_nb_receiveds'] = $nbReceiveds;
			}

			$newlyReceiveds = array_slice($receiveds, $_SESSION['popup_nb_receiveds']);

			echo json_encode($newlyReceiveds);
			$_SESSION['popup_nb_receiveds'] = $nbReceiveds;
			return true;
		}

		/**
		 * Cette fonction supprimer une liste de sms reçus
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
				header('Location: ' . $this->generateUrl('receiveds'));
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
				header('Location: ' . $this->generateUrl('receiveds'));
				return false;
			}

			$db->deleteReceivedsIn($ids);
			header('Location: ' . $this->generateUrl('receiveds'));
			return true;
		}
	}
