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
            $entities = $this->internal_event->list_for_user($_SESSION['user']['id']);
            foreach ($entities as &$entity)
            {
                $entity['icon'] = \controllers\internals\Tool::event_type_to_icon($entity['type']);
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
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
