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
     * Page des receiveds.
     */
    class Received extends \descartes\Controller
    {
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
            $this->internal_received = new \controllers\internals\Received($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les receiveds, sous forme d'un tableau permettant l'administration de ces receiveds.
         */
        public function list()
        {
            $receiveds = $this->internal_received->list_for_user($_SESSION['user']['id']);

            foreach ($receiveds as $key => $received)
            {
                if ('read' !== $received['status'])
                {
                    $this->internal_received->mark_as_read_for_user($_SESSION['user']['id'], $received['id']);
                }

                if (null !== $received['id_phone'])
                {
                    $phone = $this->internal_phone->get_for_user($_SESSION['user']['id'], $received['id_phone']);
                    if ($phone)
                    {
                        $receiveds[$key]['phone_name'] = $phone['name'];
                    }
                }

                $contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $received['origin']);
                if ($contact)
                {
                    $receiveds[$key]['contact'] = $contact['name'];
                }
            }

            $this->render('received/list', ['receiveds' => $receiveds, 'nb_results' => \count($receiveds)]);
        }

        /**
         * Return all unread receiveds messages.
         */
        public function list_unread()
        {
            $receiveds = $this->internal_received->list_unread_for_user($_SESSION['user']['id']);

            foreach ($receiveds as $key => $received)
            {
                $this->internal_received->mark_as_read_for_user($_SESSION['user']['id'], $received['id']);

                if (null !== $received['id_phone'])
                {
                    $phone = $this->internal_phone->get_for_user($_SESSION['user']['id'], $received['id_phone']);
                    if ($phone)
                    {
                        $receiveds[$key]['phone_name'] = $phone['name'];
                    }
                }

                $contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $received['origin']);
                if ($contact)
                {
                    $receiveds[$key]['contact'] = $contact['name'];
                }
            }

            $this->render('received/list_unread', ['receiveds' => $receiveds, 'nb_results' => \count($receiveds)]);
        }

        /**
         * Delete Receiveds.
         *
         * @param array int $_GET['ids'] : Ids of receiveds to delete
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Received', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_received->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Received', 'list'));
        }

        /**
         * Cette fonction retourne tous les Sms reçus aujourd'hui pour la popup.
         *
         * @return string : A JSON Un tableau des Sms reçus
         */
        public function popup()
        {
            $now = new \DateTime();
            $receiveds = $this->internal_received->get_since_by_date_for_user($_SESSION['user']['id'], $now->format('Y-m-d'));

            foreach ($receiveds as $key => $received)
            {
                if (!$contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $received['origin']))
                {
                    continue;
                }

                $receiveds[$key]['origin'] = $this->s($contact['name'], false, true, false) . ' (' . \controllers\internals\Tool::phone_link($received['origin']) . ')';
            }

            $nb_received = \count($receiveds);

            if (!isset($_SESSION['popup_nb_receiveds']) || $_SESSION['popup_nb_receiveds'] > $nb_received)
            {
                $_SESSION['popup_nb_receiveds'] = $nb_received;
            }

            $newly_receiveds = \array_slice($receiveds, $_SESSION['popup_nb_receiveds']);

            $_SESSION['popup_nb_receiveds'] = $nb_received;

            echo json_encode($newly_receiveds);

            return true;
        }
    }
