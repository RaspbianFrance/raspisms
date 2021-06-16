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
        private $bdd;
        private $model_user;
        private $internal_event;
        private $internal_setting;
        private $internal_phone;

        public function __construct(\PDO $bdd)
        {
            $this->bdd = $bdd;
            $this->model_user = new \models\User($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
            $this->internal_setting = new \controllers\internals\Setting($bdd);
            $this->internal_phone = new Phone($bdd);
        }

        /**
         * Return a list of users by their ids.
         *
         * @param array $ids : ids of entries to find
         *
         * @return array
         */
        public function gets_in_by_id(array $ids)
        {
            return $this->model_user->gets_in_by_id($ids);
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
         * Update user status.
         *
         * @param string $id     : user id
         * @param string $status : new status
         *
         * @return boolean;
         */
        public function update_status($id, $status)
        {
            return (bool) $this->model_user->update($id, ['status' => $status]);
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
         * @param mixed               $id    : User id
         * @param array               $user  : Array of fields to update for user
         * @param mixed (?array|bool) $quota : Quota to update for the user, by default null -> no update, if false, remove quota
         *
         * @return bool : True on success, false on error
         */
        public function update($id, array $user, $quota = null)
        {
            $internal_quota = new Quota($this->bdd);
            $current_quota = $internal_quota->get_user_quota($id);

            $this->bdd->beginTransaction();

            $this->model_user->update($id, $user);

            if ($current_quota && false === $quota)
            {
                $success = $internal_quota->delete_for_user($id, $current_quota['id']);
                if (!$success)
                {
                    $this->bdd->rollback();

                    return false;
                }
            }

            if ($quota)
            {
                if ($current_quota)
                {
                    $internal_quota->update_for_user($id, $current_quota['id'], $quota);
                }
                else
                {
                    $success = $internal_quota->create($id, $quota['credit'], $quota['additional'], $quota['report_unused'], $quota['report_unused_additional'], $quota['auto_renew'], $quota['renew_interval'], new \DateTime($quota['start_date']), new \DateTime($quota['expiration_date']));
                    if (!$success)
                    {
                        $this->bdd->rollback();

                        return false;
                    }
                }
            }

            if (!$this->bdd->commit())
            {
                return false;
            }

            return true;
        }

        /**
         * Create a new user.
         *
         * @param mixed   $email
         * @param mixed   $password
         * @param mixed   $admin
         * @param ?string $api_key          : The api key of the user, if null generate randomly
         * @param string  $status           : User status, default \models\User::STATUS_ACTIVE
         * @param bool    $encrypt_password : Should the password be encrypted, by default true
         * @param ?array  $quota            : Quota to create for the user, by default null -> no quota
         *
         * @return mixed bool|int : false on error, id of the new user else
         */
        public function create($email, $password, $admin, ?string $api_key = null, string $status = \models\User::STATUS_ACTIVE, bool $encrypt_password = true, ?array $quota = null)
        {
            $user = [
                'email' => $email,
                'password' => $encrypt_password ? password_hash($password, PASSWORD_DEFAULT) : $password,
                'admin' => $admin,
                'api_key' => $api_key ?? $this->generate_random_api_key(),
                'status' => $status,
            ];

            $this->bdd->beginTransaction();

            $new_id_user = $this->model_user->insert($user);
            if (!$new_id_user)
            {
                return false;
            }

            $success = $this->internal_setting->create_defaults_for_user($new_id_user);
            if (!$success)
            {
                $this->bdd->rollback();

                return false;
            }

            if (null !== $quota)
            {
                $internal_quota = new Quota($this->bdd);
                $success = $internal_quota->create($new_id_user, $quota['credit'], $quota['additional'], $quota['report_unused'], $quota['report_unused_additional'], $quota['auto_renew'], $quota['renew_interval'], $quota['start_date'], $quota['expiration_date']);
                if (!$success)
                {
                    $this->bdd->rollback();

                    return false;
                }
            }

            if (!$this->bdd->commit())
            {
                return false;
            }

            return $new_id_user;
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

        /**
         * Transfer a received sms to user email.
         *
         * @param int   $id_user  : User id
         * @param array $received : [
         *                        int 'id' => sms id,
         *                        string 'at' => sms reception date,
         *                        string 'text' => sms content,
         *                        string 'destination' => id of phone the sms was sent to
         *                        string 'origin' => phone number that sent the sms
         *                        bool 'mms' => is the sms a mms
         *                        ]
         *
         * @return bool : False if no transfer, true else
         */
        public function transfer_received(int $id_user, array $received): bool
        {
            $settings = $this->internal_setting->gets_for_user($id_user);

            if (!$settings['transfer'] ?? false)
            {
                return false;
            }

            $user = $this->get($id_user);
            if (!$user)
            {
                return false;
            }

            $phone = $this->internal_phone->get_for_user($id_user, $received['destination']);
            if (!$phone)
            {
                return false;
            }

            $mailer = new Mailer();

            $attachments = [];

            foreach ($received['medias'] ?? [] as $media)
            {
                $attachments[] = PWD_DATA_PUBLIC . '/' . $media['path'];
            }

            return $mailer->enqueue($user['email'], EMAIL_TRANSFER_SMS, [
                'at' => $received['at'],
                'origin' => $received['origin'],
                'destination' => $phone['name'],
                'text' => $received['text'],
                'mms' => $received['mms'] ?? false,
            ], $attachments);
        }
    }
