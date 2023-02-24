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
     * Page des sendeds.
     */
    class Sended extends \descartes\Controller
    {
        private $internal_sended;
        private $internal_phone;
        private $internal_contact;
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
            $this->internal_sended = new \controllers\internals\Sended($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_media = new \controllers\internals\Media($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les sendeds, sous forme d'un tableau permettant l'administration de ces sendeds.
         *
         * @param mixed $page
         */
        public function list()
        {
            $this->render('sended/list');
        }

        /**
         * Return sendeds as json.
         */
        public function list_json()
        {
            $draw = (int) ($_GET['draw'] ?? false);

            $columns = [
                0 => 'phone_name',
                1 => 'searchable_destination',
                2 => 'text',
                3 => 'tag',
                4 => 'at',
                5 => 'status',
            ];

            $search = $_GET['search']['value'] ?? null;
            $order_column = $columns[$_GET['order'][0]['column']] ?? null;
            $order_desc = ($_GET['order'][0]['dir'] ?? 'asc') == 'desc' ? true : false;
            $offset = (int) ($_GET['start'] ?? 0);
            $limit = (int) ($_GET['length'] ?? 25);

            $entities = $this->internal_sended->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc);
            $count_entities = $this->internal_sended->datatable_list_for_user($_SESSION['user']['id'], $limit, $offset, $search, $columns, $order_column, $order_desc, true);
            foreach ($entities as &$entity)
            {
                $entity['destination_formatted'] = \controllers\internals\Tool::phone_link($entity['destination']);
                if ($entity['mms'])
                {
                    $entity['medias'] = $this->internal_media->gets_for_sended($entity['id']);
                }
            }

            $records_total = $this->internal_sended->count_for_user($_SESSION['user']['id']);

            header('Content-Type: application/json');
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $records_total,
                'recordsFiltered' => $count_entities,
                'data' => $entities,
            ]);
        }

        /**
         * Cette fonction va supprimer une liste de sendeds.
         *
         * @param array int $_GET['ids'] : Les id des sendedes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Sended', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_sended->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Sended', 'list'));
        }
    }
