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
     * Statistics pages
     */
    class Stat extends \descartes\Controller
    {
        private $internal_sended;
        private $internal_phone;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_sended = new \controllers\internals\Sended($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Show the stats about sms status for a period by phone
         *
         * @return void;
         */
        public function sms_status()
        {
            $id_user = $_SESSION['user']['id'];
            $phones = $this->internal_phone->gets_for_user($id_user);
            
            $now = new \DateTime();
            $seven_days_interval = new \DateInterval('P7D');
            $seven_days_ago = clone($now);
            $seven_days_ago->sub($seven_days_interval);

            $this->render('stat/sms-status', [
                'phones' => $phones,
                'now' => $now,
                'seven_days_ago' => $seven_days_ago,
            ]);
        }
    }
