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

    class SmsStop extends StandardModel
    {
        const SMS_STOP_TAG = 'SMS_STOP';
         
        /**
         * Return a smsstop by his number and user.
         *
         * @param int    $id_user : user id
         * @param string $number  :  phone number
         *
         * @return array
         */
        public function get_by_number_for_user(int $id_user, string $number)
        {
            return $this->_select_one($this->get_table_name(), ['number' => $number, 'id_user' => $id_user]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'smsstop';
        }
    }
