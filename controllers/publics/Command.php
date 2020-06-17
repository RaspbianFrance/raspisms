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
     * Page des commandes.
     */
    class Command extends \descartes\Controller
    {
        private $internal_command;
        private $internal_event;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_command = new \controllers\internals\Command($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();

            if (!ENABLE_COMMAND)
            {
                \FlashMessage\FlashMessage::push('danger', 'Les commandes sont désactivées.');
                $this->redirect(\descartes\Router::url('Dashboard', 'show'));

                exit(0);
            }
        }

        /**
         * Cette fonction retourne tous les users, sous forme d'un tableau permettant l'administration de ces users.
         */
        public function list()
        {
            $commands = $this->internal_command->list_for_user($_SESSION['user']['id']);
            $this->render('command/list', ['commands' => $commands]);
        }

        /**
         * Cette fonction va supprimer une liste de commands.
         *
         * @param array int $_GET['ids'] : Les id des commandes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');
                $this->redirect(\descartes\Router::url('Command', 'list'));

                return false;
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_command->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Command', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'une commande.
         */
        public function add()
        {
            $this->render('command/add');
        }

        /**
         * Cette fonction retourne la page d'édition des commandes.
         *
         * @param array int $_GET['ids'] : Les id des commandes à editer
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            $commands = $this->internal_command->gets_in_for_user($_SESSION['user']['id'], $ids);

            $this->render('command/edit', [
                'commands' => $commands,
            ]);
        }

        /**
         * Cette fonction insert une nouvelle commande.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name']   : Le nom de la commande
         * @param string $_POST['script'] : Le script a appeler
         * @param bool   $_POST['admin']  : Si la commande necessite les droits d'admin (par défaut non)
         *
         * @return boolean;
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Command', 'list'));
            }

            $name = $_POST['name'] ?? false;
            $script = $_POST['script'] ?? false;
            $admin = $_POST['admin'] ?? false;

            if (!$name || !$script)
            {
                \FlashMessage\FlashMessage::push('danger', 'Renseignez au moins un nom et un script.');

                return $this->redirect(\descartes\Router::url('Command', 'list'));
            }

            if (!$this->internal_command->create($_SESSION['user']['id'], $name, $script, $admin))
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer cette commande.');

                return $this->redirect(\descartes\Router::url('commands', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'La commande a bien été crée.');

            return $this->redirect(\descartes\Router::url('Command', 'list'));
        }

        /**
         * Cette fonction met à jour une commande.
         *
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['commands'] : Un tableau des commandes avec leur nouvelle valeurs
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Command', 'list'));
            }

            $nb_commands_update = 0;
            foreach ($_POST['commands'] as $command)
            {
                $update_command = $this->internal_command->update_for_user($_SESSION['user']['id'], $command['id'], $command['name'], $command['script'], $command['admin']);
                $nb_commands_update += (int) $update_command;
            }

            if ($nb_commands_update !== \count($_POST['commands']))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certaines commandes n\'ont pas pu êtres mises à jour.');

                return $this->redirect(\descartes\Router::url('Command', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Toutes les commandes ont été modifiées avec succès.');

            return $this->redirect(\descartes\Router::url('Command', 'list'));
        }
    }
