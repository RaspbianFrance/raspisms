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
     * Page des webhooks.
     */
    class Webhook extends \descartes\Controller
    {
        private $internal_webhook;
        private $internal_event;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_webhook = new \controllers\internals\Webhook($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * List all webhooks.
         *
         * @param mixed $page
         */
        public function list()
        {
            $webhooks = $this->internal_webhook->list_for_user($_SESSION['user']['id']);
            $this->render('webhook/list', ['webhooks' => $webhooks]);
        }

        /**
         * Delete a list of webhooks.
         *
         * @param array int $_GET['ids'] : Les id des webhooks à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');
                $this->redirect(\descartes\Router::url('Webhook', 'list'));

                return false;
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_webhook->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Webhook', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'une webhook.
         */
        public function add()
        {
            $this->render('webhook/add');
        }

        /**
         * Edit a list of webhooks.
         *
         * @param array int $_GET['ids'] : ids of webhooks to edit
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            $webhooks = $this->internal_webhook->gets_in_for_user($_SESSION['user']['id'], $ids);

            $this->render('webhook/edit', [
                'webhooks' => $webhooks,
            ]);
        }

        /**
         * Insert a new webhook.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['url']  : URL to call on webhook release
         * @param string $_POST['type'] : Type of webhook, either 'send_sms' or 'receive_sms'
         *
         * @return boolean;
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Webhook', 'list'));
            }

            $url = $_POST['url'] ?? false;
            $type = $_POST['type'] ?? false;

            if (!$url || !$type)
            {
                \FlashMessage\FlashMessage::push('danger', 'Renseignez au moins une URL et un type de webhook.');

                return $this->redirect(\descartes\Router::url('Webhook', 'list'));
            }

            if (!$this->internal_webhook->create($_SESSION['user']['id'], $url, $type))
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce webhook.');

                return $this->redirect(\descartes\Router::url('Webhook', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'La webhook a bien été créé.');

            return $this->redirect(\descartes\Router::url('Webhook', 'list'));
        }

        /**
         * Cette fonction met à jour une webhook.
         *
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['webhooks'] : Un tableau des webhooks avec leur nouvelle valeurs
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Webhook', 'list'));
            }

            $nb_update = 0;
            foreach ($_POST['webhooks'] as $webhook)
            {
                $url = $webhook['url'] ?? false;
                $type = $webhook['type'] ?? false;

                if (!$url || !$type)
                {
                    continue;
                }

                $success = $this->internal_webhook->update_for_user($_SESSION['user']['id'], $webhook['id'], $url, $type);
                $nb_update += (int) $success;
            }

            if ($nb_update !== \count($_POST['webhooks']))
            {
                \FlashMessage\FlashMessage::push('info', 'Certains webhooks n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('Webhook', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les webhooks ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('Webhook', 'list'));
        }
    }
