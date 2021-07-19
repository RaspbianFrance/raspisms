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
         * Return a list of sended messages for a user.
         * Add a column contact_name and phone_name when available.
         *
         * @param int  $id_user : user id
         * @param ?int $limit   : Number of entry to return or null
         * @param ?int $offset  : Number of entry to ignore or null
         *
         * @return array
         */
        public function datatable_list_for_user(int $id_user, ?int $limit = null, ?int $offset = null, ?string $search = null, ?array $search_columns = [], ?string $order_column = null, bool $order_desc = false, ?bool $count = false)
        {
            $params = [
                'id_user' => $id_user,
            ];

            $query = $count ? 'SELECT COUNT(*) as nb' : 'SELECT * ';
            $query .= '
                FROM (
                    SELECT * FROM contact
                    WHERE id_user = :id_user
                ) as results
            ';

            if ($search && $search_columns)
            {
                $like_search = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
                $params[':like_search'] = $like_search;

                $query .= ' WHERE (0';

                foreach ($search_columns as $column)
                {
                    $query .= ' OR ' . $column . ' LIKE :like_search';
                }

                $query .= ')';
            }

            if ($order_column)
            {
                $query .= ' ORDER BY ' . $order_column . ($order_desc ? ' DESC' : ' ASC');
            }

            if (null !== $limit && !$count)
            {
                $limit = (int) $limit;

                $query .= ' LIMIT ' . $limit;
                if (null !== $offset)
                {
                    $offset = (int) $offset;
                    $query .= ' OFFSET ' . $offset;
                }
            }

            return $count ? $this->_run_query($query, $params)[0]['nb'] ?? 0 : $this->_run_query($query, $params);
        }

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
