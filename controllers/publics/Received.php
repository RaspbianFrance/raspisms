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
     * Page des receiveds.
     */
    class Received extends \descartes\Controller
    {
        private $internal_received;
        private $internal_contact;

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

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les receiveds, sous forme d'un tableau permettant l'administration de ces receiveds.
         *
         * @param mixed $page
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $receiveds = $this->internal_received->list($limit, $page);

            foreach ($receiveds as $key => $received)
            {
                if (!$contact = $this->internal_contact->get_by_number($received['origin']))
                {
                    continue;
                }

                $receiveds[$key]['send_by'] = $contact['name'].' ('.$received['origin'].')';
            }

            $this->render('received/list', ['receiveds' => $receiveds, 'page' => $page, 'limit' => $limit, 'nb_results' => \count($receiveds)]);
        }

        /**
         * Cette fonction retourne tous les Sms reçus aujourd'hui pour la popup.
         *
         * @return string : A JSON Un tableau des Sms reçus
         */
        public function popup()
        {
            $now = new \DateTime();
            $receiveds = $this->internal_received->get_since_by_date($now->format('Y-m-d'));

            foreach ($receiveds as $key => $received)
            {
                if (!$contact = $this->internal_contact->get_by_number($received['origin']))
                {
                    continue;
                }

                $receiveds[$key]['origin'] = $contact['name'].' ('.$received['origin'].')';
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

        /**
         * Cette fonction va supprimer une liste de receiveds.
         *
         * @param array int $_GET['ids'] : Les id des receivedes à supprimer
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

            if (!\controllers\internals\Tool::is_admin())
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez être administrateur pour effectuer cette action.');

                return $this->redirect(\descartes\Router::url('Received', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_received->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Received', 'list'));
        }
    }
