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

use DateTime;

/**
 * Odyssey Messaging SMS service
 */
class OdysseyMessagingAdapter implements AdapterInterface
{
    const EVENT_TYPES = [
        'OPT_OUT' => 1,
        'SYSTEM_ERROR' => 2,
        'END_OF_ITEM' => 3,
        'END_OF_JOB' => 4,
        'JOB_STATUS_CHANGED' => 5,
        'REAL_TIME_STATUS' => 6,
        'RETRIEVE_FILE' => 7,
        'INBOUND_SMS' => 8,
        'ITEM_STATUS_CHANGED' => 9,
        'DATA_COLLECTION_FILLED' => 10,
    ];

    /**
     * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
     */
    private $data;

    /**
     * Odyssey login.
     */
    private $login;

    /**
     * Odyssey password.
     */
    private $password;

    /**
     * Sender name to use instead of shortcode.
     */
    private $sender;

    /**
     * Odyssey api baseurl.
     */
    private $api_url = 'https://api.odyssey-services.fr/api/v1';

    /**
     * Adapter constructor, called when instanciated by RaspiSMS.
     *
     * @param json string $data   : JSON string of the data to configure interaction with the implemented service
     */
    public function __construct(string $data)
    {
        $this->data = json_decode($data, true);

        $this->login = $this->data['login'];
        $this->password = $this->data['password'];

        $this->sender = $this->data['sender'] ?? null;
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
        return 'odyssey_messaging_adapter';
    }

    /**
     * Should this adapter be hidden in user interface for phone creation and
     * available to creation through API only.
     */
    public static function meta_hidden(): bool
    {
        return false;
    }

    /**
     * Should this adapter data be hidden after creation
     * this help to prevent API credentials to other service leak if an attacker gain access to RaspiSMS through user credentials.
     */
    public static function meta_hide_data(): bool
    {
        return false;
    }

    /**
     * Name of the adapter.
     * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.).
     */
    public static function meta_name(): string
    {
        return 'Odyssey Messaging';
    }

    /**
     * Description of the adapter.
     * A short description of the service the adapter implements.
     */
    public static function meta_description(): string
    {
        return '
                Envoi de SMS avec <a target="_blank" href="https://www.odyssey-messaging.com/">Odyssey Messaging</a>.
                Pour plus d\'information sur l\'utilisation de ce type de téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/odyssey_messaging.html" target="_blank">la documentation sur le téléphone "Odyssey Messaging".</a>
            ';
    }

    /**
     * List of entries we want in data for the adapter.
     *
     * @return array : Every line is a field as an array with keys : name, title, description, required
     */
    public static function meta_data_fields(): array
    {
        return [
            [
                'name' => 'login',
                'title' => 'Odyssey login',
                'description' => 'Login du compte Odyssey à employer.',
                'required' => true,
            ],
            [
                'name' => 'password',
                'title' => 'Mot de passe',
                'description' => 'Mot de passe du compte Odyssey à employer.',
                'required' => true,
            ],
            [
                'name' => 'sender',
                'title' => 'Nom de l\'expéditeur',
                'description' => 'Nom de l\'expéditeur à afficher à la place du numéro (11 caractères max).<br/>
                                  <b>Laissez vide pour ne pas utiliser d\'expéditeur nommé.</b><br/>
                                  <b>Si vous utilisez un expéditeur nommé, le destinataire ne pourra pas répondre.</b>',
                'required' => false,
            ],
        ];
    }

    /**
     * Does the implemented service support reading smss.
     */
    public static function meta_support_read(): bool
    {
        return false;
    }

    /**
     * Does the implemented service support updating phone status.
     */
    public static function meta_support_phone_status(): bool
    {
        return false;
    }

    /**
     * Does the implemented service support flash smss.
     */
    public static function meta_support_flash(): bool
    {
        return false;
    }

    /**
     * Does the implemented service support status change.
     */
    public static function meta_support_status_change(): bool
    {
        return true;
    }

    /**
     * Does the implemented service support reception callback.
     */
    public static function meta_support_reception(): bool
    {
        return true;
    }

    /**
     * Does the implemented service support mms reception.
     */
    public static function meta_support_mms_reception(): bool
    {
        return false;
    }

    /**
     * Does the implemented service support mms sending.
     */
    public static function meta_support_mms_sending(): bool
    {
        return false;
    }

    public static function meta_support_inbound_call_callback(): bool
    {
        return false;
    }

    public static function meta_support_end_call_callback(): bool
    {
        return false;
    }

