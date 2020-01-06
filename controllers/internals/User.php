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
     * Methods to manage user. Not a standard controller as it has nothing to do with user based restrictions and must be usable only by admin
     */
    class User extends \descartes\InternalController
    {
        private $model_user;
        private $internal_event;

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
         * Check user credentials
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
         * Update a user transfer property value.
         *
         * @param string $id       : User id
         * @param string $transfer : New value of property transfer
         *
         * @return boolean;
         */
        public function update_transfer(int $id, int $transfer): bool
        {
            return (bool) $this->model_user->update_transfer($id, $transfer);
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
         * Get a user by his email address
         * @param string $email : User email
         *
         * @return mixed boolean | array : false if cannot find user for this email, the user else
         */
        public function get_by_email($email)
        {
            return $this->model_user->get_by_email($email);
        }

        /**
         * Return users by transfer status.
         *
         * @param bool $transfer : transfer status
         */
        public function gets_by_transfer($transfer)
        {
            return $this->model_user->get_by_transfer($transfer);
        }

        /**
         * Update a user by his id
         * @param mixed $id
         * @param mixed $email
         * @param mixed $password
         * @param mixed $admin
         * @param mixed $transfer
         *
         * @return int : Number of modified user
         */
        public function update($id, $email, $password, $admin, $transfer)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'transfer' => $transfer,
            ];

            return $this->model_user->update($id, $user);
        }

        /**
         * Create a new user
         *
         * @param mixed $email
         * @param mixed $password
         * @param mixed $admin
         * @param mixed $transfer
         *
         * @return mixed bool|int : false on error, id of the new user else
         */
        public function create($email, $password, $admin, $transfer = false)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'transfer' => $transfer,
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
    }
