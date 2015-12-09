<?php
	/**
	 * Page des discussions
	 */
	class discussions extends Controller
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
		 * Cette fonction retourne toutes les discussions, sous forme d'un tableau permettant l'administration de ces contacts
		 */	
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;
			
			//Recupération des nombres des 4 panneaux d'accueil
			$discussions = $db->getDiscussions();

			foreach ($discussions as $key => $discussion)
			{
				if (!$contacts = $db->getFromTableWhere('contacts', ['number' => $discussion['number']]))
				{
					continue;
				}

				$discussions[$key]['contact'] = $contacts[0]['name'];
			}

			$this->render('discussions/default', array(
				'discussions' => $discussions,
			));
		}
		
		/**
		 * Cette fonction permet d'afficher la discussion avec un numero
		 * @param string $number : La numéro de téléphone avec lequel on discute
		 */
		public function show ($number)
		{
			global $db;

			$contact = '';

			if ($contacts = $db->getFromTableWhere('contacts', ['number' => $number]))
			{
				$contact = $contacts[0]['name'];
			}

			$this->render('discussions/show', array(
				'number' => $number,
				'contact' => $contact,
			));
		}

		/**
		 * Cette fonction récupère l'ensemble des messages pour un numéro, recçus, envoyés, en cours
		 * @param string $number : Le numéro cible
		 * @param string $transactionId : Le numéro unique de la transaction ajax (sert à vérifier si la requete doit être prise en compte)
		 */
		function getmessages($number, $transactionId)
		{
			global $db;

			$now = new DateTime();
			$now = $now->format('Y-m-d H:i:s');

			$sendeds = $db->getFromTableWhere('sendeds', ['target' => $number], 'at');
			$receiveds = $db->getFromTableWhere('receiveds', ['send_by' => $number], 'at');
			$scheduleds = $db->getScheduledsBeforeDateForNumber($now, $number);

			$messages = [];

			foreach ($sendeds as $sended)
			{
				$messages[] = array(
					'date' => htmlspecialchars($sended['at']),
					'text' => htmlspecialchars($sended['content']),
					'type' => 'sended',
					'status' => ($sended['delivered'] ? 'delivered' : ($sended['failed'] ? 'failed' : '')),
				);
			}

			foreach ($receiveds as $received)
			{
				$messages[] = array(
					'date' => htmlspecialchars($received['at']),
					'text' => htmlspecialchars($received['content']),
					'type' => 'received',
					'md5'  => md5($received['at'] . $received['content']),
				);
			}

			foreach ($scheduleds as $scheduled)
			{
				$messages[] = array(
					'date' => htmlspecialchars($scheduled['at']),
					'text' => htmlspecialchars($scheduled['content']),
					'type' => 'inprogress',
				);
			}

			//On va trier le tableau des messages
			usort($messages, function($a, $b) {
				return strtotime($a["date"]) - strtotime($b["date"]);
			});

			//On récupère uniquement les 25 derniers messages sur l'ensemble
			$messages = array_slice($messages, -25);

			echo json_encode(['transactionId' => $transactionId, 'messages' => $messages]);
			return true;
		}

		/**
		 * Cette fonction permet d'envoyer facilement un sms à un numéro donné
		 * @param string $csrf : Le jeton csrf
		 * @param string $_POST['content'] : Le contenu du SMS
		 * @param string $_POST['numbers'] : Un tableau avec le numero des gens auxquel envoyer le sms
		 * @return json : Le statut de l'envoi
		 */
		function send ($csrf)
		{
			global $db;
			$return = ['success' => true, 'message' => ''];

			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$return['success'] = false;
				$return['message'] = 'Jeton CSRF invalide';
				echo json_encode($return);
				return false;
			}	

			$now = new DateTime();
			$now = $now->format('Y-m-d H:i:s');

			$_POST['date'] = $now;

			$scheduleds = new scheduleds();
			if (!$scheduleds->create('', true, true))
			{
				$return['success'] = false;
				$return['message'] = 'Impossible de créer le SMS';
				echo json_encode($return);
				return false;
			}

			$return['id'] = $_SESSION['discussion_wait_progress'][count($_SESSION['discussion_wait_progress']) - 1];

			echo json_encode($return);
			return true;
		}

		/**
		 * Cette fonction retourne les id des sms qui sont envoyés
		 * @return json : Tableau des ids des sms qui sont envoyés
		 */
		function checksendeds ()
		{
			global $db;

			$_SESSION['discussion_wait_progress'] = isset($_SESSION['discussion_wait_progress']) ? $_SESSION['discussion_wait_progress'] : [];

			$scheduleds = $db->getScheduledsIn($_SESSION['discussion_wait_progress']);

			//On va chercher à chaque fois si on a trouvé le sms. Si ce n'est pas le cas c'est qu'il a été envoyé
			$sendeds = [];
			foreach ($_SESSION['discussion_wait_progress'] as $key => $id)
			{
				$found = false;
				foreach ($scheduleds as $scheduled)
				{
					if ($id == $scheduled['id'])
					{
						$found = true;
					}
				}

				if (!$found)
				{
					unset($_SESSION['discussion_wait_progress'][$key]);
					$sendeds[] = $id;
				}
			}	

			echo json_encode($sendeds);
			return true;
		}

		/**
		 * Cette fonction retourne les messages reçus pour un numéro après la date $_SESSION['discussion_last_checkreceiveds']
		 * @param string $number : Le numéro de téléphone pour lequel on veux les messages
		 * @return json : Un tableau avec les messages
		 */
		function checkreceiveds ($number)
		{
			global $db;

			$now = new DateTime();
			$now = $now->format('Y-m-d H:i');
			
			$_SESSION['discussion_last_checkreceiveds'] = isset($_SESSION['discussion_last_checkreceiveds']) ? $_SESSION['discussion_last_checkreceiveds'] : $now;

			$receiveds = $db->getReceivedsSinceForNumberOrderByDate($_SESSION['discussion_last_checkreceiveds'], $number);

			//On va gérer le cas des messages en double en stockant ceux déjà reçus et en eliminant les autres
			$_SESSION['discussion_already_receiveds'] = isset($_SESSION['discussion_already_receiveds']) ? $_SESSION['discussion_already_receiveds'] : [];

			foreach ($receiveds as $key => $received)
			{
				//Sms jamais recu
				if (array_search($received['id'], $_SESSION['discussion_already_receiveds']) === false)
				{
					$_SESSION['discussion_already_receiveds'][] = $received['id'];
					continue;
				}

				//Sms déjà reçu => on le supprime des resultats
				unset($receiveds[$key]);
			}

			//On met à jour la date de dernière verif
			$_SESSION['discussion_last_checkreceiveds'] = $now;
			
			echo json_encode($receiveds);
		}
	}
