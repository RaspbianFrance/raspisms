<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    /**
     * Classe des groups.
     */
    class Group extends StandardController
    {
        protected $model;

        /**
         * Create a new group for a user.
         *
         * @param int    $id_user      : user id
         * @param stirng $name         : Group name
         * @param array  $contacts_ids : Ids of the contacts of the group
         *
         * @return mixed bool|int : false on error, new group id
         */
        public function create(int $id_user, string $name, array $contacts_ids)
        {
            $group = [
                'id_user' => $id_user,
                'name' => $name,
            ];

            $id_group = $this->get_model()->insert($group);
            if (!$id_group)
            {
                return false;
            }

            $internal_contact = new Contact($this->bdd);
            foreach ($contacts_ids as $contact_id)
            {
                $contact = $internal_contact->get_for_user($id_user, $contact_id);
                if (!$contact)
                {
                    continue;
                }

                $this->get_model()->insert_group_contact_relation($id_group, $contact_id);
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'GROUP_ADD', 'Ajout group : ' . $name);

            return $id_group;
        }

        /**
         * Update a group for a user.
         *
         * @param int    $id_user      : User id
         * @param int    $id_group     : Group id
         * @param stirng $name         : Group name
         * @param array  $contacts_ids : Ids of the contacts of the group
         *
         * @return bool : False on error, true on success
         */
        public function update_for_user(int $id_user, int $id_group, string $name, array $contacts_ids)
        {
            $group = [
                'name' => $name,
            ];

            $result = $this->get_model()->update_for_user($id_user, $id_group, $group);

            $this->get_model()->delete_group_contact_relations($id_group);

            $internal_contact = new Contact($this->bdd);
            $nb_contact_insert = 0;
            foreach ($contacts_ids as $contact_id)
            {
                $contact = $internal_contact->get_for_user($id_user, $contact_id);
                if (!$contact)
                {
                    continue;
                }

                if ($this->get_model()->insert_group_contact_relation($id_group, $contact_id))
                {
                    ++$nb_contact_insert;
                }
            }

            if (!$result && $nb_contact_insert !== \count($contacts_ids))
            {
                return false;
            }

            return true;
        }

        /**
         * Return a group by his name for a user.
         *
         * @param int    $id_user : User id
         * @param string $name    : Group name
         *
         * @return array
         */
        public function get_by_name_for_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_for_user($id_user, $name);
        }

        /**
         * Get groups contacts.
         *
         * @param int $id_group : Group id
         *
         * @return array : Contacts of the group
         */
        public function get_contacts($id_group)
        {
            return $this->get_model()->get_contacts($id_group);
        }

        /**
         * Get the model for the Controller.
         *
         * @return \descartes\Model
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Group($this->bdd);

            return $this->model;
        }
    }
