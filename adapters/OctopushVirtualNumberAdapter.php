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
    const SMS_TYPE_LOWCOST = 'sms_low_cost';
    const SMS_TYPE_PREMIUM = 'sms_premium';

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
     * Octopush SMS type.
     */
    private $sms_type;

    /**
     * Octopush api baseurl.
     */
    private $api_url = 'https://api.octopush.com/v1/public';

    /**
     * Octopush phone number.
     */
    private $number;

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

        $this->sms_type = self::SMS_TYPE_LOWCOST;
        if (($this->data['sms_type'] ?? false) && 'premium' === $this->data['sms_type'])
        {
            $this->sms_type = self::SMS_TYPE_PREMIUM;
        }
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
            [
                'name' => 'sms_type',
                'title' => 'Type de SMS à employer',
                'description' => 'Type de SMS à employer coté Octopush, rentrez "low cost" ou "premium" selon le type de SMS que vous souhaitez employer. Laissez vide pour utiliser par défaut des SMS low cost.',
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
            $headers = [
                'api-login: ' . $this->login,
                'api-key: ' . $this->api_key,
                'Content-Type: application/json',
            ];

            $data = [
                'text' => $text,
                'recipients' => [['phone_number' => $destination]],
                'sms_type' => $this->sms_type,
                'purpose' => 'alert',
                'sender' => $this->number,
                'with_replies' => 'True',
            ];

            $data = json_encode($data);

            $endpoint = $this->api_url . '/sms-campaign/send';

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
                $response['error_message'] = 'Response indicate error code : ' . $response_decode['code'] . ' -> """' . $response_decode['message'] . '""" AND  HTTP CODE -> ' . $http_code;

                return $response;
            }

            $uid = $response_decode['sms_ticket'] ?? false;
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

            if (!empty($this->data['sms_type']) && !in_array($this->data['sms_type'], ['premium', 'low cost']))
            {
                return false;
            }

            $origin = \controllers\internals\Tool::parse_phone($this->data['number']);
            if (!$origin)
            {
                return false;
            }

            $headers = [
                'api-login: ' . $this->login,
                'api-key: ' . $this->api_key,
                'Content-Type: application/json',
            ];

            //Check service name
            $endpoint = $this->api_url . '/wallet/check-balance';
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

        $uid = $content['message_id'] ?? false;
        $status = $content['status'] ?? false;

        if (false === $uid || false === $status)
        {
            return false;
        }

        switch ($status)
        {
            case 'DELIVERED':
                $status = \models\Sended::STATUS_DELIVERED;

                break;

            case 'NOT_DELIVERED':
            case 'NOT_ALLOWED':
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

        $number = $content['number'] ?? false;
        $text = $content['text'] ?? false;
        $at = $content['reception_date'] ?? false;

        if (!$number || !$text || !$at)
        {
            $response['error'] = true;
            $response['error_message'] = 'One required data of the callback is missing.';

            return $response;
        }

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
