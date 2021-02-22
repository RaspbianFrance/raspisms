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

    class Phone extends StandardController
    {
        protected $model;

        /**
         * Return all phones of a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            return $this->get_model()->gets_for_user($id_user);
        }

        /**
         * Return a phone by his name.
         *
         * @param string $name : Phone name
         *
         * @return array
         */
        public function get_by_name(string $name)
        {
            return $this->get_model()->get_by_name($name);
        }

        /**
         * Return a phone for a user by a name.
         *
         * @param int    $id_user : user id
         * @param string $name    : Phone name
         *
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_and_user($id_user, $name);
        }

        /**
         * Create a phone.
         *
         * @param int         $id_user      : User to insert phone for
         * @param string      $name         : The name of the phone
         * @param string      $adapter      : The adapter to use the phone
         * @param string json $adapter_data : A JSON string representing adapter's data (for example credentials for an api)
         *
         * @return bool|int : false on error, new id on success
         */
        public function create(int $id_user, string $name, string $adapter, string $adapter_data)
        {
            $phone = [
                'id_user' => $id_user,
                'name' => $name,
                'adapter' => $adapter,
                'adapter_data' => $adapter_data,
            ];

            return $this->get_model()->insert($phone);
        }

        /**
         * Update a phone.
         *
         * @param int    $id_user      : User to insert phone for
         * @param int    $id           : Phone id
         * @param string $name         : The name of the phone
         * @param string $adapter      : The adapter to use the phone
         * @param array  $adapter_data : An array of the data of the adapter (for example credentials for an api)
         *
         * @return bool : false on error, true on success
         */
        public function update_for_user(int $id_user, int $id, string $name, string $adapter, array $adapter_data): bool
        {
            $phone = [
                'id_user' => $id_user,
                'name' => $name,
                'adapter' => $adapter,
                'adapter_data' => json_encode($adapter_data),
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $id, $phone);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Phone($this->bdd);

            return $this->model;
        }
    }
