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

    class Phone extends StandardModel
    {
        /**
         * Return a phone by his number and user.
         *
         * @param int    $id_user : user id
         * @param string $number  :  phone number
         *
         * @return array
         */
        public function get_by_number_and_user(int $id_user, string $number)
        {
            return $this->_select_one('phone', ['number' => $number, 'id_user' => $id_user]);
        }

        /**
         * Return a phone by his number.
         *
         * @param string $number :  phone number
         *
         * @return array
         */
        public function get_by_number(string $number)
        {
            return $this->_select_one('phone', ['number' => $number]);
        }

        /**
         * Return table name.
         *
         * @return string
         */
        protected function get_table_name(): string
        {
            return 'phone';
        }
    }
