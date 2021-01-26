<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace adapters;

    /**
     * Interface for phones adapters
     * Phone's adapters allow RaspiSMS to use a platform to communicate with a phone number.
     * Its an adapter between internal and external code, as an API, command line software, physical modem, etc.
     *
     * All Phone Adapters must implement this interface
     */
    class TestAdapter implements AdapterInterface
    {
        /**
         * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $data;

        /**
         * Path for the file to read sms as a json from.
         */
        private $test_file_read = PWD_DATA . '/test_read_sms.json';

        /**
         * Path for the file to write sms as a json in.
         */
        private $test_file_write = PWD_DATA . '/test_write_sms.json';

        /**
         * Adapter constructor, called when instanciated by RaspiSMS.
         *
         * @param json string $data : JSON string of the data to configure interaction with the implemented service
         */
        public function __construct(string $data)
        {
            $this->data = $data;
        }

        /**
         * Classname of the adapter.
         */
        public static function meta_classname(): string
        {
            return __CLASS__;
        }

        /**
         * Uniq name of the adapter
         * It should be the classname of the adapter un snakecase.
         */
        public static function meta_uid(): string
        {
            return 'test_adapter';
        }
        
        /**
         * Should this adapter be hidden in user interface for phone creation and
         * available to creation through API only
         */
        public static function meta_hidden(): bool
        {
            return false;
        }

        /**
         * Name of the adapter.
         * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.).
         */
        public static function meta_name(): string
        {
            return 'Test';
        }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description(): string
        {
            return 'A test adaptater that do not actually send or receive any message.';
        }

        /**
         * List of entries we want in data for the adapter.
         *
         * @return array : Eachline line is a field as an array with keys : name, title, description, required
         */
        public static function meta_data_fields(): array
        {
            return [];
        }

        /**
         * Does the implemented service support reading smss.
         */
        public static function meta_support_read(): bool
        {
            return true;
        }

        /**
         * Does the implemented service support flash smss.
         */
        public static function meta_support_flash(): bool
        {
            return true;
        }

        /**
         * Does the implemented service support status change.
         */
        public static function meta_support_status_change(): bool
        {
            return false;
        }

        /**
         * Does the implemented service support reception callback.
         */
        public static function meta_support_reception(): bool
        {
            return false;
        }

        /**
         * Method called to send a SMS to a number.
         *
         * @param string $destination : Phone number to send the sms to
         * @param string $text        : Text of the SMS to send
         * @param bool   $flash       : Is the SMS a Flash SMS
         *
         * @return array : [
         *               bool 'error' => false if no error, true else
         *               ?string 'error_message' => null if no error, else error message
         *               array 'uid' => Uid of the sms created on success
         *               ]
         */
        public function send(string $destination, string $text, bool $flash = false)
        {
            $response = [
                'error' => false,
                'error_message' => null,
                'uid' => null,
            ];

            $uid = uniqid();

            $at = (new \DateTime())->format('Y-m-d H:i:s');
            $success = file_put_contents($this->test_file_write, json_encode(['uid' => $uid, 'at' => $at, 'destination' => $destination, 'text' => $text, 'flash' => $flash]) . "\n", FILE_APPEND);
            if (false === $success)
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot write in file : ' . $this->test_file_write;

                return $response;
            }

            $response['uid'] = $uid;

            return $response;
        }

        /**
         * Method called to read SMSs of the number.
         *
         * @return array : [
         *               bool 'error' => false if no error, true else
         *               ?string 'error_message' => null if no error, else error message
         *               array 'sms' => Array of the sms reads
         *               ]
         */
        public function read(): array
        {
            $response = [
                'error' => false,
                'error_message' => null,
                'smss' => [],
            ];

            try
            {
                $file_contents = file_get_contents($this->test_file_read);
                if (false === $file_contents)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'Cannot read file : ' . $this->test_file_read;

                    return $response;
                }

                //Empty file to avoid dual read
                $success = file_put_contents($this->test_file_read, '');
                if (false === $success)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'Cannot write in file : ' . $this->test_file_read;

                    return $response;
                }

                $smss = explode("\n", $file_contents);

                foreach ($smss as $key => $sms)
                {
                    $decode_sms = json_decode($sms, true);
                    if (null === $decode_sms)
                    {
                        continue;
                    }

                    $response['smss'][] = $decode_sms;
                }

                return $response;
            }
            catch (\Throwable $t)
            {
                $response['error'] = true;
                $response['error_message'] = $t->getMessage();

                return $response;
            }
        }

        /**
         * Method called to verify if the adapter is working correctly
         * should be use for exemple to verify that credentials and number are both valid.
         *
         * @return bool : False on error, true else
         */
        public function test(): bool
        {
            return true;
        }

        /**
         * Method called on reception of a status update notification for a SMS.
         */
        public static function status_change_callback()
        {
            $uid = $_GET['uid'] ?? false;
            $status = $_GET['status'] ?? false;

            if (!$uid || !$status)
            {
                return false;
            }

            $return = [
                'uid' => $uid,
                'status' => \models\Sended::STATUS_UNKNOWN,
            ];

            switch ($status)
            {
                case \models\Sended::STATUS_DELIVERED:
                    $return['status'] = \models\Sended::STATUS_DELIVERED;

                    break;

                case \models\Sended::STATUS_FAILED:
                    $return['status'] = \models\Sended::STATUS_FAILED;

                    break;

                default:
                    $return['status'] = \models\Sended::STATUS_UNKNOWN;

                    break;
            }

            return $return;
        }

        /**
         * Method called on reception of a sms notification.
         *
         * @return array : [
         *               bool 'error' => false on success, true on error
         *               ?string 'error_message' => null on success, error message else
         *               array 'sms' => array [
         *               string 'at' : Recepetion date format Y-m-d H:i:s,
         *               string 'text' : SMS body,
         *               string 'origin' : SMS sender,
         *               ]
         *
         * ]
         */
        public static function reception_callback(): array
        {
            return [];
        }
    }
