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

    class Event extends StandardModel
    {
        /**
         * Return a list of event for a user.
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
                    SELECT * FROM event
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
         * Gets lasts x events for a user order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of events to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->_select('event', ['id_user' => $id_user], 'at', true, $nb_entry);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'event';
        }
    }
