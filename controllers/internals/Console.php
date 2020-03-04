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
         * Start a phone daemon.
         *
         * @param $id_phone : Phone id
         */
        public function phone($id_phone)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
            $internal_phone = new \controllers\internals\Phone($bdd);

            $phone = $internal_phone->get($id_phone);
            if (!$phone)
            {
                return false;
            }

            new \daemons\Phone($phone);
        }

        /**
         * Create a user or update an existing user.
         *
         * @param $email : User email
         * @param $password : User password
         * @param $admin : Is user admin
         * @param $api_key : User API key, if null random api key is generated
         */
        public function create_update_user(string $email, string $password, bool $admin, ?string $api_key = null)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
            $internal_user = new \controllers\internals\User($bdd);

            $user = $internal_user->get_by_email($email);
            if ($user)
            {
                $api_key = $api_key ?? $internal_user->generate_random_api_key();
                $success = $internal_user->update($user['id'], $email, $password, $admin, $api_key);

                exit($success ? 0 : 1);
            }

            $success = $internal_user->create($email, $password, $admin, $api_key);
            exit($success ? 0 : 1);
        }
    }
