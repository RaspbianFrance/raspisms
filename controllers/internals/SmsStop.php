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

    class SmsStop extends StandardController
    {
        protected $model;

        /**
         * Create a new smsstop.
         *
         * @param int    $id_user : User id
         * @param string $number  : Number to stop smss for
         *
         * @return mixed bool|int : False if cannot create smsstop, id of the new smsstop else
         */
        public function create(int $id_user, string $number)
        {
            $smsstop = [
                'id_user' => $id_user,
                'number' => $number,
            ];

            return $this->get_model()->insert($smsstop);
        }

        /**
         * Update a smsstop.
         *
         * @param int    $id_user    : User id
         * @param int    $id_smsstop : SmsStop id
         * @param string $number     : Number to stop smss for
         *
         * @return mixed bool|int : False if cannot create smsstop, id of the new smsstop else
         */
        public function update_for_user(int $id_user, int $id_smsstop, string $number)
        {
            $data = [
                'number' => $number,
            ];

            return $this->get_model()->update_for_user($id_user, $id_smsstop, $data);
        }

        /**
         * Return a smsstop by his number and user.
         *
         * @param int    $id_user : user id
         * @param string $number  :  phone number
         *
         * @return array
         */
        public function get_by_number_for_user(int $id_user, string $number)
        {
            return $this->get_model()->get_by_number_for_user($id_user, $number);
        }

        /**
         * Parse a string to check if its a SMS stop.
         *
         * @param string $str : The string to check
         *
         * @return bool : true if sms stop, false else
         */
        public function check_for_stop(string $str)
        {
            return 'stop' == trim(mb_strtolower($str));
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\SmsStop
        {
            $this->model = $this->model ?? new \models\SmsStop($this->bdd);

            return $this->model;
        }
    }
