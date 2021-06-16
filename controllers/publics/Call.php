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
     * Page des calls.
     */
    class Call extends \descartes\Controller
    {
        private $internal_call;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_call = new \controllers\internals\Call($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Page for showing calls list.
         */
        public function list()
        {
            $this->render('call/list');
        }

        /**
         * Return calls list as json.
         */
        public function list_json()
        {
            $entities = $this->internal_call->list_for_user($_SESSION['user']['id']);
            foreach ($entities as &$entity)
            {
                switch ($entity['direction'])
                {
                    case \models\Call::DIRECTION_INBOUND:
                        $entity['origin_formatted'] = \controllers\internals\Tool::phone_link($entity['origin']);

                        break;

                    case \models\Call::DIRECTION_OUTBOUND:
                        $entity['destination_formatted'] = \controllers\internals\Tool::phone_link($entity['destination']);

                        break;
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
        }

        /**
         * Delete a list of calls.
         *
         * @param array int $_GET['ids'] : Ids of calls to delete
         * @param string    $csrf        : csrf token
         *
         * @return boolean;
         */
        public function delete(string $csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Call', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_call->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Call', 'list'));
        }
    }
