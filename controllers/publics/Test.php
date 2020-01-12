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
    class Test extends \descartes\Controller
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

    }

