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

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les sendeds, sous forme d'un tableau permettant l'administration de ces sendeds.
         *
         * @param mixed $page
         */
        public function list()
        {
            $sendeds = $this->internal_sended->list_for_user($_SESSION['user']['id']);

            foreach ($sendeds as $key => $sended)
            {
                if ($sended['id_phone'] !== null)
                {
                    $phone = $this->internal_phone->get_for_user($_SESSION['user']['id'], $sended['id_phone']);
                    if ($phone)
                    {
                        $sendeds[$key]['phone_name'] = $phone['name'];
                    }
                }

                $contact = $this->internal_contact->get_by_number_and_user($_SESSION['user']['id'], $sended['destination']);
                if ($contact)
                {
                    $sendeds[$key]['contact'] = $contact['name'];
                }
            }

            $this->render('sended/list', ['sendeds' => $sendeds, 'nb_results' => \count($sendeds)]);
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
