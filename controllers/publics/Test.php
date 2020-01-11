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
     * Page de connexion.
     */
    class Connect extends \descartes\Controller
    {
        private $internal_user;
        private $internal_setting;

        /**
         * Cette fonction est appelée avant toute les autres :.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_user = new \controllers\internals\User($bdd);
            $this->internal_setting = new \controllers\internals\Setting($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);
        }

        /**
         * Cette fonction retourne la fenetre de connexion.
         */
        public function test($id_phone)
        {
            $phone = $this->internal_phone->get($id_phone);
            if (!$phone)
            {
                echo "No phone for id : $id_phone\n";
                return false;
            }

            echo "Found phone for id : $id_phone\n";

            $adapter_classname = $phone['adapter'];
            $adapter = new $adapter_classname($phone['number'], $phone['adapter_datas']);
            
            //Try send a message
            $destination = '+33669529042';
            $text = "Coucou c'est pour un test !";
            $flash = false;
            $uid = $adapter->send($destination, $text, $flash);
            
            if (!$uid)
            {
                echo "Cannot send message to $destination\n";
            }

            echo "Send a message to $destination with uid $uid \n";
        }
    }

