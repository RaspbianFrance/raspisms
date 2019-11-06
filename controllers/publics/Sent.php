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
     * Page des sendeds.
     */
    class Sent extends \descartes\Controller
    {
        private $internal_sended;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_sended = new \controllers\internals\Sent($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les sendeds, sous forme d'un tableau permettant l'administration de ces sendeds.
         *
         * @param mixed $page
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $sendeds = $this->internal_sended->list($limit, $page);
            $this->render('sended/list', ['sendeds' => $sendeds, 'page' => $page, 'limit' => $limit, 'nb_results' => \count($sendeds)]);
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
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Sent', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_sended->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Sent', 'list'));
        }
    }
