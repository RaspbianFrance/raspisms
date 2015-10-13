<?php
	/**
	 * Page des SMS envoyés
	 */
	class sendeds extends Controller
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
			$sendeds = $db->getFromTableWhere('sendeds', [], 'at', true, $limit, $offset);

			$this->render('sendeds/showAll', array(
				'sendeds' => $sendeds,
				'page' => $page,
				'limit' => $limit,
				'nbResults' => count($sendeds),
			));
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
				header('Location: ' . $this->generateUrl('sendeds'));
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
				header('Location: ' . $this->generateUrl('sendeds'));
				return false;
			}

			$db->deleteSendedsIn($ids);
			header('Location: ' . $this->generateUrl('sendeds'));
			return true;
		}
	}
