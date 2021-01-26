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
    class BenchmarkAdapter implements AdapterInterface
    {
        /**
         * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $data;

        /**
         * API URL.
         */
        private $api_url = 'https://jsonplaceholder.typicode.com/posts';

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
            return 'benchmark_adapter';
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
            return 'Benchmark';
        }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description(): string
        {
            return 'A benchmark adaptater that use https://jsonplaceholder.typicode.com to test speed of SMS sending.';
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
            return false;
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
         *               int 'uid' => Uid of the sms created on success
         *               ]
         */
        public function send(string $destination, string $text, bool $flash = false)
        {
            $response = [
                'error' => false,
                'error_message' => null,
                'uid' => null,
            ];

            try
            {
                $data = [
                    'sms_text' => $text,
                    'sms_destination' => $destination,
                    'sms_flash' => $flash,
                ];

                $endpoint = $this->api_url;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $endpoint);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                $curl_response = curl_exec($curl);
                curl_close($curl);

                if (false === $curl_response)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'HTTP query failed.';

                    return $response;
                }

                var_dump($curl_response);

                $response_decode = json_decode($curl_response, true);
                if (null === $response_decode)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'Invalid JSON for response.';

                    return $response;
                }

                $response['uid'] = uniqid();

                return $response;
            }
            catch (\Throwable $t)
            {
                $response['error'] = true;
                $response['error_message'] = $t->getMessage();

                return $response;
            }
        }

        public function read(): array
        {
            return [];
        }

        public static function status_change_callback()
        {
            return null;
        }

        public static function reception_callback(): array
        {
            return [];
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
    }
