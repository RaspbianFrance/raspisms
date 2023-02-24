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
    class PhoneGroup extends StandardController
    {
        protected $model;

        /**
         * Create a new phone group for a user.
         *
         * @param int    $id_user      : user id
         * @param stirng $name         : Group name
         * @param array  $phones_ids : Ids of the phones of the group
         *
         * @return mixed bool|int : false on error, new group id
         */
        public function create(int $id_user, string $name, array $phones_ids)
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

            $internal_phone = new Phone($this->bdd);
            foreach ($phones_ids as $phone_id)
            {
                $phone = $internal_phone->get_for_user($id_user, $phone_id);
                if (!$phone)
                {
                    continue;
                }

                $this->get_model()->insert_phone_group_phone_relation($id_group, $phone_id);
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'PHONE_GROUP_ADD', 'Ajout phone group : ' . $name);

            return $id_group;
        }

        /**
         * Update a phone group for a user.
         *
         * @param int    $id_user      : User id
         * @param int    $id_group     : Group id
         * @param stirng $name         : Group name
         * @param array  $phones_ids   : Ids of the phones of the group
         *
         * @return bool : False on error, true on success
         */
        public function update_for_user(int $id_user, int $id_group, string $name, array $phones_ids)
        {
            $group = [
                'name' => $name,
            ];

            $result = $this->get_model()->update_for_user($id_user, $id_group, $group);

            $this->get_model()->delete_phone_group_phone_relations($id_group);

            $internal_phone = new Phone($this->bdd);
            $nb_phone_insert = 0;
            foreach ($phones_ids as $phone_id)
            {
                $phone = $internal_phone->get_for_user($id_user, $phone_id);
                if (!$phone)
                {
                    continue;
                }

                if ($this->get_model()->insert_phone_group_phone_relation($id_group, $phone_id))
                {
                    ++$nb_phone_insert;
                }
            }

            if (!$result && $nb_phone_insert !== \count($phones_ids))
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
         * Get groups phones.
         *
         * @param int $id_group : Group id
         *
         * @return array : phones of the group
         */
        public function get_phones($id_group)
        {
            return $this->get_model()->get_phones($id_group);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\PhoneGroup
        {
            $this->model = $this->model ?? new \models\PhoneGroup($this->bdd);

            return $this->model;
        }
    }
