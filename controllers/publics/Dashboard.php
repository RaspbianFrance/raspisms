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
        private $internal_quota;

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
            $this->internal_quota = new \controllers\internals\Quota($bdd);

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

            //Récupération des 10 derniers Sms envoyés, Sms reçus et evenements enregistrés. Par date.
            $sendeds = $this->internal_sended->get_lasts_by_date_for_user($id_user, 10);
            $receiveds = $this->internal_received->get_lasts_by_date_for_user($id_user, 10);
            $events = $this->internal_event->get_lasts_by_date_for_user($id_user, 10);

            //Récupération du nombre de Sms envoyés et reçus depuis 1 mois jours ou depuis le début du quota si il existe

            //Création de la date d'il y a 30 jours
            $now = new \DateTime();
            $one_month = new \DateInterval('P1M');
            $stats_start_date = clone $now;
            $stats_start_date->sub($one_month);
            $stats_start_date_formated = $stats_start_date->format('Y-m-d');

            //If user have a quota and the quota start before today, use quota start date instead
            $quota_unused = false;
            $quota = $this->internal_quota->get_user_quota($id_user);
            if ($quota && (new \DateTime($quota['start_date']) <= $now) && (new \DateTime($quota['expiration_date']) > $now))
            {
                $quota_unused = $quota['credit'] + $quota['additional'] - $quota['consumed'];

                $stats_start_date = new \DateTime($quota['start_date']);
                $stats_start_date_formated = $stats_start_date->format('Y-m-d');
            }

            $this->render('dashboard/show', [
                'nb_contacts' => $nb_contacts,
                'nb_groups' => $nb_groups,
                'nb_scheduleds' => $nb_scheduleds,
                'nb_sendeds' => $nb_sendeds,
                'nb_receiveds' => $nb_receiveds,
                'nb_unreads' => $nb_unreads,
                'quota_unused' => $quota_unused,
                'sendeds' => $sendeds,
                'receiveds' => $receiveds,
                'events' => $events,
                'stats_start_date_formated' => $stats_start_date_formated,
            ]);
        }

        /**
         * Return stats about sended sms
         */
        public function stats_sended()
        {
            $id_user = $_SESSION['user']['id'];

            //Création de la date d'il y a 30 jours
            $now = new \DateTime();
            $one_month = new \DateInterval('P1M');
            $stats_start_date = clone $now;
            $stats_start_date->sub($one_month);
            $stats_start_date_formated = $stats_start_date->format('Y-m-d');

            //If user have a quota and the quota start before today, use quota start date instead
            $quota = $this->internal_quota->get_user_quota($id_user);
            if ($quota && (new \DateTime($quota['start_date']) <= $now) && (new \DateTime($quota['expiration_date']) > $now))
            {
                $stats_start_date = new \DateTime($quota['start_date']);
                $stats_start_date_formated = $stats_start_date->format('Y-m-d');
            }

            $nb_sendeds_by_day = $this->internal_sended->count_by_day_and_status_since_for_user($id_user, $stats_start_date_formated);

            //On va traduire ces données pour les afficher en graphique
            $array_bar_chart_sended = [];

            $date = clone $stats_start_date;
            $one_day = new \DateInterval('P1D');

            //On va construire un tableau avec la date en clef, et les données pour chaque date
            while ($date <= $now)
            {
                $date_f = $date->format('Y-m-d');
                $array_bar_chart_sended[$date_f] = [
                    'period' => $date_f,
                    'sendeds_failed' => 0,
                    'sendeds_unknown' => 0,
                    'sendeds_delivered' => 0,
                ];

                $date->add($one_day);
            }

            $total_sendeds = 0;
            $total_receiveds = 0;

            //On remplie le tableau avec les données adaptées
            foreach ($nb_sendeds_by_day as $nb_sended)
            {
                $array_bar_chart_sended[$nb_sended['at_ymd']]['sendeds_' . $nb_sended['status']] = $nb_sended['nb'];
                $array_bar_chart_sended[$nb_sended['at_ymd']]['sendeds_total'] = ($array_bar_chart_sended[$nb_sended['at_ymd']]['sendeds_total'] ?? 0) + $nb_sended['nb'];
                $total_sendeds += $nb_sended['nb'];
            }

            $nb_days = $stats_start_date->diff($now)->days + 1;
            $avg_sendeds = round($total_sendeds / $nb_days, 2);

            $array_bar_chart_sended = array_values($array_bar_chart_sended);
            
            header('content-type:application/json');
            echo json_encode([
                'data_bar_chart_sended' => $array_bar_chart_sended,
                'avg_sendeds' => $avg_sendeds,
            ]);
        }


        /**
         * Return stats about received sms
         */
        public function stats_received()
        {
            $id_user = $_SESSION['user']['id'];

            //Création de la date d'il y a 30 jours
            $now = new \DateTime();
            $one_month = new \DateInterval('P1M');
            $stats_start_date = clone $now;
            $stats_start_date->sub($one_month);
            $stats_start_date_formated = $stats_start_date->format('Y-m-d');

            $quota = $this->internal_quota->get_user_quota($id_user);
            if ($quota && (new \DateTime($quota['start_date']) <= $now) && (new \DateTime($quota['expiration_date']) > $now))
            {
                $stats_start_date = new \DateTime($quota['start_date']);
                $stats_start_date_formated = $stats_start_date->format('Y-m-d');
            }

            $nb_receiveds_by_day = $this->internal_received->count_by_day_since_for_user($id_user, $stats_start_date_formated);

            //On va traduire ces données pour les afficher en graphique
            $array_bar_chart_received = [];

            $date = clone $stats_start_date;
            $one_day = new \DateInterval('P1D');

            //On va construire un tableau avec la date en clef, et les données pour chaque date
            while ($date <= $now)
            {
                $date_f = $date->format('Y-m-d');
                $array_bar_chart_received[$date_f] = ['period' => $date_f, 'receiveds' => 0];

                $date->add($one_day);
            }

            $total_receiveds = 0;

            foreach ($nb_receiveds_by_day as $date => $nb_received)
            {
                $array_bar_chart_received[$date]['receiveds'] = $nb_received;
                $total_receiveds += $nb_received;
            }

            $nb_days = $stats_start_date->diff($now)->days + 1;
            $avg_receiveds = round($total_receiveds / $nb_days, 2);

            $array_bar_chart_received = array_values($array_bar_chart_received);

            header('content-type:application/json');
            echo json_encode([
                'data_bar_chart_received' => $array_bar_chart_received,
                'avg_receiveds' => $avg_receiveds,
            ]);
        }

    }
