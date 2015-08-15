<?php
	class internalConsole extends Controller
	{

		/**
		 * Cette fonction retourne l'aide de la console
		 */
		public function help()
		{
			//On définit les commandes disponibles
			$commands = array(
				'sendScheduleds' => array(
					'description' => 'Cette commande permet d\'envoyer les SMS programmés qui doivent l\'êtres.',
					'requireds' => array(),
					'optionals' => array(),
				),
				'parseReceivedSMS' => array(
					'description' => 'Cette commande permet d\'enregistrer un SMS, et de l\'analyser pour voir s\'il contient une commande. Pour cela, il analyse le dossier PWD_RECEIVEDS',
					'requireds' => array(
					),
					'optionals' => array(),
				),
			);

			$message  = "Vous êtes ici dans l'aide de la console.\n";
			$message .= "Voici la liste des commandes disponibles : \n";

			//On écrit les texte pour la liste des commandes dispos
			foreach ($commands as $name => $value)
			{
				$requireds = isset($value['requireds']) ? $value['requireds'] : array();
				$optionals = isset($value['optionals']) ? $value['optionals'] : array();

				$message .= '	' . $name . ' : ' . $value['description'] . "\n";
				$message .= "		Arguments obligatoires : \n";
				if (!count($requireds))
				{
					$message .= "			Pas d'argument\n";
				}
				else
				{
					foreach ($requireds as $argument => $desc)
					{
						$message .= '				- ' . $argument . ' : ' . $desc . "\n";
					}
				}

				$message .= "		Arguments optionnels : \n";
				
				if (!count($optionals))
				{
					$message .= "			Pas d'argument\n";
				}
				else
				{
					foreach ($optionals as $argument => $desc)
					{
						$message .= '				- ' . $argument . ' : ' . $desc . "\n";
					}
				}
			}

			echo $message;
		}

		/**
		 * Cette fonction envoie tous les SMS programmés qui doivent l'être
		 */
		public function sendScheduleds()
		{
			//On créé l'objet de base de données
			global $db;
			

			$now = new DateTime();
			$now = $now->format('Y-m-d H:i:s');

			echo "Début de l'envoi des SMS programmés\n";

			$scheduleds = $db->getScheduledsNotInProgressBefore($now);

			$ids_scheduleds = array();

			//On passe en cours de progression tous les SMS
			foreach ($scheduleds as $scheduled)
			{
				$ids_scheduleds[] = $scheduled['id'];
			}

			echo count($ids_scheduleds) . " SMS à envoyer ont été trouvés et ajoutés à la liste des SMS en cours d'envoi.\n";

			$db->updateProgressScheduledsIn($ids_scheduleds, true);

			//Pour chaque SMS à envoyer
			foreach ($scheduleds as $scheduled)
			{
				$id_scheduled = $scheduled['id'];
				$text_sms = escapeshellarg($scheduled['content']);
 
				//On initialise les numéros auxquelles envoyer le SMS
				$numbers = array();

				//On récupère les numéros pour le SMS et on les ajoute
				$target_numbers = $db->getNumbersForScheduled($id_scheduled);
				foreach ($target_numbers as $target_number)
				{
					$numbers[] = $target_number['number'];
				}

				//On récupère les contacts, et on ajoute les numéros
				$contacts = $db->getContactsForScheduled($id_scheduled);
				foreach ($contacts as $contact)
				{
					$numbers[] = $contact['number'];
				}

				//On récupère les groupes
				$groups = $db->getGroupsForScheduled($id_scheduled);
				foreach ($groups as $group)
				{
					//On récupère les contacts du groupe et on les ajoute aux numéros
					$contacts = $db->getContactsForGroup($group['id']);
					foreach ($contacts as $contact)
					{
						$numbers[] = $contact['number'];
					}
				}
				
				foreach ($numbers as $number)
				{
					echo "	Envoi d'un SMS au " . $number . "\n";
					//On ajoute le SMS aux SMS envoyés
					//Pour plus de précision, on remet la date à jour en réinstanciant l'objet DateTime (et on reformatte la date, bien entendu)
					$now = new DateTime();
					$now = $now->format('Y-m-d H:i:s');
					//On peut maintenant ajouter le SMS
					$db->createSended($now, $number, $scheduled['content']);
					$id_sended = $db->lastId();
					
					//Commande qui envoie le SMS
					$commande_send_sms = 'gammu-smsd-inject TEXT ' . escapeshellarg($number) . ' -len ' . mb_strlen($text_sms) . ' -text ' . $text_sms;
					//Commande qui s'assure de passer le SMS dans ceux envoyés, et de lui donner le bon statut

					//On va liée les deux commandes pour envoyer le SMS puis le passer en echec
					$commande = '(' . $commande_send_sms . ') >/dev/null 2>/dev/null &';
					exec($commande); //On execute la commande d'envoie d'un SMS
				}
			}

			echo "Tous les SMS sont en cours d'envoi.\n";
			//Tous les SMS ont été envoyés.	
			$db->deleteScheduledsIn($ids_scheduleds);
		}

		/**
		 * Cette fonction reçoit un SMS, et l'enregistre, en essayant dde trouver une commande au passage.
		 */
		public function parseReceivedSMS()
		{
			//On créer l'objet de base de données
			global $db;
			
			foreach (scandir(PWD_RECEIVEDS) as $dir)
			{
				//Si le fichier est un fichier système, on passe à l'itération suivante
				if ($dir == '.' || $dir == '..')
				{
					continue;
				}				

				//On récupère la date du SMS à la seconde près grâce au nom du fichier (Cf. parseSMS.sh)
				//Il faut mettre la date au format Y-m-d H:i:s
				$date = substr($dir, 0, 4) . '-' . substr($dir, 4, 2) . '-' . substr($dir, 6, 2) . ' ' . substr($dir, 8, 2) . ':' . substr($dir, 10, 2) . ':' . substr($dir, 12, 2);

				//On récupère le fichier, et on récupère la chaine jusqu'au premier ':' pour le numéro de téléphone source, et la fin pour le message
				$content_file = file_get_contents(PWD_RECEIVEDS . $dir);

				//Si on peux pas ouvrir le fichier, on quitte en logant une erreur
				if ($content_file == false)
				{
					$this->wlog('Unable to read file "' . $dir);
					die(4);
				}

				//On supprime le fichier. Si on n'y arrive pas, alors on log
				if (!unlink(PWD_RECEIVEDS . $dir))
				{
					$this->wlog('Unable to delete file "' . $dir);
					die(8);
				}

				$content_file = explode(':', $content_file, 2);

				//Si on a pas passé de numéro ou de message, alors on lève une erreur
				if (!isset($content_file[0], $content_file[1]))
				{
					$this->wlog('Missing params in file "' . $dir);
					die(5);
				}

				$number = $content_file[0];
				$number = internalTools::parsePhone($number);
				$text = $content_file[1];


				if (!$number)
				{
					$this->wlog('Invalid phone number in file "' . $dir);
					die(6);
				}
			
				//On va vérifier si on a reçu une commande, et des identifiants
				$flags = internalTools::parseForFlag($text);

				//On créer le tableau qui permettra de stocker les commandes trouvées

				$found_commands = array();

				//Si on reçu des identifiants
				if (array_key_exists('LOGIN', $flags) && array_key_exists('PASSWORD', $flags))
				{
					//Si on a bien un utilisateur avec les identifiants reçus
					$user = $db->getUserFromEmail($flags['LOGIN']);
					$this->wlog('We found ' . count($user) . ' users');
					if ($user && $user['password'] == sha1($flags['PASSWORD']))
					{
						$this->wlog('Password is valid');
						//On va passer en revue toutes les commandes, pour voir si on en trouve dans ce message
						$commands = $db->getAll('commands');

						$this->wlog('We found ' . count($commands) . ' commands');
						foreach ($commands as $command)
						{
							$command_name = mb_strtoupper($command['name']);
							if (array_key_exists($command_name, $flags))
							{
								$this->wlog('We found command ' . $command_name);
								
								//Si la commande ne nécessite pas d'être admin, ou si on est admin
								if (!$command['admin'] || $user['admin'])
								{
									$this->wlog('And the count is ok');
									$found_commands[$command_name] = PWD_SCRIPTS . $command['script'] . escapeshellcmd($flags[$command_name]);
								}
							}
						}
					}
				}

				//On va supprimer le mot de passe du SMS pour pouvoir l'enregistrer sans danger
				$text = str_replace($flags['PASSWORD'], '*****', $text);

				//On map les données et on créer le SMS reçu
				$send_by = $number;
				$content = $text;
				$is_command = count($found_commands);
				if (!$db->createReceived($date, $send_by, $content, $is_command))
				{
					echo "Erreur lors de l'enregistrement du SMS\n";
					$this->wlog('Unable to process the SMS in file "' . $dir);
					die(7);
				}

				//Chaque commande sera executée.
				foreach ($found_commands as $command_name => $command)
				{
					echo 'Execution de la commande : ' . $command_name . ' :: ' . $command . "\n";
					exec($command);
				}
			}
		}
	}
