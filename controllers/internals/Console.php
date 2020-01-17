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
         * Cette fonction retourne la fenetre de connexion.
         *
         * @param mixed $id_phone
         */
        public function test($id_phone)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
            $internal_phone = new \controllers\internals\Phone($bdd);
            $phone = $internal_phone->get($id_phone);
            if (!$phone)
            {
                echo "No phone for id : {$id_phone}\n";

                return false;
            }

            echo "Found phone for id : {$id_phone}\n";

            $adapter_classname = $phone['adapter'];
            $adapter = new $adapter_classname($phone['number'], $phone['adapter_datas']);

            //Try send a message
            /*
            $destination = '+33669529042';
            $text = "Coucou c'est pour un test !";
            $flash = false;
            $uid = $adapter->send($destination, $text, $flash);

            if (!$uid)
            {
                echo "Cannot send message to $destination\n";
                return false;
            }

            echo "Send a message to $destination with uid $uid \n";
            */
            $smss = $adapter->read();
            var_dump($smss);
        }
    }
