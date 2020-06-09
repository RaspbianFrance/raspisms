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

    class ConditionalGroup extends StandardController
    {
        protected $model;

        /**
         * Create a new group for a user.
         *
         * @param int    $id_user   : user id
         * @param string $name      : Group name
         * @param string $condition : Condition for forming group content
         *
         * @return mixed bool|int : false on error, new group id
         */
        public function create(int $id_user, string $name, string $condition)
        {
            $conditional_group = [
                'id_user' => $id_user,
                'name' => $name,
                'condition' => $condition,
            ];

            $internal_ruler = new Ruler();
            $valid_condition = $internal_ruler->validate_condition($condition, ['contact' => (object) ['datas' => (object) null]]);
            if (!$valid_condition)
            {
                return false;
            }

            $id_group = $this->get_model()->insert($conditional_group);
            if (!$id_group)
            {
                return false;
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'CONDITIONAL_GROUP_ADD', 'Ajout du groupe conditionnel : ' . $name);

            return $id_group;
        }

        /**
         * Update a group for a user.
         *
         * @param int    $id_user   : User id
         * @param int    $id_group  : Group id
         * @param string $name      : Group name
         * @param string $condition : Condition for forming group content
         *
         * @return bool : False on error, true on success
         */
        public function update_for_user(int $id_user, int $id_group, string $name, string $condition)
        {
            $conditional_group = [
                'name' => $name,
                'condition' => $condition,
            ];

            $internal_ruler = new Ruler();
            $valid_condition = $internal_ruler->validate_condition($condition, ['contact' => (object) ['datas' => (object) null]]);
            if (!$valid_condition)
            {
                return false;
            }

            $result = $this->get_model()->update_for_user($id_user, $id_group, $conditional_group);
            if (!$result)
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
         * Gets the user's contacts that respects a condition.
         *
         * @param int    $id_user   : User id
         * @param string $condition : Condition string to verify
         *
         * @return array
         */
        public function get_contacts_for_condition_and_user(int $id_user, string $condition): array
        {
            $internal_contacts = new Contact($this->bdd);
            $contacts = $internal_contacts->gets_for_user($id_user);

            $ruler = new Ruler();

            foreach ($contacts as $key => $contact)
            {
                $contact['datas'] = json_decode($contact['datas']);
                $contact = (object) $contact;

                $datas = ['contact' => $contact->datas];
                $is_valid = $ruler->evaluate_condition($condition, $datas);
                if (!$is_valid)
                {
                    unset($contacts[$key]);
                }
            }

            return $contacts;
        }

        /**
         * Get the model for the Controller.
         *
         * @return \descartes\Model
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\ConditionalGroup($this->bdd);

            return $this->model;
        }
    }
