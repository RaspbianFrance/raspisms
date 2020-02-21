<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    /**
     * Methods to manage user. Not a standard controller as it has nothing to do with user based restrictions and must be usable only by admin.
     */
    class User extends \descartes\InternalController
    {
        private $model_user;
        private $internal_event;
        private $internal_setting;

        public function __construct(\PDO $bdd)
        {
            $this->model_user = new \models\User($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
            $this->internal_setting = new \controllers\internals\Setting($bdd);
        }

        /**
         * Return list of users as an array.
         *
         * @param mixed(int|bool) $nb_entry : Number of entry to return
         * @param mixed(int|bool) $page     : Numero of page
         *
         * @return array|bool : List of user or false
         */
        public function list(?int $nb_entry = null, ?int $page = null)
        {
            return $this->model_user->list($nb_entry, $page * $nb_entry);
        }

        /**
         * Delete a user.
         *
         * @param array $ids : Les id des useres à supprimer
         * @param mixed $id
         *
         * @return int : Number of users deleted
         */
        public function delete($id)
        {
            return $this->model_user->remove($id);
        }

        /**
         * Check user credentials.
         *
         * @param string $email    : User email
         * @param string $password : User password
         *
         * @return mixed false | array : False if no user for thoses credentials, the user else
         */
        public function check_credentials($email, $password)
        {
            $user = $this->model_user->get_by_email($email);
            if (!$user)
            {
                return false;
            }

            if (!password_verify($password, $user['password']))
            {
                return false;
            }

            return $user;
        }

        /**
         * Update a user password.
         *
         * @param string $id       : User id
         * @param string $password : New password
         *
         * @return bool;
         */
        public function update_password(int $id, string $password): bool
        {
            $password = password_hash($password, PASSWORD_DEFAULT);

            return (bool) $this->model_user->update_password($id, $password);
        }

        /**
         * Update user email.
         *
         * @param string $id    : user id
         * @param string $email : new mail
         *
         * @return boolean;
         */
        public function update_email($id, $email)
        {
            return (bool) $this->model_user->update_email($id, $email);
        }

        /**
         * Update user api key.
         *
         * @param string  $id      : user id
         * @param ?string $api_key : new api key
         *
         * @return mixed : false on error, else new api key;
         */
        public function update_api_key($id, ?string $api_key = null)
        {
            $api_key = $api_key ?? $this->generate_random_api_key();
            $success = $this->model_user->update($id, ['api_key' => $api_key]);

            if (!$success)
            {
                return false;
            }

            return $api_key;
        }

        /**
         * Get a user by his email address.
         *
         * @param string $email : User email
         *
         * @return mixed boolean | array : false if cannot find user for this email, the user else
         */
        public function get_by_email($email)
        {
            return $this->model_user->get_by_email($email);
        }

        /**
         * Find a user by his id.
         *
         * @param string $id : User id
         *
         * @return mixed array
         */
        public function get($id)
        {
            return $this->model_user->get($id);
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
            return $this->model_user->get_by_api_key($api_key);
        }

        /**
         * Update a user by his id.
         *
         * @param mixed $id
         * @param mixed $email
         * @param mixed $password
         * @param mixed $admin
         * @param mixed $api_key
         *
         * @return int : Number of modified user
         */
        public function update($id, $email, $password, $admin, $api_key)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'api_key' => $api_key,
            ];

            return $this->model_user->update($id, $user);
        }

        /**
         * Create a new user.
         *
         * @param mixed   $email
         * @param mixed   $password
         * @param mixed   $admin
         * @param ?string $api_key  : The api key of the user, if null generate randomly
         *
         * @return mixed bool|int : false on error, id of the new user else
         */
        public function create($email, $password, $admin, ?string $api_key = null)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'api_key' => $api_key ?? $this->generate_random_api_key(),
            ];

            $new_user_id = $this->model_user->insert($user);

            if (!$new_user_id)
            {
                return false;
            }

            $success = $this->internal_setting->create_defaults_for_user($new_user_id);

            if (!$success)
            {
                $this->delete($new_user_id);

                return false;
            }

            return $new_user_id;
        }

        /**
         * Generate a random api key.
         *
         * @return string : The api key
         */
        public function generate_random_api_key(): string
        {
            return bin2hex(random_bytes(16));
        }
    }
