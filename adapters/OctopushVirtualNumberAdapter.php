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
 * Octopush SMS service with a shortcode adapter.
 */
class OctopushVirtualNumberAdapter implements AdapterInterface
{
    const ERROR_CODE_OK = '000';
    const SMS_TYPE_LOWCOST = 'XXX';
    const SMS_TYPE_PREMIUM = 'FR';
    const SMS_TYPE_INTERNATIONAL = 'WWW';

    /**
     * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
     */
    private $data;

    /**
     * Octopush login.
     */
    private $login;

    /**
     * Octopush api key.
     */
    private $api_key;

    /**
     * Number phone to use.
     */
    private $number;

    /**
     * Number phone to use formated for octopush compatibility.
     */
    private $formatted_number;

    /**
     * Octopush api baseurl.
     */
    private $api_url = 'https://www.octopush-dm.com/api';

    /**
     * Adapter constructor, called when instanciated by RaspiSMS.
     *
     * @param string      $number : Phone number the adapter is used for
     * @param json string $data   : JSON string of the data to configure interaction with the implemented service
     */
    public function __construct(string $data)
    {
        $this->data = json_decode($data, true);

        $this->login = $this->data['login'];
        $this->api_key = $this->data['api_key'];
        $this->number = $this->data['number'];
        $this->formatted_number = '+' . mb_substr($this->data['number'], 2);
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
        return 'octopush_virtual_number_adapter';
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
     * Name of the adapter.
     * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.).
     */
    public static function meta_name(): string
    {
        return 'Octopush Numéro virtuel';
    }

    /**
     * Description of the adapter.
     * A short description of the service the adapter implements.
     */
    public static function meta_description(): string
    {
        $credentials_url = 'https://www.octopush-dm.com/api-logins';

        return '
                Envoi de SMS avec un numéro virtuel en utilisant <a target="_blank" href="https://www.octopush.com/">Octopush</a>. Pour trouver vos clés API Octopush <a target="_blank" href="' . $credentials_url . '">cliquez ici.</a><br/>
                Pour plus d\'information sur l\'utilisation de ce téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/octopush_virtual_number.html" target="_blank">la documentation sur les téléphones "Octopush Numéro Virtuel".</a>
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
                'title' => 'Octopush Login',
                'description' => 'Login du compte Octopush à employer. Trouvable sur la page des identifiants API Octopush.',
                'required' => true,
            ],
            [
                'name' => 'api_key',
                'title' => 'API Key',
                'description' => 'Clef API octopush. Trouvable sur la page des identifiants API Octopush.',
                'required' => true,
            ],
            [
                'name' => 'number',
                'title' => 'Numéro de téléphone virtuel',
                'description' => 'Numéro de téléphone virtuel Octopush à utiliser.',
                'required' => true,
                'number' => true,
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
            $data = [
                'user_login' => $this->login,
                'api_key' => $this->api_key,
                'sms_text' => $text,
                'sms_recipients' => str_replace('+', '00', $destination), //Must use 00 instead of + notation
                'sms_sender' => $this->formatted_number,
                'sms_type' => self::SMS_TYPE_LOWCOST,
            ];

            $endpoint = $this->api_url . '/sms/json';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($curl);
            curl_close($curl);

            if (false === $response)
            {
                $response['error'] = true;
                $response['error_message'] = 'HTTP query failed.';

                return $response;
            }

            $response_decode = json_decode($response, true);
            if (null === $response_decode)
            {
                $response['error'] = true;
                $response['error_message'] = 'Invalid JSON for response.';

                return $response;
            }

            if (self::ERROR_CODE_OK !== $response_decode['error_code'])
            {
                $response['error'] = true;
                $response['error_message'] = 'Response indicate error code : ' . $response_decode['error_code'];

                return $response;
            }

            $uid = $response_decode['ticket'] ?? false;
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

    public function test(): bool
    {
        try
        {
            $success = true;

            if ($this->data['sender'] && (mb_strlen($this->data['sender']) < 3 || mb_strlen($this->data['sender'] > 11)))
            {
                return false;
            }

            $data = [
                'user_login' => $this->login,
                'api_key' => $this->api_key,
            ];

            //Check service name
            $endpoint = $this->api_url . '/balance/json';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($curl);
            curl_close($curl);

            if (false === $response)
            {
                return false;
            }

            $response_decode = json_decode($response, true);
            if (null === $response_decode)
            {
                return false;
            }

            if (self::ERROR_CODE_OK !== $response_decode['error_code'])
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

        $uid = $_POST['message_id'] ?? false;
        $status = $_POST['status'] ?? false;

        if (false === $uid || false === $status)
        {
            return false;
        }

        switch ($status)
        {
            case 'DELIVERED':
                $status = \models\Sended::STATUS_DELIVERED;

                break;

            case 'NOT_ALLOWED':
            case 'INVALID_DESTINATION_ADDRESS':
            case 'OUT_OF_DATE':
            case 'EXPIRED':
            case 'BLACKLISTED_NUMBER':
                $status = \models\Sended::STATUS_FAILED;

                break;

            default:
                $status = \models\Sended::STATUS_UNKNOWN;

                break;
        }

        return ['uid' => $uid, 'status' => $status];
    }

    public static function reception_callback(): array
    {
        $response = [
            'error' => false,
            'error_message' => null,
            'uid' => null,
        ];

        header('Connection: close');
        header('Content-Encoding: none');
        header('Content-Length: 0');

        $number = $_POST['number'] ?? false;
        $text = $_POST['text'] ?? false;
        $at = $_POST['reception_date'] ?? false;

        if (!$number || !$text || !$at)
        {
            $response['error'] = true;
            $response['error_message'] = 'One required data of the callback is missing.';

            return $response;
        }

        $origin = \controllers\internals\Tool::parse_phone('+' . mb_substr($number, 2));
        if (!$origin)
        {
            $response['error'] = true;
            $response['error_message'] = 'Invalid origin number : ' . mb_substr($number, 2);

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
