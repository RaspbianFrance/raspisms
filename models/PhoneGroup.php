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

    class PhoneGroup extends StandardModel
    {
        /**
         * Return a list of phone groups for a user.
         * Add a column nb_phone.
         *
         * @param int  $id_user : user id
         * @param ?int $limit   : Number of entry to return or null
         * @param ?int $offset  : Number of entry to ignore or null
         *
         * @return array
         */
        public function list_for_user(int $id_user, $limit, $offset)
        {
            $query = '
                SELECT pg.*, COUNT(pgp.id) as nb_phone
                FROM `phone_group` as pg
                LEFT JOIN phone_group_phone as pgp
                ON pg.id = pgp.id_phone_group
                WHERE pg.id_user = :id_user
                GROUP BY pg.id
            ';

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

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return a phone group by his name for a user.
         *
         * @param int    $id_user : User id
         * @param string $name    : Group name
         *
         * @return array
         */
        public function get_by_name_for_user(int $id_user, string $name)
        {
            return $this->_select_one($this->get_table_name(), ['id_user' => $id_user, 'name' => $name]);
        }

        /**
         * Delete relations between phone group and phone for a group.
         *
         * @param int $id_phone_group : Group id
         *
         * @return int : Number of deleted rows
         */
        public function delete_phone_group_phone_relations(int $id_phone_group)
        {
            return $this->_delete('phone_group_phone', ['id_phone_group' => $id_phone_group]);
        }

        /**
         * Insert a relation between a phone group and a phone.
         *
         * @param int $id_phone_group   : Phone Group id
         * @param int $id_phone : Phone id
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_phone_group_phone_relation(int $id_phone_group, int $id_phone)
        {
            $success = (bool) $this->_insert('phone_group_phone', ['id_phone_group' => $id_phone_group, 'id_phone' => $id_phone]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Get phone groups phones.
         *
         * @param int $id_phone_group : Phone Group id
         *
         * @return array : Phones of the group
         */
        public function get_phones(int $id_phone_group)
        {
            $query = '
                SELECT *
                FROM `phone`
                WHERE id IN (SELECT id_phone FROM `phone_group_phone` WHERE id_phone_group = :id_phone_group)
            ';

            $params = ['id_phone_group' => $id_phone_group];

            return $this->_run_query($query, $params);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'phone_group';
        }
    }
