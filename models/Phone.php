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

        const STATUS_AVAILABLE = 'available'; # Everything OK 
        const STATUS_UNAVAILABLE = 'unavailable'; # RaspiSMS cannot communication with the phone 
        const STATUS_DISABLED = 'disabled'; # Phone have been manually or automatically disabled by user/system
        const STATUS_NO_CREDIT = 'no_credit'; # Phone have no more credit available
        const STATUS_LIMIT_REACHED = 'limit_reached'; # We reached the limit in of SMS in RaspiSMS for this phone

        /**
         * Return all phones that belongs to active users
         *
         * @return array
         */
        public function get_all_for_active_users()
        {
            $query = '
                SELECT phone.*
                FROM phone
                LEFT JOIN user
                ON phone.id_user = user.id
                WHERE user.status = :status
            ';

            $params = [
                'status' => \models\User::STATUS_ACTIVE,
            ];

            $result = $this->_run_query($query, $params);

            return $result;
        }

        /**
         * Return a phone by his name and user.
         *
         * @param int    $id_user : user id
         * @param string $name    :  phone name
         *
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->_select_one('phone', ['name' => $name, 'id_user' => $id_user]);
        }

        /**
         * Return a phone by his name.
         *
         * @param string $name :  phone name
         *
         * @return array
         */
        public function get_by_name(string $name)
        {
            return $this->_select_one('phone', ['name' => $name]);
        }


        /**
         * Return a list of phone limits
         *
         * @param int $id_phone : Phone id
         *
         * @return array
         */
        public function get_limits(int $id_phone)
        {
            return $this->_select('phone_limit', ['id_phone' => $id_phone]);
        }

        /**
         * Add a limit for a phone.
         *
         * @param int $id_phone      : Phone id
         * @param int $volume        : Limit in volume of SMS
         * @param string $startpoint :  A relative time to use as startpoint for counting volume. See https://www.php.net/manual/en/datetime.formats.relative.php
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_phone_limit(int $id_phone, int $volume, string $startpoint)
        {
            $success = $this->_insert('phone_limit', ['id_phone' => $id_phone, 'volume' => $volume, 'startpoint' => $startpoint]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Delete limits for a phone
         *
         * @param array $id_phone : Phone id
         *
         * @return array
         */
        public function delete_phone_limits(int $id_phone)
        {
            return $this->_delete('phone_limit', ['id_phone' => $id_phone]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'phone';
        }
    }
