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
     * Manage bdd operations for calls.
     */
    class Call extends StandardModel
    {
        const DIRECTION_INBOUND = 'inbound';
        const DIRECTION_OUTBOUND = 'outbound';

        /**
         * Return a list of call for a user.
         * Add a column contact_name and phone_name when available.
         *
         * @param int  $id_user : user id
         * @param ?int $limit   : Number of entry to return or null
         * @param ?int $offset  : Number of entry to ignore or null
         * @param ?int $after_id  : If provided use where id > $after_id instead of offset 
         * @param ?int $before_id  : If provided use where id < $before_id instead of offset 
         *
         *
         * @return array
         */
        public function list_for_user(int $id_user, $limit, $offset, ?int $after_id = null, ?int $before_id = null)
        {
            $query = '
                SELECT `call`.*, contact.name as contact_name, phone.name as phone_name
                FROM `call`
                LEFT JOIN contact
                ON contact.number = `call`.destination
                OR contact.number = `call`.origin
                LEFT JOIN phone
                ON phone.id = `call`.id_phone
                WHERE `call`.id_user = :id_user
            ';

            $params = [
                'id_user' => $id_user,
            ];

            if ($after_id || $before_id)
            {
                $offset = null;
            }

            if ($after_id)
            {
                $query .= '
                    AND `call`.id > :after_id
                    ORDER BY `call`.id
                ';
                $params['after_id'] = $after_id;
            }
            elseif ($before_id)
            {
                $query .= '
                    AND `call`.id < :before_id
                    ORDER BY `call`.id DESC
                ';
                $params['before_id'] = $before_id;
            }
            else
            {
                $query .= ' ORDER BY `call`.id';
            }


            if (null !== $limit)
            {
                $limit = (int) $limit;

                $query .= ' LIMIT ' . $limit;
                if (null !== $offset)
                {
                    $offset = (int) $offset;
                    $query .= ' OFFSET ' . $offset;
                }
            }

            if ($before_id)
            {
                return array_reverse($this->_run_query($query, $params));
            }

            return $this->_run_query($query, $params);
        }

        /**
         * Get a call for a user by his phone and uid.
         *
         * @param int $id_user  : user id
         * @param int $id_phone : phone id
         * @param int $uid      : call uid
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
