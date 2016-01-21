<?php
	/**
	 * Page des webhooks
	 */
	class webhooks extends Controller
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
		 * Cette fonction retourne toutes les webhooks, sous forme d'un tableau permettant l'administration de ces webhooks
		 */	
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;
			
			//Recupération des webhooks
			$webhooks = $db->getFromTableWhere('webhooks');

			$this->render('webhooks/default', array(
				'webhooks' => $webhooks,
			));
			
		}

		/**
		 * Cette fonction va supprimer une liste de webhooks
		 * @param int... $ids : Les id des webhooks à supprimer
		 * @return boolean;
		 */
		public function delete($csrf)
		{
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('webhooks'));
				return false;
			}

			//On récupère les ids comme étant tous les arguments de la fonction et on supprime le premier (csrf)
			$ids = func_get_args();
			unset($ids[0]);

			//Create de l'object de base de données
			global $db;
			
			$db->deleteWebhooksIn($ids);
			header('Location: ' . $this->generateUrl('webhooks'));		
			return true;
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un webhook
		 */
		public function add()
		{
			$this->render('webhooks/add');
		}

		/**
		 * Cette fonction retourne la page d'édition des webhook
		 * @param int... $ids : Les id des commandes à editer
		 */
		public function edit()
		{
			global $db;
			$ids = func_get_args();

			$webhooks = $db->getWebhooksIn($ids);
			$this->render('webhooks/edit', array(
				'webhooks' => $webhooks,
			));
		}

		/**
		 * Cette fonction insert une nouvelle commande
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['url'] : L'adresse url à laquelle on va envoyer la requête
		 * @param string $_POST['type'] : Le type de hook à ajouter
		 * @return boolean;
		 */
		public function create($csrf)
		{
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('webhooks'));
				return false;
			}

			global $db;

			$url = $_POST['url'];
			$type = $_POST['type'];

			if (!$db->insertIntoTable('webhooks', ['url' => $url, 'type' => $type]))
			{
				$_SESSION['errormessage'] = 'Impossible créer ce webhook.';
				header('Location: ' . $this->generateUrl('webhooks', 'add'));
				return false;
			}

			$db->insertIntoTable('events', ['type' => 'WEBHOOKS_ADD', 'text' => 'Ajout webhook : ' . $type . ' => ' . $url]);
			
			$_SESSION['successmessage'] = 'Le webhook a bien été créé.';
			header('Location: ' . $this->generateUrl('webhooks'));
			return true;

		}

		/**
		 * Cette fonction met à jour une liste de webhooks
		 * @param $csrf : Le jeton CSRF
		 * @param array $_POST['webhooks'] : Un tableau des webhooks avec leur nouvelle valeurs
		 * @return boolean;
		 */
		public function update($csrf)
		{
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('webhooks'));
				return false;
			}

			global $db;
			
			$errors = array(); //On initialise le tableau qui contiendra les erreurs rencontrés

			//Pour chaque webhook reçu, on boucle en récupérant son id (la clef), et le webhook lui-même (la value)
			foreach ($_POST['webhooks'] as $id => $webhook)
			{
				$db->updateTableWhere('webhooks', $webhook, ['id' => $id]);
			}

			$_SESSION['successmessage'] = 'Tout les webhooks ont été modifiés avec succès.';
			header('Location: ' . $this->generateUrl('webhooks'));
		}

		/**
		 * Cette méthode permet d'ajouter d'un coup toutes les requête d'un webhook à la queue des requête pour un type de webhook
		 * @param int $webhookType : Le type de webhook (une constante issue de internalConstants::WEBHOOK_TYPE)
		 * @param array $datas : Les données à envoyer avec la requête (si non définie, [])
		 * @return void
		 */
		public function _addWebhooksForType ($webhookType, $datas = [])
		{
			global $db;

			$webhooks = $db->getFromTableWhere('webhooks', ['type' => $webhookType]);

			foreach ($webhooks as $webhook)
			{
				$this->addWebhookQuery($webhook['url'], $datas);
			}
		}

		/**
		 * Cette méthode est appelée pour ajouter une requête issue d'un webhook à la queue
		 * @param string $url : L'url à laquelle envoyer la requête
		 * @param array $datas : Les données à envoyer avec la requête (si non définie, [])
		 * @return boolean : true si on reussi à l'ajouter, false sinon
		 */
		private function addWebhookQuery ($url, $datas = [])
		{
			global $db;

			if (!$db->insertIntoTable('webhook_queries', ['url' => $url, 'datas' => json_encode($datas)]))
			{
				return false;
			}

			return true;
		}
	}
