<?php
namespace controllers\publics;

    /**
     * Page d'index, qui gère l'affichage par défaut temporairement
     */
    class Dashboard extends \descartes\Controller
    {
        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté
         * @return void;
         */
        public function _before()
        {
            global $bdd;
            $this->bdd = $bdd;

            $this->internalSended = new \controllers\internals\Sended($this->bdd);
            $this->internalReceived = new \controllers\internals\Received($this->bdd);
            $this->internalContact = new \controllers\internals\Contact($this->bdd);
            $this->internalGroupe = new \controllers\internals\Groupe($this->bdd);
            $this->internalScheduled = new \controllers\internals\Scheduled($this->bdd);
            $this->internalCommand = new \controllers\internals\Command($this->bdd);
            $this->internalEvent = new \controllers\internals\Event($this->bdd);

            \controllers\internals\Tool::verify_connect();
        }

        /**
         * Cette fonction est un alias de show
         * @return void;
         */
        public function show()
        {
            //Creation de l'object de base de données
            global $db;
            
            //Recupération des nombres des 4 panneaux d'accueil
            $nb_contacts = $this->internalContact->count();
            $nb_groupes = $this->internalGroupe->count();
            $nb_scheduleds = $this->internalScheduled->count();
            $nb_commands = $this->internalCommand->count();
            $nb_sendeds = $this->internalSended->count();
            $nb_receiveds = $this->internalReceived->count();

            //Création de la date d'il y a une semaine
            $now = new \DateTime();
            $one_week = new \DateInterval('P7D');
            $date = $now->sub($one_week);
            $formated_date = $date->format('Y-m-d');

            //Récupération des 10 derniers SMS envoyés, SMS reçus et evenements enregistrés. Par date.
            $sendeds = $this->internalSended->get_lasts_by_date(10);
            $receiveds = $this->internalReceived->get_lasts_by_date(10);
            $events = $this->internalEvent->get_lasts_by_date(10);

            //Récupération du nombre de SMS envoyés et reçus depuis les 7 derniers jours
            $nb_sendeds_by_day = $this->internalSended->count_by_day_since($formated_date);
            $nb_receiveds_by_day = $this->internalReceived->count_by_day_since($formated_date);

            //On va traduire ces données pour les afficher en graphique
            $array_area_chart = array();
            
            $today_less_7_day = new \DateTime();
            $today_less_7_day->sub(new \DateInterval('P7D'));
            $increment_day = new \DateInterval('P1D');
            $i = 0;

            //On va construire un tableau avec la date en clef, et les données pour chaque date
            while ($i < 7) {
                $today_less_7_day->add($increment_day);
                $i ++;
                $date_f = $today_less_7_day->format('Y-m-d');
                $array_area_chart[$date_f] = array(
                    'period' => $date_f,
                    'sendeds' => 0,
                    'receiveds' => 0,
                );
            }

            $total_sendeds = 0;
            $total_receiveds = 0;

            //0n remplie le tableau avec les données adaptées
            foreach ($nb_sendeds_by_day as $date => $nb_sended) {
                $array_area_chart[$date]['sendeds'] = $nb_sended;
                $total_sendeds += $nb_sended;
            }

            foreach ($nb_receiveds_by_day as $date => $nb_received) {
                $array_area_chart[$date]['receiveds'] = $nb_received;
                $total_receiveds += $nb_received;
            }

            $avg_sendeds = round($total_sendeds / 7, 2);
            $avg_receiveds = round($total_receiveds / 7, 2);

            $array_area_chart = array_values($array_area_chart);


            $this->render('dashboard/show', array(
                'nb_contacts' => $nb_contacts,
                'nb_groupes' => $nb_groupes,
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
