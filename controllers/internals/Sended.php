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

    class Sended extends StandardController
    {
        protected $model;

        /**
         * @param int     $id_user        : User id
         * @param ?int    $limit          : Number of entry to return
         * @param ?int    $offset         : Number of entry to avoid
         * @param ?string $search         : String to search for
         * @param ?array  $search_columns : List of columns to search on
         * @param ?string $order_column   : Name of the column to order by
         * @param bool    $order_desc     : Should result be ordered DESC, if false order ASC
         * @param bool    $count          : Should the query only count results
         *
         * @return array : Entries list
         */
        public function datatable_list_for_user(int $id_user, ?int $limit = null, ?int $offset = null, ?string $search = null, ?array $search_columns = [], ?string $order_column = null, bool $order_desc = false, $count = false)
        {
            return $this->get_model()->datatable_list_for_user($id_user, $limit, $offset, $search, $search_columns, $order_column, $order_desc, $count);
        }

        /**
         * Create a sended.
         *
         * @param int $id_user  : Id of user to create sended message for
         * @param int $id_phone : Id of the number the message was send with
         * @param $at : Reception date
         * @param $text : Text of the message
         * @param string $destination           : Number of the receiver
         * @param string $uid                   : Uid of the sms on the adapter service used
         * @param string $adapter               : Name of the adapter service used to send the message
         * @param bool   $flash                 : Is the sms a flash
         * @param bool   $mms                   : Is the sms a MMS. By default false.
         * @param ?string $tag                   : A string tag to associate to sended SMS
         * @param array  $medias                : Array of medias to link to the MMS
         * @param ?int   $originating_scheduled : Id of the scheduled message that was responsible for sending this message. By default null.
         * @param string $status                : Status of a the sms. By default \models\Sended::STATUS_UNKNOWN
         *
         * @return mixed : false on error, new sended id else
         */
        public function create(int $id_user, int $id_phone, $at, string $text, string $destination, string $uid, string $adapter, bool $flash = false, bool $mms = false, ?string $tag = null, array $medias = [], ?int $originating_scheduled = null, ?string $status = \models\Sended::STATUS_UNKNOWN)
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
                'mms' => $mms,
                'tag' => $tag,
                'status' => $status,
                'originating_scheduled' => $originating_scheduled,
            ];

            //Ensure atomicity
            $this->bdd->beginTransaction();

            $id_sended = $this->get_model()->insert($sended);
            if (!$id_sended)
            {
                $this->bdd->rollback();

                return false;
            }

            //Link medias
            $internal_media = new Media($this->bdd);
            foreach ($medias as $media)
            {
                $internal_media->link_to($media['id'], 'sended', $id_sended); //No rollback on error, keeping track of mms is more important than integrity
            }

            if (!$this->bdd->commit())
            {
                return false;
            }

            return $id_sended;
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

            $success = (bool) $this->get_model()->update_for_user($id_user, $id_sended, $sended);

            if (!$success)
            {
                return $success;
            }

            $webhook = [
                'id' => $id_sended,
                'status' => $status,
            ];

            $internal_webhook = new Webhook($this->bdd);
            $internal_webhook->trigger($id_user, \models\Webhook::TYPE_SEND_SMS_STATUS_CHANGE, $webhook);

            return $success;
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
         * Return sendeds for a destination and a user since a date.
         *
         * @param int    $id_user : User id
         * @param string $since   : Date we want messages since format Y-m-d H:i:s
         * @param string $origin  : Number who sent the message
         *
         * @return array
         */
        public function gets_since_date_by_destination_and_user(int $id_user, string $since, string $origin)
        {
            return $this->get_model()->gets_since_date_by_destination_and_user($id_user, $since, $origin);
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
         * Get number of sended SMS since a date for a phone
         * 
         * @param int $id_user : User id
         * @param int $id_phone : Phone id we want the number of sended message for
         * @param ?\DateTime $since : Date since which we want sended number. Default to null.
         * @param ?\DateTime $before : Date up to which we want sended number. Default to null.
         * @param ?string $tag_like : Tag to filter sms by, this is not a = but a LIKE operator
         * 
         * @return int
         */
        public function count_since_for_phone_and_user(int $id_user, int $id_phone, ?\DateTime $since, ?\DateTime $before = null, ?string $tag_like = null): int
        {
            return $this->get_model()->count_since_for_phone_and_user($id_user, $id_phone, $since, $before, $tag_like);
        }

        /**
         * Get number of sended SMS for every date since a date for a specific user.
         *
         * @param int       $id_user : user id
         * @param \DateTime $date    : Date since which we want the messages
         *
         * @return array
         */
        public function count_by_day_and_status_since_for_user(int $id_user, $date)
        {
            return $this->get_model()->count_by_day_and_status_since_for_user($id_user, $date);
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
         * @param string     $destination           : Number of the receiver
         * @param bool       $flash                 : Is the sms a flash. By default false.
         * @param ?string    $tag                   : A string tag to associate to sended SMS
         * @param bool       $mms                   : Is the sms a MMS. By default false.
         * @param array      $medias                : Array of medias to link to the MMS
         * @param string     $status                : Status of a the sms. By default \models\Sended::STATUS_UNKNOWN
         * @param null|mixed $originating_scheduled
         *
         * @return array : [
         *               bool 'error' => false if success, true else
         *               ?string 'error_message' => null if success, error message else
         *               ]
         */
        public function send(\adapters\AdapterInterface $adapter, int $id_user, int $id_phone, string $text, string $destination, bool $flash = false, bool $mms = false, ?string $tag = null, array $medias = [], $originating_scheduled = null, string $status = \models\Sended::STATUS_UNKNOWN): array
        {
            $return = [
                'error' => false,
                'error_message' => null,
            ];

            $internal_setting = new Setting();
            $user_settings = $internal_setting->gets_for_user($id_user);

            $at = (new \DateTime())->format('Y-m-d H:i:s');
            $media_uris = [];
            foreach ($medias as $media)
            {
                $media_uris[] = [
                    'path' => $media['path'],
                    'local_uri' => PWD_DATA_PUBLIC . '/' . $media['path'],
                ];
            }

            //If adapter does not support mms and the message is a mms, add medias as link
            if (!$adapter::meta_support_mms_sending() && $mms)
            {
                $media_urls = [];
                foreach ($media_uris as $media_uri)
                {
                    $media_urls[] = STATIC_HTTP_URL . '/data/public/' . $media_uri['path'];
                }

                $text .= "\n" . join(' - ', $media_urls);
            }

            try
            {
                //If we reached our max quota, do not send the message
                $internal_quota = new Quota($this->bdd);
                $nb_credits = $internal_quota::compute_credits_for_message($text); //Calculate how much credit the message require
                if (!$internal_quota->has_enough_credit($id_user, $nb_credits))
                {
                    throw new Exception('Not enough credit to send message.');
                }

                // If this phone status indicate it is not available
                $internal_phone = new Phone($this->bdd);
                $phone = $internal_phone->get_for_user($id_user, $id_phone);
                if (!$phone || $phone['status'] != \models\Phone::STATUS_AVAILABLE)
                {
                    throw new Exception('Invalid phone status : ' . $phone['status']);
                }

                //If we reached limit for this phone and phone limits are enabled, do not send the message
                if ((int) ($user_settings['phone_limit'] ?? false))
                {
                    $limits = $internal_phone->get_limits($id_phone);

                    $remaining_volume = PHP_INT_MAX;
                    foreach ($limits as $limit)
                    {
                        $startpoint = new \DateTime($limit['startpoint']);
                        $consumed = $this->count_since_for_phone_and_user($id_user, $id_phone, $startpoint);
                        $remaining_volume = min(($limit['volume'] - $consumed), $remaining_volume);
                    }

                    if ($remaining_volume < 1)
                    {
                        throw new Exception('Phone send limit have been reached.');
                    }
                }

                $response = $adapter->send($destination, $text, $flash, $mms, $media_uris);

                if ($response['error'])
                {
                    throw new Exception($response['error_message']);
                }

                $uid = $response['uid'];
                $status = \models\Sended::STATUS_UNKNOWN;

                // If send with success, consume credit
                $internal_quota->consume_credit($id_user, $nb_credits);
            }
            catch (Exception $e)
            {
                $return['error'] = true;
                $return['error_message'] = $e->getMessage();

                $status = \models\Sended::STATUS_FAILED;
                
                return $return;
            }
            finally
            {
                $uid = $uid ?? uniqid();
                $sended_id = $this->create($id_user, $id_phone, $at, $text, $destination, $uid, $adapter->meta_classname(), $flash, $mms, $tag, $medias, $originating_scheduled, $status);
                
                $webhook_body = [
                    'id' => $sended_id,
                    'at' => $at,
                    'status' => $status,
                    'text' => $text,
                    'destination' => $destination,
                    'origin' => $id_phone,
                    'mms' => $mms,
                    'medias' => $medias,
                    'originating_scheduled' => $originating_scheduled,
                ];

                $internal_webhook = new Webhook($this->bdd);
                $internal_webhook->trigger($id_user, \models\Webhook::TYPE_SEND_SMS, $webhook_body);

                return $return;
            }
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\Sended
        {
            $this->model = $this->model ?? new \models\Sended($this->bdd);

            return $this->model;
        }
    }
