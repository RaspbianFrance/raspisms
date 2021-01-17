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
     * Page des groups.
     */
    class Group extends \descartes\Controller
    {
        private $internal_group;
        private $internal_contact;
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

            $this->internal_group = new \controllers\internals\Group($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les groups, sous forme d'un tableau permettant l'administration de ces groups.
         */
        public function list()
        {
            $this->render('group/list');
        }

        /**
         * Return groups as json.
         */
        public function list_json()
        {
            $entities = $this->internal_group->list_for_user($_SESSION['user']['id']);
            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
        }

        /**
         * Cette fonction va supprimer une liste de groups.
         *
         * @param array int $_GET['group_ids'] : Les id des groups à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Group', 'list'));
            }

            $ids = $_GET['group_ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_group->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Group', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un group.
         */
        public function add()
        {
            $this->render('group/add');
        }

        /**
         * Cette fonction retourne la page d'édition des groups.
         *
         * @param int... $ids : Les id des groups à supprimer
         */
        public function edit()
        {
            $ids = $_GET['group_ids'] ?? [];

            $groups = $this->internal_group->gets_in_for_user($_SESSION['user']['id'], $ids);

            foreach ($groups as $key => $group)
            {
                $groups[$key]['contacts'] = $this->internal_group->get_contacts($group['id']);
            }

            $this->render('group/edit', [
                'groups' => $groups,
            ]);
        }

        /**
         * Cette fonction insert un nouveau group.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name']     : Le nom du group
         * @param array  $_POST['contacts'] : Les ids des contacts à mettre dans le group
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Group', 'add'));
            }

            $name = $_POST['name'] ?? false;
            $contacts_ids = $_POST['contacts'] ?? false;

            if (!$name || !$contacts_ids)
            {
                \FlashMessage\FlashMessage::push('danger', 'Des champs sont manquants !');

                return $this->redirect(\descartes\Router::url('Group', 'add'));
            }

            $id_group = $this->internal_group->create($_SESSION['user']['id'], $name, $contacts_ids);
            if (!$id_group)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce groupe.');

                return $this->redirect(\descartes\Router::url('Group', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le groupe a bien été créé.');

            return $this->redirect(\descartes\Router::url('Group', 'list'));
        }

        /**
         * Cette fonction met à jour une group.
         *
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['groups'] : Un tableau des groups avec leur nouvelle valeurs & une entrée 'contacts_id' avec les ids des contacts pour chaque group
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Group', 'list'));
            }

            $groups = $_POST['groups'] ?? [];

            $nb_groups_update = 0;
            foreach ($groups as $id => $group)
            {
                foreach ($group['contacts_ids'] as $key => $value)
                {
                    $group['contacts_ids'][$key] = (int) $value;
                }

                $nb_groups_update += (int) $this->internal_group->update_for_user($_SESSION['user']['id'], $id, $group['name'], $group['contacts_ids']);
            }

            if ($nb_groups_update !== \count($groups))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains groupes n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('Group', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les groupes ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('Group', 'list'));
        }

        /**
         * Cette fonction retourne la liste des groups sous forme JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_group->list_for_user($_SESSION['user']['id']));
        }
    }
