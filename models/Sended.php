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
     * Cette classe gère les accès bdd pour les sendedes.
     */
    class Sended extends StandardModel
    {
        const STATUS_UNKNOWN = 'unknown';
        const STATUS_DELIVERED = 'delivered';
        const STATUS_FAILED = 'failed';

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
                    SELECT sended.*, contact.name as contact_name, phone.name as phone_name, IF(contact.name IS NULL, sended.destination, CONCAT(sended.destination, " (", contact.name, ")")) as searchable_destination
                    FROM sended
                    LEFT JOIN contact
                    ON contact.number = sended.destination
                    AND contact.id_user = sended.id_user
                    LEFT JOIN phone
                    ON phone.id = sended.id_phone
                    WHERE sended.id_user = :id_user
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
         * Return x last sendeds message for a user, order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of sendeds messages to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user($id_user, $nb_entry)
        {
            $nb_entry = (int) $nb_entry;

            $query = '
                SELECT *
                FROM sended
                WHERE id_user = :id_user
                ORDER BY at ASC
                LIMIT ' . $nb_entry;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return sendeds for an destination and a user.
         *
         * @param int    $id_user     : User id
         * @param string $destination : Number who sent the message
         *
         * @return array
         */
        public function gets_by_destination_and_user(int $id_user, string $destination)
        {
            $query = '
                SELECT *
                FROM sended
                WHERE id_user = :id_user
                AND destination = :destination
            ';

            $params = [
                'id_user' => $id_user,
                'destination' => $destination,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return sendeds for an destination and a user since a date.
         *
         * @param int    $id_user     : User id
         * @param string $since       : Date we want messages since
         * @param string $destination : Number who sent the message
         *
         * @return array
         */
        public function gets_since_date_by_destination_and_user(int $id_user, string $since, string $destination)
        {
            $query = '
                SELECT *
                FROM sended
                WHERE id_user = :id_user
                AND destination = :destination
                AND at > :since
            ';

            $params = [
                'id_user' => $id_user,
                'destination' => $destination,
                'since' => $since,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return sended for an uid and an adapter.
         *
         * @param int    $id_user : Id of the user
         * @param string $uid     : Uid of the sended
         * @param string $adapter : Adapter used to send the message
         *
         * @return array
         */
        public function get_by_uid_and_adapter_for_user(int $id_user, string $uid, string $adapter)
        {
            return $this->_select_one('sended', ['id_user' => $id_user, 'uid' => $uid, 'adapter' => $adapter]);
        }

        /**
         * Get number of sended SMS for every date since a date for a specific user.
         *
         * @param int       $id_user : user id
         * @param \DateTime $date    : Date since which we want the messages
         *
         * @return array
         */
        public function count_by_day_since_for_user($id_user, $date)
        {
            $query = "
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM sended
                WHERE at > :date
                AND id_user = :id_user
                GROUP BY at_ymd
            ";

            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Get SMS sended since a date for a user.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param int $id_user : User id
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date_for_user($date, $id_user)
        {
            $query = "
                SELECT *
                FROM sended
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND id_user = :id_user
                ORDER BY at ASC";

            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Find last sended message for a destination and user.
         *
         * @param int    $id_user     : User id
         * @param string $destination : Destination number
         *
         * @return array
         */
        public function get_last_for_destination_and_user(int $id_user, string $destination)
        {
            $query = '
                SELECT *
                FROM sended
                WHERE destination = :destination
                AND id_user = :id_user
                ORDER BY at DESC
                LIMIT 0,1
            ';

            $params = [
                'destination' => $destination,
                'id_user' => $id_user,
            ];

            $result = $this->_run_query($query, $params);

            return $result[0] ?? [];
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'sended';
        }
    }
