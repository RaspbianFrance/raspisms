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
     * Class for user table administration. Not a standard model because has absolutly no user based restrictions.
     */
    class User extends \descartes\Model
    {
        const STATUS_SUSPENDED = 'suspended';
        const STATUS_ACTIVE = 'active';

        /**
         * Find a user by his id.
         *
         * @param string $id : User id
         *
         * @return mixed array
         */
        public function get($id)
        {
            return $this->_select_one('user', ['id' => $id]);
        }

        /**
         * Return all active users.
         *
         * @return array
         */
        public function get_all_active()
        {
            return $this->_select('user', ['status' => self::STATUS_ACTIVE]);
        }

        /**
         * Find user by ids.
         *
         * @param array $ids : users ids
         *
         * @return array
         */
        public function gets_in_by_id($ids)
        {
            if (!$ids)
            {
                return [];
            }

            $query = '
                SELECT * FROM `user`
                WHERE id ';

            $params = [];

            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Find a user using his email.
         *
         * @param string $email : User email
         *
         * @return mixed array
         */
        public function get_by_email($email)
        {
            return $this->_select_one('user', ['email' => $email]);
        }

        /**
         * Get a user by his api_key address.
         *
         * @param string $api_key : User api key
         *
         * @return mixed boolean | array : false if cannot find user for this api key, the user else
         */
        public function get_by_api_key(string $api_key)
        {
            return $this->_select_one('user', ['api_key' => $api_key]);
        }

        /**
         * Return list of user.
         *
         * @param int $limit  : Number of user to return
         * @param int $offset : Number of user to skip
         */
        public function list($limit, $offset)
        {
            return $this->_select('user', [], null, false, $limit, $offset);
        }

        /**
         * Delete a user.
         *
         * @param int $id : Id de l'utilisateur a supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function remove($id)
        {
            return $this->_delete('user', ['id' => $id]);
        }

        /**
         * Insert a new user.
         *
         * @param array $user : User to insert
         *
         * @return mixed bool|int : false if fail, new user id else
         */
        public function insert($user)
        {
            $success = $this->_insert('user', $user);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Update a user using his is.
         *
         * @param int   $id   : User id
         * @param array $data : Data to update
         *
         * @return int : number of modified rows
         */
        public function update($id, $data)
        {
            return $this->_update('user', $data, ['id' => $id]);
        }

        /**
         * Update a user password by his id.
         *
         * @param int   $id       : User id
         * @param array $password : The new password of the user
         *
         * @return int : Number of modified lines
         */
        public function update_password($id, $password)
        {
            return $this->_update('user', ['password' => $password], ['id' => $id]);
        }

        /**
         * Update a user email by his id.
         *
         * @param int   $id    : User id
         * @param array $email : The new email
         *
         * @return int : Number of modified lines
         */
        public function update_email($id, $email)
        {
            return $this->_update('user', ['email' => $email], ['id' => $id]);
        }
    }
