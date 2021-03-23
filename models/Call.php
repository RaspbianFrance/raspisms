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

    /**
     * Manage bdd operations for calls
     */ 
    class Call extends StandardModel
    {
        const DIRECTION_INBOUND = 'inbound';
        const DIRECTION_OUTBOUND = 'outbound';

        /**
         * Get a call for a user by his phone and uid
         * 
         * @param int $id_user : user id
         * @param int $id_phone : phone id
         * @param int $uid : call uid
         *
         * @return array : the call or an empty array
         */
        public function get_by_uid_and_phone_for_user($id_user, $id_phone, $uid)
        {
            $where = [
                'id_user' => $id_user,
                'id_phone' => $id_phone,
                'uid' => $uid,
            ];

            return $this->_select_one($this->get_table_name(), $where);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'call';
        }
    }
