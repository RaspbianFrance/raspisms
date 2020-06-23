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
        protected $model;

        /**
         * Create a sended.
         *
         * @param int $id_user  : Id of user to create sended message for
         * @param int $id_phone : Id of the number the message was send with
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $destination : Number of the receiver
         * @param string $uid         : Uid of the sms on the adapter service used
         * @param string $adapter     : Name of the adapter service used to send the message
         * @param bool   $flash       : Is the sms a flash
         * @param string $status      : Status of a the sms. By default \models\Sended::STATUS_UNKNOWN
         *
         * @return mixed : false on error, new sended id else
         */
        public function create(int $id_user, int $id_phone, $at, string $text, string $destination, string $uid, string $adapter, bool $flash = false, ?string $status = \models\Sended::STATUS_UNKNOWN)
        {
            $sended = [
                'id_user' => $id_user,
                'id_phone' => $id_phone,
                'at' => $at,
                'text' => $text,
                'destination' => $destination,
                'uid' => $uid,
                'adapter' => $adapter,
                'flash' => $flash,
                'status' => $status,
            ];

            return $this->get_model()->insert($sended);
        }

        /**
         * Update a sended status for a user.
         *
         * @param int    $id_user   : user id
         * @param int    $id_sended : Sended id
         * @param string $status    : Status of a the sms (unknown, delivered, failed)
         *
         * @return bool : false on error, true on success
         */
        public function update_status_for_user(int $id_user, int $id_sended, string $status): bool
        {
            $sended = [
                'status' => $status,
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $id_sended, $sended);
        }

        /**
         * Update a sended status for a sended.
         *
         * @param int    $id_sended : Sended id
         * @param string $status    : Status of a the sms (unknown, delivered, failed)
         *
         * @return bool : false on error, true on success
         */
        public function update_status(int $id_sended, string $status): bool
        {
            $sended = [
                'status' => $status,
            ];

            return (bool) $this->get_model()->update($id_sended, $sended);
        }

        /**
         * Return x last sendeds message for a user, order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of sendeds messages to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->get_model()->get_lasts_by_date_for_user($id_user, $nb_entry);
        }

        /**
         * Return sendeds for a destination and a user.
         *
         * @param int    $id_user : User id
         * @param string $origin  : Number who sent the message
         *
         * @return array
         */
        public function gets_by_destination_and_user(int $id_user, string $origin)
        {
            return $this->get_model()->gets_by_destination_and_user($id_user, $origin);
        }

        /**
         * Return sended for an uid and an adapter.
         *
         * @param int    $id_user : user id
         * @param string $uid     : Uid of the sended
         * @param string $adapter : Adapter used to send the message
         *
         * @return array
         */
        public function get_by_uid_and_adapter_for_user(int $id_user, string $uid, string $adapter)
        {
            return $this->get_model()->get_by_uid_and_adapter_for_user($id_user, $uid, $adapter);
        }

        /**
         * Get number of sended SMS for every date since a date for a specific user.
         *
         * @param int       $id_user : user id
         * @param \DateTime $date    : Date since which we want the messages
         *
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
         * Find last sended message for a destination and user.
         *
         * @param int    $id_user     : User id
         * @param string $destination : Destination number
         *
         * @return array
         */
        public function get_last_for_destination_and_user(int $id_user, string $destination)
        {
            return $this->get_model()->get_last_for_destination_and_user($id_user, $destination);
        }

        /**
         * Send a SMS message.
         *
         * @param \adapters\AdapterInterface $adapter  : Adapter object to use to send the message
         * @param int                        $id_user  : Id of user to create sended message for
         * @param int                        $id_phone : Id of the phone the message was send with
         * @param $text : Text of the message
         * @param string $destination : Number of the receiver
         * @param bool   $flash       : Is the sms a flash. By default false.
         * @param string $status      : Status of a the sms. By default \models\Sended::STATUS_UNKNOWN
         *
         * @return array : [
         *               bool 'error' => false if success, true else
         *               ?string 'error_message' => null if success, error message else
         *               ]
         */
        public function send(\adapters\AdapterInterface $adapter, int $id_user, int $id_phone, string $text, string $destination, bool $flash = false, string $status = \models\Sended::STATUS_UNKNOWN): array
        {
            $return = [
                'error' => false,
                'error_message' => null,
            ];

            $at = (new \DateTime())->format('Y-m-d H:i:s');
            $response = $adapter->send($destination, $text, $flash);

            if ($response['error'])
            {
                $return['error'] = true;
                $return['error_message'] = $response['error_message'];
                $status = \models\Sended::STATUS_FAILED;
                $this->create($id_user, $id_phone, $at, $text, $destination, $response['uid'] ?? uniqid(), $adapter->meta_classname(), $flash, $status);

                return $return;
            }

            $sended_id = $this->create($id_user, $id_phone, $at, $text, $destination, $response['uid'] ?? uniqid(), $adapter->meta_classname(), $flash, $status);

            $sended = [
                'id' => $sended_id,
                'at' => $at,
                'text' => $text,
                'destination' => $destination,
                'origin' => $id_phone,
            ];

            $internal_webhook = new Webhook($this->bdd);
            $internal_webhook->trigger($id_user, \models\Webhook::TYPE_SEND, $sended);

            return $return;
        }

        /**
         * Get the model for the Controller.
         *
         * @return \descartes\Model
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Sended($this->bdd);

            return $this->model;
        }
    }
