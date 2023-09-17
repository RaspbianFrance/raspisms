<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    class Setting extends StandardModel
    {
        /**
         * Update a setting for a user by his name.
         *
         * @param int    $id_user : user id
         * @param string $name    : setting name
         * @param mixed  $value   : new value of the setting
         *
         * @return int : number of modified settings
         */
        public function update_by_name_for_user(int $id_user, string $name, $value)
        {
            return $this->_update($this->get_table_name(), ['value' => $value], ['id_user' => $id_user, 'name' => $name]);
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
            return $this->_select_one($this->get_table_name(), ['name' => $name, 'id_user' => $id_user]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'setting';
        }
    }
