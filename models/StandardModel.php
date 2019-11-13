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
     * Abstract class reprensenting the Standard Model
     * This class implement/define most common methods for models
     */
    abstract class StandardModel extends \descartes\Model
    {
        /**
         * Return table name
         * @return string 
         */
        abstract protected function get_table_name() : string;


        /**
         * Return an entry by his id
         * @param int $id : entry id
         * @return array
         */
        public function get(int $id)
        {
            return $this->_select_one($this->get_table_name(), ['id' => $id]);
        }
        
        
        /**
         * Return an entry by his id for a user
         * @param int $id_user : user id
         * @param int $id : entry id
         * @return array
         */
        public function get_for_user(int $id_user, int $id)
        {
            return $this->_select_one($this->get_table_name(), ['id' => $id]);
        }


        /**
         * Return a list of entries for a user
         * @param int $id_user : user id 
         * @param int $limit  : Number of entry to return
         * @param int $offset : Number of entry to ignore
         * @return array
         */
        public function list_for_user (int $id_user, $limit, $offset)
        {
            return $this->_select($this->get_table_name(), ['id_user' => $id_user], null, false, $limit, $offset);
        }


        /**
         * Return a list of entries in a group of ids and for a user
         * @param int $id_user : user id
         * @param array $ids : ids of entries to find
         * @return array 
         */
        public function gets_in_for_user(int $id_user, $ids)
        {
            $query = ' 
                SELECT * FROM ' . $this->get_table_name() . '
                WHERE id_user = :id_user
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
        public function delete_for_user(int $id_user, $id)
        {
            return $this->_delete($this->get_table_name(), ['id_user' => $id_user, 'id' => $id]);
        }


        /**
         * Insert a entry
         * @param array $entry : Entry to insert
         * @return mixed bool|int : false on error, new entry id else
         */
        public function insert($entry)
        {
            $result = $this->_insert($this->get_table_name(), $entry);
            return ($result ? $this->_last_id() : false);
        }


        /**
         * Update a entry for a user
         * @param int $id_user : User id
         * @param int   $id      : Entry id
         * @param array $datas : datas to update
         *
         * @return int : number of modified rows
         */
        public function update_for_user(int $id_user, $id, $entry)
        {
            return $this->_update($this->get_table_name(), $entry, ['id_user' => $id_user, 'id' => $id]);
        }


        /**
         * Count number of entry for a user
         * @param int $id_user : User id
         * @return int : number of entries
         */
        public function count_for_user(int $id_user)
        {
            return $this->_count($this->get_table_name(), ['id_user' => $id_user]);
        }
    }
