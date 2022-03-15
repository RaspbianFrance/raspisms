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
     * Class to call the console scripts.
     */
    class Console extends \descartes\InternalController
    {
        /**
         * Start launcher daemon.
         */
        public function launcher()
        {
            new \daemons\Launcher();
        }

        /**
         * Start sender daemon.
         */
        public function sender()
        {
            new \daemons\Sender();
        }

        /**
         * Start webhook daemon.
         */
        public function webhook()
        {
            new \daemons\Webhook();
        }

        /**
         * Start mailer daemon.
         */
        public function mailer()
        {
            new \daemons\Mailer();
        }

        /**
         * Start a phone daemon.
         *
         * @param $id_phone : Phone id
         */
        public function phone($id_phone)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_phone = new \controllers\internals\Phone($bdd);

            $phone = $internal_phone->get($id_phone);
            if (!$phone)
            {
                exit(1);
            }

            new \daemons\Phone($phone);
        }

        /**
         * Check if a user exists based on email.
         *
         * @param string $email : User email
         */
        public function user_exists(string $email)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_user = new \controllers\internals\User($bdd);

            $user = $internal_user->get_by_email($email);

            exit($user ? 0 : 1);
        }

        /**
         * Check if a user exists based on id.
         *
         * @param string $id : User id
         */
        public function user_id_exists(string $id)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_user = new \controllers\internals\User($bdd);

            $user = $internal_user->get($id);

            exit($user ? 0 : 1);
        }

        /**
         * Create a user or update an existing user.
         *
         * @param $email : User email
         * @param $password : User password
         * @param $admin : Is user admin
         * @param $api_key : User API key, if null random api key is generated
         * @param $status : User status, default \models\User::STATUS_ACTIVE
         * @param bool $encrypt_password : Should the password be encrypted, by default true
         *
         * exit code 0 on success | 1 on error
         */
        public function create_update_user(string $email, string $password, bool $admin, ?string $api_key = null, string $status = \models\User::STATUS_ACTIVE, bool $encrypt_password = true)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_user = new \controllers\internals\User($bdd);

            $user = $internal_user->get_by_email($email);
            if ($user)
            {
                $api_key = $api_key ?? $internal_user->generate_random_api_key();
                $update_datas = [
                    'email' => $email,
                    'password' => $encrypt_password ? password_hash($password, PASSWORD_DEFAULT) : $password,
                    'admin' => $admin,
                    'api_key' => $api_key,
                    'status' => $status,
                ];

                $success = $internal_user->update($user['id'], $update_datas);
                echo json_encode(['id' => $user['id']]);

                exit($success ? 0 : 1);
            }

            $new_user_id = $internal_user->create($email, $password, $admin, $api_key, $status, $encrypt_password);
            echo json_encode(['id' => $new_user_id]);

            exit($new_user_id ? 0 : 1);
        }

        /**
         * Update a user status.
         *
         * @param string $id     : User id
         * @param string $status : User status, default \models\User::STATUS_ACTIVE
         */
        public function update_user_status(string $id, string $status)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_user = new \controllers\internals\User($bdd);

            $user = $internal_user->get($id);
            if (!$user)
            {
                exit(1);
            }

            $success = $internal_user->update_status($user['id'], $status);

            exit($success ? 0 : 1);
        }

        /**
         * Delete a user.
         *
         * @param string $id : User id
         */
        public function delete_user(string $id)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_user = new \controllers\internals\User($bdd);

            $success = $internal_user->delete($id);

            exit($success ? 0 : 1);
        }

        /**
         * Delete medias that are no longer usefull.
         */
        public function clean_unused_medias()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_media = new \controllers\internals\Media($bdd);

            $medias = $internal_media->gets_unused();

            foreach ($medias as $media)
            {
                $success = $internal_media->delete_for_user($media['id_user'], $media['id']);

                echo (false === $success ? '[KO]' : '[OK]') . ' - ' . $media['path'] . "\n";
            }
        }

        /**
         * Do alerting for quota limits.
         */
        public function quota_limit_alerting()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_quota = new \controllers\internals\Quota($bdd);
            $internal_quota->alerting_for_limit_close_and_reached();
        }

        /**
         * Do quota renewal.
         */
        public function renew_quotas()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_quota = new \controllers\internals\Quota($bdd);
            $internal_quota->renew_quotas();
        }
    }
