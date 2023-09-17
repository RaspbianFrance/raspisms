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

    class Setting extends StandardController
    {
        protected $model;

        /**
         * Return all settings of a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            $settings = $this->get_model()->gets_for_user($id_user);
            $settings_array = [];

            foreach ($settings as $setting)
            {
                $settings_array[$setting['name']] = $setting['value'];
            }

            return $settings_array;
        }

        /**
         * Get a user setting by his name for a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function get_by_name_for_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_for_user($id_user, $name);
        }

        /**
         * Update a setting by his name and user id.
         *
         * @param int    $id_user : user id
         * @param string $name    : setting name
         * @param mixed  $value
         *
         * @return int : number of modified lines
         */
        public function update_for_user(int $id_user, string $name, $value): bool
        {
            return (bool) $this->get_model()->update_by_name_for_user($id_user, $name, $value);
        }

        /**
         * Create a new setting.
         *
         * @param int    $id_user : user id
         * @param string $name    : setting name
         * @param mixed  $value   : value of the setting
         */
        public function create(int $id_user, string $name, $value): bool
        {
            $setting = [
                'id_user' => $id_user,
                'name' => $name,
                'value' => $value,
            ];

            return (bool) $this->get_model()->insert($setting);
        }

        /**
         * Generate and insert default settings for a user.
         *
         * @param int $id_user : user id
         *
         * @return bool
         */
        public function create_defaults_for_user(int $id_user)
        {
            $all_success = true;
            foreach (USER_DEFAULT_SETTINGS as $name => $value)
            {
                $success = $this->create($id_user, $name, $value);
                $all_success = ($all_success && $success);
            }

            return $all_success;
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\Setting
        {
            $this->model = $this->model ?? new \models\Setting($this->bdd);

            return $this->model;
        }
    }
