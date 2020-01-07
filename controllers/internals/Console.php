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
     * Class to call the console scripts 
     */
    class Console extends \descartes\InternalController
    {
        /**
         * Start manager daemon
         */
        public function manager ()
        {
            new \daemons\Manager();
        }


        /**
         * Start sender daemon
         */
        public function sender ()
        {
            new \daemons\Sender();
        }
        
        
        /**
         * Start webhook daemon
         */
        public function webhook ()
        {
            new \daemons\Webhook();
        }


        /**
         * Start a phone daemon
         * @param $id_phone : Phone id
         */
        public function phone ($id_phone)
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
    }
