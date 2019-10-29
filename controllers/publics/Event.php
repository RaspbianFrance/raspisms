<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
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
        public function list($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $events = $this->internal_event->get_list($limit, $page);
            $this->render('event/list', ['events' => $events, 'limit' => $limit, 'page' => $page, 'nb_results' => \count($events)]);
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
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Event', 'list'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être admin pour pouvoir supprimer des events.');

                return $this->redirect(\descartes\Router::url('Event', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_event->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Event', 'list'));
        }
    }
