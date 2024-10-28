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
     * Cette classe gère les accès bdd pour les receivedes.
     */
    class Received extends StandardModel
    {
        const STATUS_UNREAD = 'unread';
        const STATUS_READ = 'read';

        /**
         * @param int     $id_user        : User id
         * @param ?int    $limit          : Number of entry to return
         * @param ?int    $offset         : Number of entry to avoid
         * @param ?string $search         : String to search for
         * @param ?array  $search_columns : List of columns to search on
         * @param ?string $order_column   : Name of the column to order by
         * @param bool    $order_desc     : Should result be ordered DESC, if false order ASC
         * @param bool    $count          : Should the query only count results
         * @param bool    $unread         : Should only unread messages be returned
         *
         * @return array : Entrys list
         */
        public function datatable_list_for_user(int $id_user, ?int $limit = null, ?int $offset = null, ?string $search = null, ?array $search_columns = [], ?string $order_column = null, bool $order_desc = false, bool $count = false, bool $unread = false)
        {
            $params = [
                'id_user' => $id_user,
            ];

            $query = $count ? 'SELECT COUNT(*) as nb' : 'SELECT * ';
            $query .= '
                FROM (
                    SELECT received.*, contact.name as contact_name, phone.name as phone_name, IF(contact.name IS NULL, received.origin, CONCAT(received.origin, " (", contact.name, ")")) as searchable_origin
                    FROM received
                    LEFT JOIN contact
                    ON contact.number = received.origin
                    AND contact.id_user = received.id_user
                    LEFT JOIN phone
                    ON phone.id = received.id_phone
                    WHERE received.id_user = :id_user
                    ' . ($unread ? ' AND received.status = \'unread\'' : '') . '
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
         * Return a list of unread received messages for a user.
         * Add a column contact_name and phone_name when available.
         *
         * @param int  $id_user : user id
         * @param ?int $limit   : Number of entry to return or null
         * @param ?int $offset  : Number of entry to ignore or null
         *
         * @return array
         */
        public function list_unread_for_user(int $id_user, $limit, $offset)
        {
            $query = '
                SELECT received.*, contact.name as contact_name, phone.name as phone_name
                FROM received
                LEFT JOIN contact
                ON contact.number = received.origin
                AND contact.id_user = received.id_user
                LEFT JOIN phone
                ON phone.id = received.id_phone
                WHERE received.id_user = :id_user
                AND status = :status
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
                'status' => self::STATUS_UNREAD,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Count number of unread received sms for user.
         *
         * @param int $id_user : user id
         *
         * @return int : Number of received SMS for user
         */
        public function count_unread_for_user(int $id_user)
        {
            $query = '
                SELECT COUNT(id) as nb
                FROM received
                WHERE id_user = :id_user
                AND status = \'unread\'
            ';

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params)[0]['nb'] ?? 0;
        }

        /**
         * Return x last receiveds message for a user, order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of receiveds messages to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            $nb_entry = (int) $nb_entry;

            $query = '
                SELECT *
                FROM received
                WHERE id_user = :id_user
                ORDER BY at ASC
                LIMIT ' . $nb_entry;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return receiveds for an origin and a user.
         *
         * @param int    $id_user : User id
         * @param string $origin  : Number who sent the message
         *
         * @return array
         */
        public function gets_by_origin_and_user(int $id_user, string $origin)
        {
            $query = '
                SELECT *
                FROM received
                WHERE id_user = :id_user
                AND origin = :origin
            ';

            $params = [
                'id_user' => $id_user,
                'origin' => $origin,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return sendeds for an origin and a user since a date.
         *
         * @param int    $id_user : User id
         * @param string $since   : Date we want messages since
         * @param string $origin  : Number who sent the message
         *
         * @return array
         */
        public function gets_since_date_by_origin_and_user(int $id_user, string $since, string $origin)
        {
            $query = '
                SELECT *
                FROM received
                WHERE id_user = :id_user
                AND origin = :origin
                AND at > :since
            ';

            $params = [
                'id_user' => $id_user,
                'origin' => $origin,
                'since' => $since,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Get number of sended SMS for every date since a date for a specific user.
         *
         * @param int       $id_user : user id
         * @param \DateTime $date    : Date since which we want the messages
         *
         * @return array
         */
        public function count_by_day_since_for_user(int $id_user, $date)
        {
            $query = "
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM received
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
         * Return all discussions (ie : numbers we have a message received from or sended to) for a user.
         *
         * @param int $id_user : User id
         *
         * @return array
         */
        public function get_discussions_for_user(int $id_user, ?int $nb_entry = null, ?int $page = null)
        {
            $query = '
                SELECT at, destination AS number, contact.name AS contact_name
                FROM sended
                LEFT JOIN contact ON contact.number = sended.destination
                WHERE sended.id_user = :id_user

                UNION ALL

                SELECT at, origin AS number, contact.name AS contact_name
                FROM received
                LEFT JOIN contact ON contact.number = received.origin
                WHERE received.id_user = :id_user

                ORDER BY at DESC
            ';

            $params = ['id_user' => $id_user];

            if ($nb_entry !== null)
            {
                $query .= 'LIMIT ' . intval($nb_entry) * intval($page) . ', ' . intval($nb_entry);
            }

            $results = $this->_run_query($query, $params);
            return $results;
        }

        /**
         * Get SMS received since a date for a user.
         *
         * @param int $id_user : User id
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date_for_user(int $id_user, $date)
        {
            $query = "
                SELECT *
                FROM received
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
         * Find messages received since a date for a certain origin and user.
         *
         * @param int $id_user : User id
         * @param $date : Date we want messages sinces
         * @param string $origin : Origin number
         *
         * @return array
         */
        public function get_since_by_date_for_origin_and_user(int $id_user, $date, string $origin)
        {
            $query = "
                SELECT *
                FROM received
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND origin = :origin
                AND id_user = :id_user
                ORDER BY at ASC
            ";

            $params = [
                'id_user' => $id_user,
                'date' => $date,
                'origin' => $origin,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Find last received message for an origin and user.
         *
         * @param int    $id_user : User id
         * @param string $origin  : Origin number
         *
         * @return array
         */
        public function get_last_for_origin_and_user(int $id_user, string $origin)
        {
            $query = '
                SELECT *
                FROM received
                WHERE origin = :origin
                AND id_user = :id_user
                ORDER BY at DESC
                LIMIT 0,1
            ';

            $params = [
                'origin' => $origin,
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
            return 'received';
        }
    }
