<?php
	/**
	 * Page d'index, qui gère l'affichage par défaut temporairement
	 */
	class dashboard extends Controller
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
		 * Cette fonction est un alias de show
		 * @return void;
		 */	
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;
			
			//Recupération des nombres des 4 panneaux d'accueil
			$nb_contacts = $db->countTable('contacts');;
			$nb_groups = $db->countTable('groups');
			$nb_scheduleds = $db->countTable('scheduleds');
			$nb_commands = $db->countTable('commands');

			//Création de la date d'il y a une semaine
			$now = new DateTime();
			$one_week = new DateInterval('P7D');
			$date = $now->sub($one_week);
			$formated_date = $date->format('Y-m-d');

			//Récupération des 10 derniers SMS envoyés, SMS reçus et evenements enregistrés. Par date.
			$sendeds = $db->getFromTableWhere('sendeds', [], 'at', true, 10);
			$receiveds = $db->getFromTableWhere('receiveds', [], 'at', true, 10);
			$events = $db->getFromTableWhere('events', [], 'at', true, 10);	

			//Récupération du nombre de SMS envoyés et reçus depuis les 7 derniers jours
			$nb_sendeds = $db->getNbSendedsSinceGroupDay($formated_date);
			$nb_receiveds = $db->getNbReceivedsSinceGroupDay($formated_date);

			//On va traduire ces données pour les afficher en graphique
			$array_area_chart = array();
			
			$now = new DateTime();
			$now->sub(new DateInterval('P7D'));
			$increment_day = new DateInterval('P1D');
			$i = 0;

			//On va construire un tableau avec la date en clef, et les données pour chaque date
			while ($i < 7)
			{
				$now->add($increment_day);
				$i ++;
				$date_f = $now->format('Y-m-d');
				$array_area_chart[$date_f] = array(
					'period' => $date_f,
					'sendeds' => 0,
					'receiveds' => 0,
				);	
			}

			$total_sendeds = 0;
			$total_receiveds = 0;

			//0n remplie le tableau avec les données adaptées
			foreach ($nb_sendeds as $nb_sended)
			{
				if (array_key_exists($nb_sended['at_ymd'], $array_area_chart))
				{
					$array_area_chart[$nb_sended['at_ymd']]['sendeds'] = $nb_sended['nb'];
					$total_sendeds += $nb_sended['nb'];
				}
			}
			foreach ($nb_receiveds as $nb_received)
			{
				if (array_key_exists($nb_received['at_ymd'], $array_area_chart))
				{
					$array_area_chart[$nb_received['at_ymd']]['receiveds'] = $nb_received['nb'];
					$total_receiveds += $nb_received['nb'];
				}
			}

			$avg_sendeds = round($total_sendeds / 7, 2);
			$avg_receiveds = round($total_receiveds / 7, 2);

			$array_area_chart = array_values($array_area_chart);


			$this->render('dashboard/default', array(
				'nb_contacts' => $nb_contacts,
				'nb_groups' => $nb_groups,
				'nb_scheduleds' => $nb_scheduleds,
				'nb_commands' => $nb_commands,
				'nb_sendeds' => $nb_sendeds,
				'nb_receiveds' => $nb_receiveds,
				'avg_sendeds' => $avg_sendeds,
				'avg_receiveds' => $avg_receiveds,
				'sendeds' => $sendeds,
				'receiveds' => $receiveds,
				'events' => $events,
				'datas_area_chart' => json_encode($array_area_chart),
			));
			
		}
	}
