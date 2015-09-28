<?php
	/**
	 * Page des sms programmés
	 */
	class scheduleds extends Controller
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
		 * Cette fonction retourne tous les sms programmés, sous forme d'un tableau permettant l'administration de ces sms
		 */	
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;

			$scheduleds = $db->getFromTableWhere('scheduleds');
			$this->render('scheduleds/default', array(
				'scheduleds' => $scheduleds,
			));
		}

		/**
		 * Cette fonction supprime une liste de groupes
		 * @param $csrf : Le jeton CSRF
		 * @param int... $ids : Les id des commandes à supprimer
		 * @return boolean;
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

			//On récupère les ids comme étant tous les arguments de la fonction et on supprime le premier (csrf)
			$ids = func_get_args();
			unset($ids[0]);

			//Create de l'object de base de données
			global $db;
			
			$db->deleteScheduledsIn($ids);
			header('Location: ' . $this->generateUrl('scheduleds'));
			return true;
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un group
		 */
		public function add()
		{
			$now = new DateTime();
			$babyonemoretime = new DateInterval('PT1M'); //Haha, i'm so a funny guy
			$now->sub($babyonemoretime);	
			$now = $now->format('Y-m-d H:i');
			return $this->render('scheduleds/add', array(
				'now' => $now
			));
		}

		/**
		 * Cette fonction retourne la page d'édition des sms programmés
		 * @param int... $ids : Les id des commandes à supprimer
		 */
		public function edit()
		{
			global $db;

			//On récupère les ids comme étant tous les arguments de la fonction et on supprime le premier (csrf)
			$ids = func_get_args();
			
			$scheduleds = $db->getScheduledsIn($ids);
			//Pour chaque groupe, on récupère les contacts liés
			foreach ($scheduleds as $key => $scheduled)
			{
				$date = new DateTime($scheduled['at']);
				$scheduleds[$key]['at'] = $date->format('Y-m-d H:i');

				$scheduleds[$key]['numbers'] = [];
				$numbers = $db->getNumbersForScheduled($scheduled['id']);
				foreach ($numbers as $number)
				{
					$scheduleds[$key]['numbers'][] = $number['number'];
				}

				$scheduleds[$key]['contacts'] = [];
				$contacts = $db->getContactsForScheduled($scheduled['id']);
				foreach ($contacts as $contact)
				{
					$scheduleds[$key]['contacts'][] = (int)$contact['id'];
				}

				$scheduleds[$key]['groups'] = [];
				$groups = $db->getGroupsForScheduled($scheduled['id']);
				foreach ($groups as $group)
				{
					$scheduleds[$key]['groups'][] = (int)$group['id'];
				}
			}

			$this->render('scheduleds/edit', array(
				'scheduleds' => $scheduleds,
			));
		}

		/**
		 * Cette fonction insert un nouveau SMS programmé
		 * @param $csrf : Le jeton CSRF
		 * @param optionnal boolean $api : Si vrai (faux par défaut), on retourne des réponses au lieu de rediriger
		 * @param string $_POST['date'] : La date a la quelle de sms devra être envoyé
		 * @param string $_POST['content'] : Le contenu du SMS
		 * @param string $_POST['numbers'] : Un tableau avec le numero des gens auxquel envoyer le sms
		 * @param string $_POST['contacts'] : Un tableau avec les ids des contacts auxquels envoyer le sms
		 * @param string $_POST['groups'] : Un tableau avec les ids des groupes auxquels envoyer le sms
		 * @return boolean;
		 */
		public function create($csrf = '', $api = false, $discussion = false)
		{
			if (!$api)
			{
				//On vérifie que le jeton csrf est bon
				if (!internalTools::verifyCSRF($csrf))
				{
					$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
					header('Location: ' . $this->generateUrl('profile'));
					return false;
				}
			}

			global $db;

			$date = $_POST['date'];
			$content = $_POST['content'];
			$numbers = (isset($_POST['numbers'])) ? $_POST['numbers'] : array();
			$contacts = (isset($_POST['contacts'])) ? $_POST['contacts'] : array();
			$groups = (isset($_POST['groups'])) ? $_POST['groups'] : array();

			//Si pas de contenu dans le SMS
			if (!$content)
			{
				if (!$api)
				{
					$_SESSION['errormessage'] = 'Pas de texte pour ce SMS !';
					header('Location: ' . $this->generateUrl('scheduleds'));
				}
				return false;
			}

			//Si pas numéros, contacts, ou groupes cibles
			if (!$numbers && !$contacts && !$groups)
			{
				if (!$api)
				{
					$_SESSION['errormessage'] = 'Pas numéro, de contacts, ni de groupes définis pour envoyer ce SMS !';
					header('Location: ' . $this->generateUrl('scheduleds'));
				}
				return false;
			}

			if (!internalTools::validateDate($date, 'Y-m-d H:i:s') && !internalTools::validateDate($date, 'Y-m-d H:i'))
			{
				if (!$api)
				{
					$_SESSION['errormessage'] = 'La date renseignée est invalide.';
					header('Location: ' . $this->generateUrl('scheduleds'));
				}

				return false;
			}		

			if (!$db->insertIntoTable('scheduleds', ['at' => $date, 'content' => $content, 'progress' => false]))
			{
				if (!$api)
				{
					$_SESSION['errormessage'] = 'Impossible de créer ce SMS.';
					header('Location: ' . $this->generateUrl('scheduleds'));
				}
				return false;
			}

			$id_scheduled = $db->lastId();

			if ($discussion)
			{
				$_SESSION['discussion_wait_progress'] = isset($_SESSION['discussion_wait_progress']) ? $_SESSION['discussion_wait_progress'] : [];
				$_SESSION['discussion_wait_progress'][] = $id_scheduled;
			}

			$db->insertIntoTable('events', ['type' => 'SCHEDULED_ADD', 'text' => 'Ajout d\'un SMS pour le ' . $date]);	
			$errors = false;

			foreach ($numbers as $number)
			{
				if (!$number = internalTools::parsePhone($number))
				{
					$errors = true;
					continue;
				}

				if (!$db->insertIntoTable('scheduleds_numbers', ['id_scheduled' => $id_scheduled, 'number' => $number]))
				{
					$errors = true;
				}
			}

			foreach ($contacts as $id_contact)
			{
				if (!$db->insertIntoTable('scheduleds_contacts', ['id_scheduled' => $id_scheduled, 'id_contact' => $id_contact]))
				{
					$errors = true;
				}
			}

			foreach ($groups as $id_group)
			{
				if (!$db->insertIntoTable('scheduleds_groups', ['id_scheduled' => $id_scheduled, 'id_group' => $id_group]))
				{
					$errors = true;
				}
			}

			if ($errors)
			{
				if (!$api)
				{
					$_SESSION['errormessage'] = 'Le SMS a bien été créé, mais certains numéro ne sont pas valides.';
					header('Location: ' . $this->generateUrl('scheduleds'));
				}
				return true;
			}

			if (!$api)
			{
				$_SESSION['successmessage'] = 'Le SMS a bien été créé.';
				header('Location: ' . $this->generateUrl('scheduleds'));
			}
			return true;
		}

		/**
		 * Cette fonction met à jour une liste de sms
		 * @param $csrf : Le jeton CSRF
		 * @param array $_POST['scheduleds'] : Un tableau contenant les sms avec leurs nouvelles valeurs
		 */
		public function update($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['successmessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('scheduleds'));
				return false;
			}

			global $db;
			
			$errors = false;
			//Pour chaque SMS programmé reçu, on boucle en récupérant son id (la clef), et sont contenu
			foreach ($_POST['scheduleds'] as $id_scheduled => $scheduled)
			{
				$date = $scheduled['date'];
				if (!internalTools::validateDate($date, 'Y-m-d H:i:s') && !internalTools::validateDate($date, 'Y-m-d H:i'))
				{
					$_SESSION['errormessage'] = 'La date renseignée pour le SMS numéro ' . $scheduled['id'] . ' est invalide.';
					header('Location: ' . $this->generateUrl('scheduleds'));
					return false;
				}		

				//Si la date fournie est passée, on la change pour dans 2 minutes	
				$objectDate = DateTime::createFromFormat('Y-m-d H:i', $date);

				$db->updateTableWhere('scheduleds', ['content' => $scheduled['content'], 'at' => $date], ['id' => $id_scheduled]);

				$db->deleteScheduleds_numbersForScheduled($id_scheduled); //On supprime tous les numéros pour ce SMS
				$db->deleteScheduleds_contactsForScheduled($id_scheduled); //On supprime tous les contacts pour ce SMS
				$db->deleteScheduleds_GroupsForScheduled($id_scheduled); //On supprime tous les groupes pour ce SMS

				foreach ($scheduled['numbers'] as $number)
				{
					if (!$number = internalTools::parsePhone($number))
					{
						$errors = true;
						continue;
					}

					if (!$db->insertIntoTable('scheduleds_numbers', ['id_scheduled' => $id_scheduled, 'number' => $number]))
					{
						$errors = true;
					}
				}

				foreach ($scheduled['contacts'] as $id_contact)
				{
					if (!$db->insertIntoTable('scheduleds_contacts', ['id_scheduled' => $id_scheduled, 'id_contact' => $id_contact]))
					{
						$errors = true;
					}
				}

				foreach ($scheduled['groups'] as $id_group)
				{
					if (!$db->insertIntoTable('scheduleds_groups', ['id_scheduled' => $id_scheduled, 'id_group' => $id_group]))
					{
						$errors = true;
					}
				}
			}
			
			if ($errors)
			{
				$_SESSION['errormessage'] = 'Tous les SMS ont été modifiés mais certaines données incorrects ont été ignorées.';
				header('Location: ' . $this->generateUrl('scheduleds'));
				return false;
			}
			else
			{
				$_SESSION['successmessage'] = 'Tous les SMS ont été modifiés avec succès.';
				header('Location: ' . $this->generateUrl('scheduleds'));
				return true;
			}
		}
	}
