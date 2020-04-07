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
     * Page des smsstops.
     */
    class SmsStop extends \descartes\Controller
    {
        private $internal_sms_stop;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_sms_stop = new \controllers\internals\SmsStop($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les smsstops, sous forme d'un tableau permettant l'administration de ces smsstops.
         *
         * @param mixed $page
         */
        public function list()
        {
            $smsstops = $this->internal_sms_stop->list_for_user($_SESSION['user']['id']);
            $this->render('smsstop/list', ['smsstops' => $smsstops, 'nb_results' => \count($smsstops)]);
        }

        /**
         * Cette fonction va supprimer une liste de smsstops.
         *
         * @param array int $_GET['ids'] : Les id des smsstopes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('SmsStop', 'list'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez être administrateur pour pouvoir supprimer un "STOP Sms" !');

                return $this->redirect(\descartes\Router::url('SmsStop', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_sms_stop->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('SmsStop', 'list'));
        }
    }
