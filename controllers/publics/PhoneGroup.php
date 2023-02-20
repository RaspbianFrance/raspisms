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
     * Page of phone groups.
     */
    class PhoneGroup extends \descartes\Controller
    {
        private $internal_phone_group;
        private $internal_phone;
        private $internal_event;

        /**
         * Call before any other func to check user is connected
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_phone_group = new \controllers\internals\PhoneGroup($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Return all groups as an array for administration.
         */
        public function list()
        {
            $this->render('phone_group/list');
        }

        /**
         * Return groups as json.
         */
        public function list_json()
        {
            $entities = $this->internal_phone_group->list_for_user($_SESSION['user']['id']);
            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
        }

        /**
         * Delete a list of phone groups
         *
         * @param array int $_GET['ids'] : Ids of phone groups to delete
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_phone_group->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
        }

        /**
         * Return the creation page of a group
         */
        public function add()
        {
            $this->render('phone_group/add');
        }

        /**
         * Return the edition page for phone groups
         *
         * @param array $_GET['ids'] : Ids of phone groups to edit
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            $groups = $this->internal_phone_group->gets_in_for_user($_SESSION['user']['id'], $ids);

            foreach ($groups as $key => $group)
            {
                $groups[$key]['phones'] = $this->internal_phone_group->get_phones($group['id']);
            }

            $this->render('phone_group/edit', [
                'phone_groups' => $groups,
            ]);
        }

        /**
         * Create a new phone group
         *
         * @param $csrf : CSRF token
         * @param string $_POST['name']     : Name of phone group
         * @param array  $_POST['phones'] : Ids of phones to put in the group
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'add'));
            }

            $name = $_POST['name'] ?? false;
            $phones_ids = $_POST['phones'] ?? false;

            if (!$name || !$phones_ids)
            {
                \FlashMessage\FlashMessage::push('danger', 'Des champs sont manquants !');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'add'));
            }

            $id_group = $this->internal_phone_group->create($_SESSION['user']['id'], $name, $phones_ids);
            if (!$id_group)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce groupe.');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le groupe a bien été créé.');

            return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
        }

        /**
         * Update a list of phone groups
         *
         * @param $csrf : CSRF token
         * @param array $_POST['phone_groups'] : An array of phone groups with group id as keys
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
            }

            $groups = $_POST['phone_groups'] ?? [];

            $nb_groups_update = 0;
            foreach ($groups as $id => $group)
            {
                foreach ($group['phones_ids'] as $key => $value)
                {
                    $group['phones_ids'][$key] = (int) $value;
                }

                $nb_groups_update += (int) $this->internal_phone_group->update_for_user($_SESSION['user']['id'], $id, $group['name'], $group['phones_ids']);
            }

            if ($nb_groups_update !== \count($groups))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains groupes n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les groupes ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('PhoneGroup', 'list'));
        }

        /**
         * Return phones of a group as json array
         * @param int $id_group = PhoneGroup id
         * 
         * @return json
         */
        public function preview (int $id_group)
        {
            $return = [
                'success' => false,
                'result' => 'Une erreur inconnue est survenue.',
            ];

            $group = $this->internal_phone_group->get_for_user($_SESSION['user']['id'], $id_group);

            if (!$group)
            {
                $return['result'] = 'Ce groupe n\'existe pas.';
                echo json_encode($return);

                return false;
            }

            $phones = $this->internal_phone_group->get_phones($id_group);
            if (!$phones)
            {
                $return['result'] = 'Aucun téléphone dans le groupe.';
                echo json_encode($return);

                return false;
            }

            foreach ($phones as &$phone)
            {
                $phone['adapter_name'] = call_user_func([$phone['adapter'], 'meta_name']);
            }

            $return['success'] = true;
            $return['result'] = $phones;
            echo json_encode($return);

            return true;
        }

        /**
         * Cette fonction retourne la liste des groups sous forme JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_phone_group->list_for_user($_SESSION['user']['id']));
        }
    }
