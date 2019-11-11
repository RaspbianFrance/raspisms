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

    class Setting extends \descartes\InternalController
    {
        private $model_setting;

        public function __construct(\PDO $bdd)
        {
            $this->model_setting = new \models\Setting($bdd);
        }

        /**
         * Return all settings of a user.
         * @param int $id_user : user id
         * @return array
         */
        public function gets_for_user (int $id_user)
        {
            $settings = $this->model_setting->gets_for_user($id_user);
            $settings_array = [];

            foreach ($settings as $setting)
            {
                $settings_array[$setting['name']] = $setting['value'];
            }

            return $settings_array;
        }


        /**
         * Update a setting by his name and user id.
         * @param int $id_user : user id
         * @param string $name : setting name
         * @param mixed $value
         * @return int : number of modified lines
         */
        public function update (int $id_user, string $name, $value) : bool
        {
            return (bool) $this->model_setting->update($id_user, $name, $value);
        }


        /**
         * Create a new setting
         * @param int $id_user : user id
         * @param string $name : setting name
         * @param mixed $value : value of the setting
         * @return bool 
         */
        public function insert (int $id_user, string $name, $value) : bool
        {
            $setting = [
                'id_user' => $id_user,
                'name' => $name,
                'value' => $value,
            ];

            return (bool) $this->model_setting->insert($setting);
        }
    }
