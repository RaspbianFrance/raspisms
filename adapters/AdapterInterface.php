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
    interface AdapterInterface
    {
        /**
         * Adapter constructor, called when instanciated by RaspiSMS.
         *
         * @param json string $data : JSON string of the data to configure interaction with the implemented service
         */
        public function __construct(string $data);

        /**
         * Classname of the adapter.
         */
        public static function meta_classname(): string;

        /**
         * Uniq name of the adapter
         * It should be the classname of the adapter un snakecase.
         */
        public static function meta_uid(): string;

        /**
        * Should this adapter be hidden in user interface for phone creation and
        * available to creation through API only
         */
        public static function meta_hidden(): bool;

        /**
         * Name of the adapter.
         * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.).
         */
        public static function meta_name(): string;

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description(): string;

        /**
         * List of entries we want in data for the adapter.
         *
         * @return array : Eachline line is a field as an array with keys : name, title, description, required
         */
        public static function meta_data_fields(): array;

        /**
         * Does the implemented service support flash smss.
         */
        public static function meta_support_flash(): bool;

        /**
         * Does the implemented service support reading smss.
         */
        public static function meta_support_read(): bool;

        /**
         * Does the implemented service support reception callback.
         */
        public static function meta_support_reception(): bool;

        /**
         * Does the implemented service support status change callback.
         */
        public static function meta_support_status_change(): bool;

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
         *               ?string 'uid' => Uid of the sms created on success
         *               ]
         */
        public function send(string $destination, string $text, bool $flash = false);

        /**
         * Method called to read SMSs of the number.
         *
         * @return array : [
         *               bool 'error' => false if no error, true else
         *               ?string 'error_message' => null if no error, else error message
         *               array 'smss' => Array of the sms reads
         *               [
         *               [
         *               string 'at' => sms reception date,
         *               string 'text' => sms text,
         *               string 'origin' => phone number who sent the sms
         *               ],
         *               ...
         *               ]
         *               ]
         */
        public function read(): array;

        /**
         * Method called to verify if the adapter is working correctly
         * should be use for exemple to verify that credentials and number are both valid.
         *
         * @return bool : False on error, true else
         */
        public function test(): bool;

        /**
         * Method called on reception of a status update notification for a SMS.
         *
         * @return mixed : False on error, else array ['uid' => uid of the sms, 'status' => New status of the sms (\models\Sended::STATUS_UNKNOWN, \models\Sended::STATUS_DELIVERED, \models\Sended::STATUS_FAILED)]
         */
        public static function status_change_callback();

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
        public static function reception_callback(): array;
    }
