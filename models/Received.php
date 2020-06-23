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
         * Return a list of unread received for a user.
         *
         * @param int $id_user : User id
         * @param int $limit   : Max results to return
         * @param int $offset  : Number of results to ignore
         */
        public function list_unread_for_user($id_user, $limit, $offset)
        {
            $limit = (int) $limit;
            $offset = (int) $offset;

            $query = ' 
                SELECT * FROM received
                WHERE status = \'unread\'
                AND id_user = :id_user
                LIMIT ' . $limit . ' OFFSET ' . $offset;

            $params = [
                'id_user' => $id_user,
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
        public function get_discussions_for_user(int $id_user)
        {
            $query = ' 
                    SELECT at, number
                    FROM (
                        SELECT at, destination as number FROM sended
                        WHERE id_user = :id_user
                        UNION (
                            SELECT at, origin as number FROM received
                            WHERE id_user = :id_user
                        )
                    ) as discussions
                    GROUP BY number
                    ORDER BY at DESC
            ';

            $params = ['id_user' => $id_user];

            return $this->_run_query($query, $params);
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
         *
         * @return string
         */
        protected function get_table_name(): string
        {
            return 'received';
        }
    }
