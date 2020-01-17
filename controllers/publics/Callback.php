<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    /**
     * Controller of callback pages, like sms status update notification.
     */
    class Callback extends \descartes\Controller
    {
        private $internal_user;
        private $internal_sended;
        private $internal_adapter;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_user = new \controllers\internals\User($bdd);
            $this->internal_sended = new \controllers\internals\Sended($bdd);
            $this->internal_adapter = new \controllers\internals\Adapter();
        }

        /**
         * Function call on a sended sms status change notification reception.
         *
         * @param string $adapter_name : Name of the adapter to use
         *
         * @return false : We must always return false, and we respect a random usleep before returning anything
         *               in order to prevent bruteforce api key guessing and time guessing
         */
        public function update_sended_status(string $adapter_name)
        {
            //Wait between 0.5 and 1.03s in order to counter time guessing bruteforce attack against api key
            usleep(mt_rand(5, 10) / 10 * 1000000 + mt_rand(0, 30000));

            //Search for an adapter
            $find_adapter = false;
            $adapters = $this->internal_adapter->list_adapters();
            foreach ($adapters as $adapter)
            {
                if (mb_strtolower($adapter['meta_name']) === mb_strtolower($adapter_name))
                {
                    $find_adapter = $adapter;
                }
            }

            if (false === $find_adapter)
            {
                return false;
            }

            //Instanciate adapter, check if status change is supported and if so call status change callback
            $adapter_classname = $find_adapter['meta_classname'];

            if (!$find_adapter['meta_support_status_change'])
            {
                return false;
            }

            $callback_return = $adapter_classname::status_change_callback();

            var_dump($callback_return);

            if (!$callback_return)
            {
                return false;
            }

            $sended = $this->internal_sended->get_by_uid_and_adapter($callback_return['uid'], $adapter_classname);
            if (!$sended)
            {
                return false;
            }

            $this->internal_sended->update_status($sended['id'], $callback_return['status']);

            return false;
        }
    }
