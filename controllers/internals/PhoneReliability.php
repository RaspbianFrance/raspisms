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

use Exception;

    class PhoneReliability extends StandardController
    {
        protected $model;

        /**
         * Create a phone reliability history entry.
         *
         * @param int $id_user  : Id of user to create sended message for
         * @param int $id_phone : Id of the number the message was send with
         * @param $type : Type of reliability alert
         * @return mixed : false on error, new sended id else
         */
        public function create(int $id_user, int $id_phone, string $type)
        {
            return $this->get_model()->insert([
                'id_user' => $id_user,
                'id_phone' => $id_phone,
                'type' => $type,
            ]);

            return $id_sended;
        }

        /**
         * Find all unreliable phones for a user, based on sended sms status, rate limit, etc.
         * 
         * @param int $id_user : User id
         * @param string $sms_status : Status of SMS to use to calculate rate
         * @param float $rate_limit : Percentage of SMS matching status after which we consider the phone unreliable         
         * @param int $min_volume : Minimum number of sms we need to have to consider the statistic relevent
         * @param int $period : The time span in minutes from which SMS counting should begin.
         * @param int $grace_period : How long in minutes should we wait before including a SMS in counting
         * 
         * @return array : A list of unreliable phone for the user, with phone id, total number of sms, and rate of failed sms
         */
        public function find_unreliable_phones (int $id_user, string $sms_status, float $rate_limit, int $min_volume, int $period, int $grace_period)
        {
            return $this->get_model()->find_unreliable_phones($id_user, $sms_status, $rate_limit, $min_volume, $period, $grace_period);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\PhoneReliabilityHistory
        {
            $this->model = $this->model ?? new \models\PhoneReliabilityHistory($this->bdd);

            return $this->model;
        }
    }
