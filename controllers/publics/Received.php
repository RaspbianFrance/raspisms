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
        private $internal_media;

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
            $this->internal_media = new \controllers\internals\Media($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les receiveds, sous forme d'un tableau permettant l'administration de ces receiveds.
         */
        public function list()
        {
            $this->render('received/list', ['is_unread' => false]);
        }

        /**
         * Return receiveds as json.
         *
         * @param bool $unread : Should we only search for unread messages
         */
        public function list_json(bool $unread = false)
        {
            $draw = (int) ($_GET['draw'] ?? false);

            $columns = [
                0 => 'searchable_origin',
                1 => 'phone_name',
                2 => 'text',
                3 => 'at',
                4 => 'status',
                5 => 'command',
            ];

            $search = $_GET['search']['value'] ?? null;
            $order_column = $columns[$_GET['order'][0]['column']] ?? null;
            $order_desc = ($_GET['order'][0]['dir'] ?? 'asc') == 'desc' ? true : false;
            $offset = (int) ($_GET['start'] ?? 0);
            $limit = (int) ($_GET['length'] ?? 25);

            $entities = $this->internal_received->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc, false, $unread);
            $count_entities = $this->internal_received->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc, true, $unread);
            foreach ($entities as &$entity)
            {
                $entity['origin_formatted'] = \controllers\internals\Tool::phone_link($entity['origin']);
                if ($entity['mms'])
                {
                    $entity['medias'] = $this->internal_media->gets_for_received($entity['id']);
                }
            }

            $records_total = $this->internal_received->count_for_user($_SESSION['user']['id']);

            header('Content-Type: application/json');
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $records_total,
                'recordsFiltered' => $count_entities,
                'data' => $entities,
            ]);
        }

        /**
         * Return all unread receiveds messages.
         */
        public function list_unread()
        {
            $this->render('received/list', ['is_unread' => true]);
        }

        /**
         * Mark messages as.
         *
         * @param string    $status      : New status of the message, read or unread
         * @param array int $_GET['ids'] : Ids of receiveds to delete
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function mark_as($status, $csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Received', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                if (\models\Received::STATUS_UNREAD === $status)
                {
                    $this->internal_received->mark_as_unread_for_user($_SESSION['user']['id'], $id);
                }
                elseif (\models\Received::STATUS_READ === $status)
                {
                    $this->internal_received->mark_as_read_for_user($_SESSION['user']['id'], $id);
                }
            }

            return $this->redirect(\descartes\Router::url('Received', 'list'));
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
