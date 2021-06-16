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
    class ConditionalGroup extends \descartes\Controller
    {
        private $internal_conditional_group;
        private $internal_contact;
        private $internal_ruler;
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

            $this->internal_conditional_group = new \controllers\internals\ConditionalGroup($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
            $this->internal_ruler = new \controllers\internals\Ruler();

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Return all conditionnals groups for administration.
         *
         * @param mixed $page
         */
        public function list()
        {
            $this->render('conditional_group/list');
        }

        /**
         * Return conditionnals groups as json.
         */
        public function list_json()
        {
            $entities = $this->internal_conditional_group->list_for_user($_SESSION['user']['id']);
            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
        }

        /**
         * Cette fonction va supprimer une liste de groups.
         *
         * @param array int $_GET['conditional_group_ids'] : Les id des groups à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
            }

            $ids = $_GET['conditional_group_ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_conditional_group->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un group.
         */
        public function add()
        {
            $this->render('conditional_group/add');
        }

        /**
         * Cette fonction retourne la page d'édition des groups.
         *
         * @param int... $ids : Les id des groups à supprimer
         */
        public function edit()
        {
            $ids = $_GET['conditional_group_ids'] ?? [];

            $groups = $this->internal_conditional_group->gets_in_for_user($_SESSION['user']['id'], $ids);

            $this->render('conditional_group/edit', [
                'groups' => $groups,
            ]);
        }

        /**
         * Cette fonction insert un nouveau group.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name']      : Le nom du group
         * @param array  $_POST['condition'] : The condition to used
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'add'));
            }

            $name = $_POST['name'] ?? false;
            $condition = $_POST['condition'] ?? false;

            if (!$name || !$condition)
            {
                \FlashMessage\FlashMessage::push('danger', 'Des champs sont manquants !');

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'add'));
            }

            $id_group = $this->internal_conditional_group->create($_SESSION['user']['id'], $name, $condition);
            if (!$id_group)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce groupe.');

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le groupe a bien été créé.');

            return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
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

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
            }

            $groups = $_POST['groups'] ?? [];

            $nb_groups_update = 0;
            foreach ($groups as $id => $group)
            {
                $nb_groups_update += (int) $this->internal_conditional_group->update_for_user($_SESSION['user']['id'], $id, $group['name'], $group['condition']);
            }

            if ($nb_groups_update !== \count($groups))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains groupes n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les groupes ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('ConditionalGroup', 'list'));
        }

        /**
         * Try to get the preview of contacts for a conditionnal group.
         *
         * @param string $_POST['condition'] : Condition to apply
         *
         * @return mixed : False on error, json string ['success' => bool, 'result' => String with contacts]
         */
        public function contacts_preview()
        {
            $return = [
                'success' => false,
                'result' => 'Une erreur inconnue est survenue.',
            ];

            $condition = $_POST['condition'] ?? false;

            if (!$condition)
            {
                $return['result'] = 'Vous devez renseigner une condition.';
                echo json_encode($return);

                return false;
            }

            $internal_ruler = new \controllers\internals\Ruler();
            $valid_condition = $internal_ruler->validate_condition($condition, ['contact' => (object) ['data' => (object) null], 'contact_metas' => (object) null]);
            if (!$valid_condition)
            {
                $return['result'] = 'Syntaxe de la condition invalide.';
                echo json_encode($return);

                return false;
            }

            $contacts = $this->internal_conditional_group->get_contacts_for_condition_and_user($_SESSION['user']['id'], $condition);
            if (!$contacts)
            {
                $return['result'] = 'Aucun contact dans le groupe.';
                echo json_encode($return);

                return false;
            }

            $contacts_name = [];
            foreach ($contacts as $contact)
            {
                $contacts_name[] = $contact['name'];
            }

            $visible_names = array_slice($contacts_name, 0, 5);
            $how_many_more = count($contacts_name) - count($visible_names);

            $result_text = 'Contacts du groupe : ' . implode(', ', $visible_names);

            if ($how_many_more > 0)
            {
                $result_text .= ", et {$how_many_more} autres.";
            }

            $return['result'] = $result_text;
            $return['success'] = true;
            echo json_encode($return);

            return true;
        }

        /**
         * Return the list of groups as JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_conditional_group->list_for_user($_SESSION['user']['id']));
        }
    }
