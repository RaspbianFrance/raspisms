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
        /**
         * Return table name
         * @return string 
         */
        protected function get_table_name() : string { return 'sended'; }
        
        
        /**
         * Return an entry by his id for a user
         * @param int $id_user : user id
         * @param int $id : entry id
         * @return array
         */
        public function get_for_user(int $id_user, int $id)
        {
            $query = ' 
                SELECT * FROM `' . $this->get_table_name() . '`
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
            ';

            $params = [
                'id_user' => $id_user,
            ];

            $receiveds = $this->_run_query($query, $params);
            return $receiveds[0] ?? [];
        }
        
        
        /**
         * Return a list of sended for a user
         * @param int $id_user : User id
         * @param int $limit  : Max results to return
         * @param int $offset : Number of results to ignore
         */
        public function list_for_user($id_user, $limit, $offset)
        {
            $limit = (int) $limit;
            $offset = (int) $offset;

            $query = ' 
                SELECT * FROM sended
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                LIMIT ' . $limit . ' OFFSET ' . $offset;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }


        /**
         * Return a list of sendeds in a group of ids and for a user
         * @param int $id_user : user id
         * @param array $ids : ids of sendeds to find
         * @return array 
         */
        public function gets_in_for_user(int $id_user, $ids)
        {
            $query = ' 
                SELECT * FROM sended
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                AND id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];
            $params['id_user'] = $id_user; 

            return $this->_run_query($query, $params);
        }
        
        /**
         * Delete a entry by his id for a user
         * @param int $id_user : User id
         * @param int $id : Entry id
         * @return int : Number of removed rows
         */
        public function delete_for_user(int $id_user, int $id)
        {
            $query = ' 
                DELETE FROM sended
                WHERE id = :id
                AND origin IN (SELECT number FROM phone WHERE id_user = :id_user)
            ';

            $params = ['id_user' => $id_user, 'id' => $id];
            
            return $this->_run_query($query, $params, self::ROWCOUNT);
        }


        /**
         * Update a sended sms for a user
         * @param int $id_user : User id
         * @param int   $id      : Entry id
         * @param array $datas : datas to update
         *
         * @return int : number of modified rows
         */
        public function update_for_user (int $id_user, int $id, array $datas)
        {
            $params = [];
            $sets = [];

            foreach ($datas as $label => $value)
            {
                $label = preg_replace('#[^a-zA-Z0-9_]#', '', $label);
                $params['set_' . $label] = $value;
                $sets[] = '`' . $label . '` = :set_' . $label . ' ';
            }

            $query = '
                UPDATE `sended`
                SET ' . implode(', ', $sets) . '
                WHERE id = :id
                AND origin IN (SELECT number FROM phone WHERE id_user = :id_user)
            ';

            //If try to update origin, also check it does belong to user
            if ($sets['set_origin'] ?? false)
            {
                $query .= ' AND :set_origin IN (SELECT number FROM phone WHERE id_user = :id_user)'
            }

            $params['id'] = $id;
            $params['id_user'] = $id_user;

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }
        
        
        /**
         * Count number of sended sms for user
         * @param int $id_user : user id
         * @return int : Number of sended SMS for user
         */
        public function count_for_user($id_user)
        {
            $query = '
                SELECT COUNT(id) as nb
                FROM sended
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
            ';

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params)[0]['nb'] ?? 0;
        }

        
        /**
         * Return x last sendeds message for a user, order by date
         * @param int $id_user : User id
         * @param int $nb_entry : Number of sendeds messages to return
         * @return array 
         */
        public function get_lasts_by_date_for_user($id_user, $nb_entry)
        {
            $nb_entry = (int) $nb_entry;

            $query = '
                SELECT *
                FROM sended
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                ORDER BY at ASC
                LIMIT ' . $nb_entry;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }


        /**
         * Return sendeds for an destination and a user
         * @param int $id_user : User id
         * @param string $destination : Number who sent the message
         * @return array
         */
        public function gets_by_destination_for_user(int $id_user, string $destination)
        {
            $nb_entry = (int) $nb_entry;

            $query = '
                SELECT *
                FROM sended
                WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                AND destination = :destination
            ';

            $params = [
                'id_user' => $id_user,
                'destination' => $destination,
            ];

            return $this->_run_query($query, $params);
        }


        /**
         * Get number of sended SMS for every date since a date for a specific user
         * @param int $id_user : user id
         * @param \DateTime $date : Date since which we want the messages
         * @return array
         */
        public function count_by_day_since_for_user($id_user, $date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM sended
                WHERE at > :date
                AND origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                GROUP BY at_ymd
            ";

            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return all discussions (ie : numbers we have a message sended from or sended to) for a user
         * @param int $id_user : User id
         * @return array
         */
        public function get_discussions_for_user(int $id_user)
        {
            $query = ' 
                    SELECT at, number
                    FROM (
                        SELECT at, origin as number FROM sended
                        WHERE destination IN (SELECT number FROM phone WHERE id_user = :id_user)
                        UNION (
                            SELECT at, destination as number FROM sended
                            WHERE origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                        )
                    ) as discussions
                    GROUP BY number
                    ORDER BY at DESC
            ';

            $params = ['id_user' => $id_user];
            return $this->_run_query($query, $params);
        }

        /**
         * Get SMS sended since a date for a user 
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param int $id_user : User id
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date_for_user($date, $id_user)
        {
            $query = " 
                SELECT *
                FROM sended
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                ORDER BY at ASC";
            
            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Find messages sended since a date for a certain destination and user
         * @param int $id_user : User id
         * @param $date : Date we want messages sinces
         * @param string $destination : Origin number
         * @return array
         */
        public function get_since_by_date_for_destination_and_user(int $id_user, $date, string $destination)
        {
            $query = " 
                SELECT *
                FROM sended
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND destination = :destination
                AND origin IN (SELECT number FROM phone WHERE id_user = :id_user)
                ORDER BY at ASC
            ";

            $params = [
                'id_user' => $id_user
                'date' => $date,
                'destination' => $destination,
            ];

            return $this->_run_query($query, $params);
        }
    }
