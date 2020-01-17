<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    /**
     * Page d'index, qui gère l'affichage par défaut temporairement.
     */
    class Dashboard extends \descartes\Controller
    {
        private $internal_sended;
        private $internal_received;
        private $internal_contact;
        private $internal_group;
        private $internal_scheduled;
        private $internal_event;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_sended = new \controllers\internals\Sended($bdd);
            $this->internal_received = new \controllers\internals\Received($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_group = new \controllers\internals\Group($bdd);
            $this->internal_scheduled = new \controllers\internals\Scheduled($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction est un alias de show.
         *
         * @return void;
         */
        public function show()
        {
            $id_user = $_SESSION['user']['id'];

            //Recupération des nombres des 4 panneaux d'accueil
            $nb_contacts = $this->internal_contact->count_for_user($id_user);
            $nb_groups = $this->internal_group->count_for_user($id_user);
            $nb_scheduleds = $this->internal_scheduled->count_for_user($id_user);
            $nb_unreads = $this->internal_received->count_unread_for_user($id_user);
            $nb_sendeds = $this->internal_sended->count_for_user($id_user);
            $nb_receiveds = $this->internal_received->count_for_user($id_user);

            //Création de la date d'il y a une semaine
            $now = new \DateTime();
            $one_week = new \DateInterval('P7D');
            $date = $now->sub($one_week);
            $formated_date = $date->format('Y-m-d');

            //Récupération des 10 derniers Sms envoyés, Sms reçus et evenements enregistrés. Par date.
            $sendeds = $this->internal_sended->get_lasts_by_date_for_user($id_user, 10);
            $receiveds = $this->internal_received->get_lasts_by_date_for_user($id_user, 10);
            $events = $this->internal_event->get_lasts_by_date_for_user($id_user, 10);

            //Récupération du nombre de Sms envoyés et reçus depuis les 7 derniers jours
            $nb_sendeds_by_day = $this->internal_sended->count_by_day_since_for_user($id_user, $formated_date);
            $nb_receiveds_by_day = $this->internal_received->count_by_day_since_for_user($id_user, $formated_date);

            //On va traduire ces données pour les afficher en graphique
            $array_area_chart = [];

            $today_less_7_day = new \DateTime();
            $today_less_7_day->sub(new \DateInterval('P7D'));
            $increment_day = new \DateInterval('P1D');
            $i = 0;

            //On va construire un tableau avec la date en clef, et les données pour chaque date
            while ($i < 7)
            {
                $today_less_7_day->add($increment_day);
                ++$i;
                $date_f = $today_less_7_day->format('Y-m-d');
                $array_area_chart[$date_f] = [
                    'period' => $date_f,
                    'sendeds' => 0,
                    'receiveds' => 0,
                ];
            }

            $total_sendeds = 0;
            $total_receiveds = 0;

            //0n remplie le tableau avec les données adaptées
            foreach ($nb_sendeds_by_day as $date => $nb_sended)
            {
                $array_area_chart[$date]['sendeds'] = $nb_sended;
                $total_sendeds += $nb_sended;
            }

            foreach ($nb_receiveds_by_day as $date => $nb_received)
            {
                $array_area_chart[$date]['receiveds'] = $nb_received;
                $total_receiveds += $nb_received;
            }

            $avg_sendeds = round($total_sendeds / 7, 2);
            $avg_receiveds = round($total_receiveds / 7, 2);

            $array_area_chart = array_values($array_area_chart);

            $this->render('dashboard/show', [
                'nb_contacts' => $nb_contacts,
                'nb_groups' => $nb_groups,
                'nb_scheduleds' => $nb_scheduleds,
                'nb_sendeds' => $nb_sendeds,
                'nb_receiveds' => $nb_receiveds,
                'nb_unreads' => $nb_unreads,
                'avg_sendeds' => $avg_sendeds,
                'avg_receiveds' => $avg_receiveds,
                'sendeds' => $sendeds,
                'receiveds' => $receiveds,
                'events' => $events,
                'datas_area_chart' => json_encode($array_area_chart),
            ]);
        }
    }
