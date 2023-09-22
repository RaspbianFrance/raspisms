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
     * available to creation through API only.
     */
    public static function meta_hidden(): bool;

    /**
     * Should this adapter data be hidden after creation
     * this help to prevent API credentials to other service leak if an attacker gain access to RaspiSMS through user credentials.
     */
    public static function meta_hide_data(): bool;

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
     * Does the implemented service support updating phone status.
     */
    public static function meta_support_phone_status(): bool;

    /**
     * Does the implemented service support reception callback.
     */
    public static function meta_support_reception(): bool;

    /**
     * Does the implemented service support status change callback.
     */
    public static function meta_support_status_change(): bool;

    /**
     * Does the implemented service support mms reception.
     */
    public static function meta_support_mms_reception(): bool;

    /**
     * Does the implemented service support mms sending.
     */
    public static function meta_support_mms_sending(): bool;

    /**
     * Does the implemented service support inbound call callback.
     */
    public static function meta_support_inbound_call_callback(): bool;

    /**
     * Does the implemented service support end call callback.
     */
    public static function meta_support_end_call_callback(): bool;

    /**
     * Method called to send a SMS to a number.
     *
     * @param string $destination : Phone number to send the sms to
     * @param string $text        : Text of the SMS to send
     * @param bool   $flash       : Is the SMS a Flash SMS
     * @param bool   $mms         : Is the SMS a MMS
     * @param array  $medias      : Array of medias to link to the MMS, [['http_url' => HTTP public url of the media et 'local_uri' => local uri to media file]]
     *
     * @return array : [
     *               bool 'error' => false if no error, true else,
     *               ?string 'error_message' => null if no error, else error message,
     *               array 'uid' => Uid of the sms created on success,
     *               ]
     */
    public function send(string $destination, string $text, bool $flash = false, bool $mms = false, array $medias = []): array;

    /**
     * Method called to read SMSs of the number.
     *
     * @return array : [
     *               bool 'error' => false if no error, true else
     *               ?string 'error_message' => null if no error, else error message
     *               array 'smss' => Array of the sms reads [[
     *               (optional) bool 'mms' => default to false, true if mms
     *               (optional) array 'medias' => default to [], list of array representing medias to link to sms, with [
     *               'filepath' => local file copy of the media,
     *               'extension' (optional) => extension of the media,
     *               'mimetype' (optional) => mimetype of the media
     *               ]
     *               ], ...]
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
     * Method called to verify phone status
     * 
     * @return string : Return one phone status among 'available', 'unavailable', 'no_credit', 'limit_reached'
     */
    public function check_phone_status(): string;


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
     *               (optional) array 'medias' => default to [], list of array representing medias to link to sms, with [
     *               'filepath' => local file copy of the media,
     *               'extension' (optional) => extension of the media,
     *               'mimetype' (optional) => mimetype of the media
     *               ]
     *               ]
     *               ]
     */
    public static function reception_callback(): array;

    /**
     * Method called on reception of an inbound_call notification.
     *
     * @return array : [
     *               bool 'error' => false on success, true on error
     *               ?string 'error_message' => null on success, error message else
     *               array 'call' => array [
     *               string 'uid' : Uid of the call on the adapter plateform
     *               string 'start' : Start of the call date format Y-m-d H:i:s,
     *               ?string 'end' : End of the call date format Y-m-d H:i:s. If no known end, NULL
     *               string 'origin' : Emitter phone call number. International format.
     *               ]
     *               ]
     */
    public static function inbound_call_callback(): array;

    /**
     * Method called on reception of a end call notification.
     *
     * @return array : [
     *               bool 'error' => false on success, true on error
     *               ?string 'error_message' => null on success, error message else
     *               array 'call' => array [
     *               string 'uid' : Uid of the call on the adapter plateform. Used to find the raspisms local call to update.
     *               string 'end' : End of the call date format Y-m-d H:i:s.
     *               ]
     *               ]
     */
    public static function end_call_callback(): array;
}
