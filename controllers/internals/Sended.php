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

    class Sended extends StandardController
    {
        protected $model = false;

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        protected function get_model () : \descartes\Model
        {
            $this->model = $this->model ?? new \models\Sended($this->$bdd);
            return $this->model;
        } 

        /**
         * Create a sended
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $origin : Number of the sender
         * @param string $destination : Number of the receiver
         * @param bool $flash : Is the sms a flash
         * @param ?string $status : Status of a the sms. By default null -> unknown
         * @return bool : false on error, new sended id else
         */
        public function create ($at, string $text, string $origin, string $destination, bool $flash = false, ?string $status = null) : bool
        {
            $sended = [ 
                'at' => $at,
                'text' => $text, 
                'origin' => $origin,
                'destination' => $destination,
                'flash' => $flash,
                'status' => $status,
            ];

            return (bool) $this->get_model()->insert($sended);
        }


        /**
         * Update a sended for a user
         * @param int $id_user : user id
         * @param int $id_sended : Sended id
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $origin : Number of the sender
         * @param string $destination : Number of the receiver
         * @param bool $flash : Is the sms a flash
         * @param ?string $status : Status of a the sms. By default null -> unknown
         * @return bool : false on error, true on success
         */
        public function update_for_user (int $id_user, int $id_sended, $at, string $text, string $origin, string $destination, bool $flash = false, ?string $status = null) : bool
        {
            $sended = [ 
                'at' => $at,
                'text' => $text, 
                'origin' => $origin,
                'destination' => $destination,
                'flash' => $flash,
                'status' => $status,
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $id_sended, $sended);
        }
        
        
        /**
         * Update a sended status for a user
         * @param int $id_user : user id
         * @param int $id_sended : Sended id
         * @param string $status : Status of a the sms (unknown, delivered, failed)
         * @return bool : false on error, true on success
         */
        public function update_status_for_user (int $id_user, int $id_sended, string $status) : bool
        {
            $sended = [ 
                'status' => $status,
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $id_sended, $sended);
        }


        /**
         * Return x last sendeds message for a user, order by date
         * @param int $id_user : User id
         * @param int $nb_entry : Number of sendeds messages to return
         * @return array 
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->get_model()->get_lasts_by_date_for_user($id_user, $nb_entry);
        }


        /**
         * Return sendeds for a destination and a user
         * @param int $id_user : User id
         * @param string $origin : Number who sent the message
         * @return array
         */
        public function gets_by_destination_for_user(int $id_user, string $origin)
        {
            return $this->get_model()->gets_by_destination_for_user($id_user, $origin);
        }


        /**
         * Get number of sended SMS for every date since a date for a specific user
         * @param int $id_user : user id
         * @param \DateTime $date : Date since which we want the messages
         * @return array
         */
        public function count_by_day_since_for_user(int $id_user, $date)
        {
            return $this->get_model()->count_by_day_since_for_user($id_user, $date);
        }
    }
