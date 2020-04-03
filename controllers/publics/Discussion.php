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
     * Page des discussions.
     */
    class Discussion extends \descartes\Controller
    {
        private $internal_sended;
        private $internal_scheduled;
        private $internal_received;
        private $internal_contact;
        private $internal_phone;

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
            $this->internal_scheduled = new \controllers\internals\Scheduled($bdd);
            $this->internal_received = new \controllers\internals\Received($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne toutes les discussions, sous forme d'un tableau permettant l'administration de ces contacts.
         */
        public function list()
        {
            $discussions = $this->internal_received->get_discussions_for_user($_SESSION['user']['id']);

            foreach ($discussions as $key => $discussion)
            {
                if (!$contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $discussion['number']))
                {
                    continue;
                }

                $discussions[$key]['contact'] = $contact['name'];
            }

            $this->render('discussion/list', [
                'discussions' => $discussions,
            ]);
        }

        /**
         * Cette fonction permet d'afficher la discussion avec un numero.
         *
         * @param string $number : La numéro de téléphone avec lequel on discute
         */
        public function show($number)
        {
            $contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $number);

            $last_sended = $this->internal_sended->get_last_for_destination_and_user($_SESSION['user']['id'], $number);
            $last_received = $this->internal_received->get_last_for_origin_and_user($_SESSION['user']['id'], $number);

            $response_phone_id = ($last_received['id_phone'] ?? $last_sended['id_phone'] ?? false);
            if ($response_phone_id)
            {
                $response_phone = $this->internal_phone->get_for_user($_SESSION['user']['id'], $response_phone_id);
            }

            $this->render('discussion/show', [
                'number' => $number,
                'contact' => $contact,
                'response_phone' => $response_phone ?? false,
            ]);
        }

        /**
         * Cette fonction récupère l'ensemble des messages pour un numéro, recçus, envoyés, en cours.
         *
         * @param string $number         : Le numéro cible
         * @param string $transaction_id : Le numéro unique de la transaction ajax (sert à vérifier si la requete doit être prise en compte)
         */
        public function get_messages($number, $transaction_id)
        {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            $id_user = $_SESSION['user']['id'];

            $sendeds = $this->internal_sended->gets_by_destination_and_user($id_user, $number);
            $receiveds = $this->internal_received->gets_by_origin_and_user($id_user, $number);
            $scheduleds = $this->internal_scheduled->gets_before_date_for_number_and_user($id_user, $now, $number);

            $messages = [];

            foreach ($sendeds as $sended)
            {
                $messages[] = [
                    'date' => htmlspecialchars($sended['at']),
                    'text' => htmlspecialchars($sended['text']),
                    'type' => 'sended',
                    'status' => $sended['status'],
                ];
            }

            foreach ($receiveds as $received)
            {
                if ('read' !== $received['status'])
                {
                    $this->internal_received->mark_as_read_for_user($id_user, $received['id']);
                }

                $messages[] = [
                    'date' => htmlspecialchars($received['at']),
                    'text' => htmlspecialchars($received['text']),
                    'type' => 'received',
                    'md5' => md5($received['at'] . $received['text']),
                ];
            }

            foreach ($scheduleds as $scheduled)
            {
                $messages[] = [
                    'date' => htmlspecialchars($scheduled['at']),
                    'text' => htmlspecialchars($scheduled['text']),
                    'type' => 'inprogress',
                ];
            }

            //On va trier le tableau des messages
            usort($messages, function ($a, $b)
            {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            //On récupère uniquement les 25 derniers messages sur l'ensemble
            $messages = \array_slice($messages, -25);

            echo json_encode(['transaction_id' => $transaction_id, 'messages' => $messages]);

            return true;
        }

        /**
         * Cette fonction permet d'envoyer facilement un sms à un numéro donné.
         *
         * @param string $csrf                 : Le jeton csrf
         * @param string $_POST['text']        : Le contenu du Sms
         * @param string $_POST['destination'] : Number to send sms to
         * @param string $_POST['id_phone']    : If of phone to send sms with
         *
         * @return string : json string Le statut de l'envoi
         */
        public function send($csrf)
        {
            $return = ['success' => true, 'message' => ''];

            //On vérifie que le jeton csrf est bon
            if (!$this->verify_csrf($csrf))
            {
                $return['success'] = false;
                $return['message'] = 'Jeton CSRF invalide';
                echo json_encode($return);

                return false;
            }

            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            $id_user = $_SESSION['user']['id'];
            $at = $now;
            $text = $_POST['text'] ?? '';
            $destination = $_POST['destination'] ?? false;
            $id_phone = $_POST['id_phone'] ?? false;

            if (!$destination)
            {
                $return['success'] = false;
                $return['message'] = 'Vous devez renseigner un numéro valide';
                echo json_encode($return);

                return false;
            }

            if (!$id_phone)
            {
                $id_phone = null;
            }

            //Destinations must be an array of number
            $destinations = [$destination];

            if (!$this->internal_scheduled->create($id_user, $at, $text, $id_phone, false, $destinations))
            {
                $return['success'] = false;
                $return['message'] = 'Impossible de créer le Sms';
                echo json_encode($return);

                return false;
            }

            echo json_encode($return);

            return true;
        }

        /**
         * Cette fonction retourne les id des sms qui sont envoyés.
         *
         * @return string : json string Tableau des ids des sms qui sont envoyés
         */
        public function checksendeds()
        {
            $_SESSION['discussion_wait_progress'] = isset($_SESSION['discussion_wait_progress']) ? $_SESSION['discussion_wait_progress'] : [];

            $scheduleds = $this->internal_scheduled->gets_in_for_user($_SESSION['user']['id'], $_SESSION['discussion_wait_progress']);

            //On va chercher à chaque fois si on a trouvé le sms. Si ce n'est pas le cas c'est qu'il a été envoyé
            $sendeds = [];
            foreach ($_SESSION['discussion_wait_progress'] as $key => $id_scheduled)
            {
                $found = false;
                foreach ($scheduleds as $scheduled)
                {
                    if ($id_scheduled === $scheduled['id'])
                    {
                        $found = true;
                    }
                }

                if (!$found)
                {
                    unset($_SESSION['discussion_wait_progress'][$key]);
                    $sendeds[] = $id_scheduled;
                }
            }

            echo json_encode($sendeds);

            return true;
        }

        /**
         * Cette fonction retourne les messages reçus pour un numéro après la date $_SESSION['discussion_last_checkreceiveds'].
         *
         * @param string $number : Le numéro de téléphone pour lequel on veux les messages
         *
         * @return string : json string Un tableau avec les messages
         */
        public function checkreceiveds($number)
        {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i');

            $_SESSION['discussion_last_checkreceiveds'] = isset($_SESSION['discussion_last_checkreceiveds']) ? $_SESSION['discussion_last_checkreceiveds'] : $now;

            $receiveds = $this->internal_received->get_since_for_number_by_date($_SESSION['discussion_last_checkreceiveds'], $number);

            //On va gérer le cas des messages en double en stockant ceux déjà reçus et en eliminant les autres
            $_SESSION['discussion_already_receiveds'] = isset($_SESSION['discussion_already_receiveds']) ? $_SESSION['discussion_already_receiveds'] : [];

            foreach ($receiveds as $key => $received)
            {
                //Sms jamais recu
                if (false === array_search($received['id'], $_SESSION['discussion_already_receiveds'], true))
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
