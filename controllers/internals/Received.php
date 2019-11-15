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

    class Received extends StandardController
    {
        protected $model = null;

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        protected function get_model () : \descartes\Model
        {
            $this->model = $this->model ?? new \models\Received($this->bdd);
            return $this->model;
        } 

        /**
         * Create a received
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $origin : Number of the sender
         * @param string $destination : Number of the receiver
         * @param bool $command : Is the sms a command
         * @return bool : false on error, new received id else
         */
        public function create ($at, string $text, string $origin, string $destination, bool $command = false) : bool
        {
            $received = [ 
                'at' => $at,
                'text' => $text, 
                'origin' => $origin,
                'destination' => $destination,
                'command' => $command,
            ];

            return (bool) $this->get_model()->insert($received);
        }


        /**
         * Update a received for a user
         * @param int $id_user : user id
         * @param int $id_received : received id
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $origin : Number of the sender
         * @param string $destination : Number of the receiver
         * @param bool $command : Is the sms a command
         * @return bool : false on error, true on success
         */
        public function update_for_user (int $id_user, int $id_received, $at, string $text, string $origin, string $destination, bool $command = false) : bool
        {
            $received = [ 
                'at' => $at,
                'text' => $text, 
                'origin' => $origin,
                'destination' => $destination,
                'command' => $command,
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $id_received, $received);
        }


        /**
         * Return x last receiveds message for a user, order by date
         * @param int $id_user : User id
         * @param int $nb_entry : Number of receiveds messages to return
         * @return array 
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->get_model()->get_lasts_by_date_for_user($id_user, $nb_entry);
        }


        /**
         * Return receiveds for an origin and a user
         * @param int $id_user : User id
         * @param string $origin : Number who sent the message
         * @return array
         */
        public function gets_by_origin_and_user(int $id_user, string $origin)
        {
            return $this->get_model()->gets_by_origin_and_user($id_user, $origin);
        }


        /**
         * Get number of sended SMS for every date since a date for a specific user
         * @param int $id_user : user id
         * @param \DateTime $date : Date since which we want the messages
         * @return array
         */
        public function count_by_day_since_for_user(int $id_user, $date)
        {
            $counts_by_day = $this->get_model()->count_by_day_since_for_user($id_user, $date);
            $return = [];

            foreach ($counts_by_day as $count_by_day)
            {
                $return[$count_by_day['at_ymd']] = $count_by_day['nb'];
            }

            return $return;
        }


        /**
         * Return all discussions (ie : numbers we have a message received from or sended to) for a user
         * @param int $id_user : User id
         * @return array
         */
        public function get_discussions_for_user(int $id_user)
        {
            return $this->get_model()->get_discussions_for_user($id_user);
        }


        /**
         * Get SMS received since a date for a user
         * @param int $id_user : User id
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date_for_user(int $id_user, $date)
        {
            return $this->get_model()->get_since_by_date_for_user($id_user, $date);
        }


        /**
         * Find messages received since a date for a certain origin and user
         * @param int $id_user : User id
         * @param $date : Date we want messages sinces
         * @param string $origin : Origin number
         * @return array
         */
        public function get_since_by_date_for_origin_and_user(int $id_user, $date, string $origin)
        {
            return $this->get_model()->get_since_by_date_for_origin_and_user($id_user, $date, $origin);
        }
    }
