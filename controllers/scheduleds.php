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
		 * Cette fonction est alias de showAll()
		 */	
		public function byDefault()
		{
			$this->showAll();
		}
		
		/**
		 * Cette fonction retourne tous les sms programmés, sous forme d'un tableau permettant l'administration de ces sms
		 * @return void;
		 */
		public function showAll()
		{
			//Creation de l'object de base de données
			global $db;
			

			$scheduleds = $db->getAll('scheduleds');
			$this->render('scheduleds', array(
				'scheduleds' => $scheduleds,
			));
		}

		/**
		 * Cette fonction supprime une liste de groupes
		 * @return void;
		 */
		public function delete()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Create de l'object de base de données
			global $db;
			
			$scheduleds_ids = $_GET;
			$db->deleteScheduledsIn($scheduleds_ids);
			header('Location: ' . $this->generateUrl('scheduleds'));		
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un group
		 */
		public function add()
		{
			$now = new DateTime();
			$babyonemoretime = new DateInterval('PT1M'); //Haha, i'm so a funny guy
			$now->add($babyonemoretime);	
			$now = $now->format('Y-m-d H:i');
			$this->render('addScheduled', array(
				'now' => $now
			));
		}

		/**
		 * Cette fonction retourne la page d'édition des sms programmés
		 */
		public function edit()
		{
			global $db;
			
			$scheduleds = $db->getScheduledsIn($_GET);
			//Pour chaque groupe, on récupère les contacts liés
			foreach ($scheduleds as $key => $scheduled)
			{
				$date = new DateTime($scheduled['at']);
				$scheduleds[$key]['at'] = $date->format('Y-m-d H:i');

				$scheduleds[$key]['numbers'] = $db->getNumbersForScheduled($scheduled['id']);	
				$scheduleds[$key]['contacts'] = $db->getContactsForScheduled($scheduled['id']);	
				$scheduleds[$key]['groups'] = $db->getGroupsForScheduled($scheduled['id']);	
			}

			$this->render('editScheduleds', array(
				'scheduleds' => $scheduleds,
			));
		}

		/**
		 * Cette fonction insert un nouveau SMS programmé
		 * @param optionnal boolean $api : Si vrai (faux par défaut), on retourne des réponses au lieu de rediriger
		 */
		public function create($api = false)
		{
			if (!$api)
			{
				//On vérifie que le jeton csrf est bon
				if (!internalTools::verifyCSRF())
				{
					header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
						'errormessage' => 'Jeton CSRF invalide !'
					)));
					return true;
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
					header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
						'errormessage' => 'Pas de texte pour ce SMS !'
					)));
				}
				return false;
			}

			//Si pas numéros, contacts, ou groupes cibles
			if (!$numbers && !$contacts && !$groups)
			{
				if (!$api)
				{
					header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
						'errormessage' => 'Pas numéro, de contacts, ni de groupes définis pour envoyer ce SMS !'
					)));
				}
				return false;
			}

			if (!internalTools::validateDate($date, 'Y-m-d H:i'))
			{
				if (!$api)
				{
					header('Location: ' . $this->generateUrl('scheduleds', 'add', array(
						'errormessage' => 'La date renseignée est invalide.'
					)));
				}

				return false;
			}		

			if ($db->createScheduleds($date, $content))
			{
				$id_scheduled = $db->lastId();
				$db->createEvent('SCHEDULED_ADD', 'Ajout d\'un SMS pour le ' . $date);	
				$errors = false;

				foreach ($numbers as $number)
				{
					if ($number = internalTools::parsePhone($number))
					{
						$db->createScheduleds_numbers($id_scheduled, $number);
					}
					else
					{
						$errors = true;
					}
				}

				foreach ($contacts as $id_contact)
				{
					if (!$db->createScheduleds_contacts($id_scheduled, $id_contact))
					{
						$errors = true;
					}
				}

				foreach ($groups as $id_group)
				{
					if (!$db->createScheduleds_groups($id_scheduled, $id_group))
					{
						$errors = true;
					}
				}

				if ($errors)
				{
					if (!$api)
					{
						header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
							'errormessage' => 'Le SMS a bien été créé, mais certains numéro ne sont pas valides.'
						)));
					}
					return true;
				}
				else
				{
					if (!$api)
					{
						header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
							'successmessage' => 'Le SMS a bien été créé.'
						)));
					}
					return true;
				}
			}

			if (!$api)
			{
				header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
					'errormessage' => 'Impossible de créer ce SMS.'
				)));
			}
			return false;
		}

		/**
		 * Cette fonction met à jour une liste de sms
		 */
		public function update()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			
			$errors = false;
			//Pour chaque SMS programmé reçu, on boucle en récupérant son id (la clef), et sont contenu
			foreach ($_POST['scheduleds'] as $id_scheduled => $scheduled)
			{
				$date = $scheduled['date'];
				if (!internalTools::validateDate($date, 'Y-m-d H:i'))
				{
					header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
						'errormessage' => 'La date renseignée pour le SMS numéro ' . $scheduled['id'] . ' est invalide.'
					)));
					return true;
				}		

				//Si la date fournie est passée, on la change pour dans 2 minutes	
				$objectDate = DateTime::createFromFormat('Y-m-d H:i', $date);

				$db->updateScheduled($id_scheduled, $date, $scheduled['content'], false); //On met à jour le sms

				$db->deleteScheduleds_numbersForScheduled($id_scheduled); //On supprime tous les numéros pour ce SMS
				$db->deleteScheduleds_contactsForScheduled($id_scheduled); //On supprime tous les contacts pour ce SMS
				$db->deleteScheduleds_GroupsForScheduled($id_scheduled); //On supprime tous les groupes pour ce SMS

				//Pour chaque numéro, on va le vérifier et l'ajouter au sms
				foreach ($scheduled['numbers'] as $number)
				{
					if (internalTools::parsePhone($number))
					{
						$db->createScheduleds_numbers($id_scheduled, $number);
					}
					else
					{
						$errors = true;
					}
				}

				//Pour chaque contact, on va l'ajouter au sms
				foreach ($scheduled['contacts'] as $id_contact)
				{
					if (!$db->createScheduleds_contacts($id_scheduled, $id_contact))
					{
						$errors = true;
					}
				}

				//Pour chaque groupe, on va l'ajouter au sms
				foreach ($scheduled['groups'] as $id_group)
				{
					if (!$db->createScheduleds_groups($id_scheduled, $id_group))
					{
						$errors = true;
					}
				}
			}
			
			if (!$errors)
			{
				$message = 'Tous les SMS ont été modifiés avec succès.';
				header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
					'successmessage' => $message,
				)));
			}
			else
			{
				$message = 'Tous les SMS ont été modifiés mais certaines données incorrects ont été ignorées.';
				header('Location: ' . $this->generateUrl('scheduleds', 'showAll', array(
					'errormessage' => $message,
				)));
			}
		}
	}
