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
         * Return all hones that belongs to active users
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
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'phone';
        }
    }
