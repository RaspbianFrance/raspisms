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

    class Contact extends StandardModel
    {
        /**
         * Return a contact by his number for a user.
         *
         * @param int    $id_user : User id
         * @param string $number  : Contact number
         *
         * @return array
         */
        public function get_by_number_and_user(int $id_user, string $number)
        {
            return $this->_select_one($this->get_table_name(), ['id_user' => $id_user, 'number' => $number]);
        }

        /**
         * Return a contact by his name for a user.
         *
         * @param int    $id_user : User id
         * @param string $name    : Contact name
         *
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->_select($this->get_table_name(), ['id_user' => $id_user, 'name' => $name]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'contact';
        }
    }
