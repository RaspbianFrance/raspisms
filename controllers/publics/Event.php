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
     * Page des events.
     */
    class Event extends \descartes\Controller
    {
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

            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les events, sous forme d'un tableau permettant l'administration de ces events.
         *
         * @param mixed $page
         */
        public function list()
        {
            $this->render('event/list');
        }
        
        /**
         * Return events as json.
         */
        public function list_json()
        {
            $draw = (int) ($_GET['draw'] ?? false);

            $columns = [
                0 => 'type',
                1 => 'at',
                2 => 'text',
                3 => 'updated_at',
            ];

            $search = $_GET['search']['value'] ?? null;
            $order_column = $columns[$_GET['order'][0]['column']] ?? null;
            $order_desc = ($_GET['order'][0]['dir'] ?? 'asc') == 'desc' ? true : false;
            $offset = (int) ($_GET['start'] ?? 0);
            $limit = (int) ($_GET['length'] ?? 25);

            $entities = $this->internal_event->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc);
            $count_entities = $this->internal_event->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc, true);
            foreach ($entities as &$entity)
            {
                $entity['icon'] = \controllers\internals\Tool::event_type_to_icon($entity['type']);
            }

            $records_total = $this->internal_event->count_for_user($_SESSION['user']['id']);

            header('Content-Type: application/json');
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $records_total,
                'recordsFiltered' => $count_entities,
                'data' => $entities,
            ]);
        }

        /**
         * Cette fonction va supprimer une liste de events.
         *
         * @param array int $_GET['ids'] : Les id des eventes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Event', 'list'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez être administrateur pour supprimer un event !');

                return $this->redirect(\descartes\Router::url('Event', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_event->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Event', 'list'));
        }
    }