    public function send(string $destination, string $text, bool $flash = false, bool $mms = false, array $medias = []): array
    {
        $response = [
            'error' => false,
            'error_message' => null,
            'uid' => null,
        ];

        try
        {
            $credentials = base64_encode($this->login . ':' . $this->password);
            $headers = [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json',
            ];

            $data = [
                'JobType' => 'SMS',
                'Text' => $text,
                'TrackingID' => uniqid(),
                'AdhocRecipients' => [['Name' => uniqid(), 'Address' => str_replace('+', '00', $destination)]],
            ];

            if ($this->sender)
            {
                $data['Parameter'] = ['Sender' => $this->sender, 'Media' => 1];
            }

            $data = json_encode($data);

            $endpoint = $this->api_url . '/SMSJobs';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $curl_response = curl_exec($curl);
            $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (false === $curl_response)
            {
                $response['error'] = true;
                $response['error_message'] = 'HTTP query failed.';

                return $response;
            }

            $response_decode = json_decode($curl_response, true);
            if (null === $response_decode)
            {
                $response['error'] = true;
                $response['error_message'] = 'Invalid JSON for response.';

                return $response;
            }

            if (200 !== $http_code)
            {
                $response['error'] = true;
                $response['error_message'] = 'Response indicate error : ' . $response_decode['Message'] . ' -> """' . json_encode($response_decode['ModelState']) . '""" AND  HTTP CODE -> ' . $http_code;

                return $response;
            }

            $uid = $response_decode['JobNumber'] ?? false;
            if (!$uid)
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot extract SMS uid';

                return $response;
            }

            $response['uid'] = $uid;

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

    /**
     * Method called to verify phone status
     * 
     * @return string : Return one phone status among 'available', 'unavailable', 'no_credit'
     */
    public function check_phone_status(): string
    {
        return \models\Phone::STATUS_AVAILABLE;
    }

    public function test(): bool
    {
        try
        {
            if ($this->data['sender'] && (mb_strlen($this->data['sender']) < 3 || mb_strlen($this->data['sender'] > 11)))
            {
                return false;
            }

            if (!empty($this->data['sms_type']) && !in_array($this->data['sms_type'], ['premium', 'low cost']))
            {
                return false;
            }

            $credentials = base64_encode($this->login . ':' . $this->password);
            $headers = [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json',
            ];

            //Check service name
            $endpoint = $this->api_url . '/JobTypes';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
            $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (200 !== $http_code)
            {
                return false;
            }

            return true;
        }
        catch (\Throwable $t)
        {
            return false;
        }
    }

    public static function status_change_callback()
    {
        header('Connection: close');
        header('Content-Encoding: none');
        header('Content-Length: 0');

        $input = file_get_contents('php://input');
        $content = json_decode($input, true);
        if (null === $content)
        {
            return false;
        }

        $event_type = $content['EventType'] ?? false;
        if ($event_type != self::EVENT_TYPES['ITEM_STATUS_CHANGED'])
        {
            return false;
        }

        $uid = $content['JobNumber'] ?? false;
        $status = $content['Outcome'] ?? false;

        if (false === $uid || false === $status)
        {
            return false;
        }

        switch ($status)
        {
            case 'S':
                $status = \models\Sended::STATUS_DELIVERED;

                break;

            case 'B':
                $status = \models\Sended::STATUS_UNKNOWN;

                break;

            default:
                $status = \models\Sended::STATUS_FAILED;

                break;
        }

        return ['uid' => $uid, 'status' => $status];
    }

    public static function reception_callback(): array
    {
        $response = [
            'error' => false,
            'error_message' => null,
            'sms' => null,
        ];

        header('Connection: close');
        header('Content-Encoding: none');
        header('Content-Length: 0');

        $input = file_get_contents('php://input');
        $content = json_decode($input, true);
        if (null === $content)
        {
            $response['error'] = true;
            $response['error_message'] = 'Cannot read input data from callback request.';

            return $response;
        }

        $event_type = $content['EventType'] ?? false;
        if ($event_type != self::EVENT_TYPES['INBOUND_SMS'])
        {
            $response['error'] = true;
            $response['error_message'] = 'Invalid event type : ' . $event_type . '.';

            return $response;
        }

        $number = $content['From'] ?? false;
        $text = $content['Message'] ?? false;
        $at = $content['EventDateTime'] ?? false;

        if (!$number || !$text || !$at)
        {
            $response['error'] = true;
            $response['error_message'] = 'One required data of the callback is missing.';

            return $response;
        }

        $matches = null;
        $match = preg_match('#/Date\(([0-9]+)\+([0-9]+)\)/#', $at, $matches);
        $timestamp = ($matches[1] ?? null);
        if (!$match || !$timestamp)
        {
            $response['error'] = true;
            $response['error_message'] = 'Invalid date.';

            return $response;
        }

        $at = DateTime::createFromFormat('U', $timestamp / 1000);
        $at = $at->format('Y-m-d H:i:s');

        $origin = \controllers\internals\Tool::parse_phone($number);
        if (!$origin)
        {
            $response['error'] = true;
            $response['error_message'] = 'Invalid origin number : ' . $number;

            return $response;
        }

        $response['sms'] = [
            'at' => $at,
            'text' => $text,
            'origin' => $origin,
        ];

        return $response;
    }

    public function inbound_call_callback(): array
    {
        return [];
    }

    public function end_call_callback(): array
    {
        return [];
    }
}
