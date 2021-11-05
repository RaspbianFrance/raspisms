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

use controllers\internals\Quota;
use controllers\internals\Tool;
use descartes\Router;

/**
 * Kannel adapter.
 */
class KannelAdapter implements AdapterInterface
{
    const KANNEL_SENDSMS_RESULTS_ACCEPTED = 0;
    const KANNEL_SENDSMS_RESULTS_QUEUED = 3;

    const KANNEL_SENDSMS_HTTP_CODE_ACCEPTED = 202;
    const KANNEL_SENDSMS_HTTP_CODE_QUEUED = 202;

    const KANNEL_CODING_7_BITS = 0;
    const KANNEL_CODING_8_BITS = 1;
    const KANNEL_CODING_UCS_2 = 2;

    /**
     * DLR mask to transmit to kannel
     * 
     * 1 -> Delivered to phone
     * 2 -> not delivered
     * 16 -> non delivered to SMSC
     * 
     * (see https://gist.github.com/grantpullen/3d550f31c454e80fda8fc0d5b9105fd0)
     */
    const KANNEL_DLR_BITMASK = 1 + 2 + 16; 

    /**
     * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
     */
    private $data;

    /**
     * Kannel send-sms service url
     */
    private $kannel_sendsms_url;

    /**
     * Kannel send-sms username.
     */
    private $username;

    /**
     * Kannel send-sms password.
     */
    private $password;

    /**
     * Phone number of the sender, this number may or may not actually be overrided by the SMSC
     */
    private $from;

    /**
     * SMSC's id to use for sending the message
     */
    private $smsc;

    /**
     * SMS Delivery Report Url
     */
    private $dlr_url;

    /**
     * Adapter constructor, called when instanciated by RaspiSMS.
     *
     * @param string      $number : Phone number the adapter is used for
     * @param json string $data   : JSON string of the data to configure interaction with the implemented service
     */
    public function __construct(string $data)
    {
        $this->data = json_decode($data, true);

        $this->kannel_sendsms_url = $this->data['kannel_sendsms_url'];
        $this->username = $this->data['username'];
        $this->password = $this->data['password'];
        $this->from = $this->data['from'];
        $this->dlr_url = $this->data['dlr_url'];
        
        $this->smsc = $this->data['smsc'] ?? null;
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
        return 'kannel_adapter';
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
        return 'Kannel';
    }

    /**
     * Description of the adapter.
     * A short description of the service the adapter implements.
     */
    public static function meta_description(): string
    {
        $kannel_homepage = 'https://www.kannel.org';

        return '
                Envoi de SMS via le logiciel Kannel, pour plus d\'information sur Kannel, voir <a target="_blank" href="' . $kannel_homepage . '">le site du projet.</a><br/>
                Pour plus d\'information sur l\'utilisation de ce type de téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/kannel.html" target="_blank">la documentation sur le téléphone "Kannel".</a>
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
                'name' => 'kannel_sendsms_url',
                'title' => 'Adresse URL du service kannel sendsms',
                'description' => 'Adresse URL du service sendsms de Kannel (ex : http://smsbox.host.name:13013/cgi-bin/sendsms)',
                'required' => true,
            ],
            [
                'name' => 'username',
                'title' => 'Nom de l\'utilisateur',
                'description' => 'Nom d\'utilisateur du service send-sms de Kannel.',
                'required' => true,
            ],
            [
                'name' => 'password',
                'title' => 'Mot de passe de l\'utilisateur',
                'description' => 'Mot de passe de l\'utilisateur du service send-sms de Kannel.',
                'required' => true,
            ],
            [
                'name' => 'from',
                'title' => 'Numéro de téléphone émetteur ou nom de l\'émetteur',
                'description' => 'Numéro de téléphone à transmettre au SMS Center, ou nom à afficher à la place du numéro (dans ce cas, entre 3 et 11 caractères), dans la très grande majorité des cas, ce numéro ou ce nom sera écrasé par le SMSC.',
                'required' => true,
            ],
            [
                'name' => 'dlr_url',
                'title' => 'Adresse URL de livraison du Delivery Report du SMS',
                'description' => 'Adresse URL de livraison du Delivery Report du SMS qui sera transmis à Kannel. Vous devriez probablement laisser ce champs tel quel.',
                'required' => true,
                'default_value' => \descartes\Router::url('Callback', 'update_sended_status', ['adapter_uid' => self::meta_uid()], ['api_key' => $_SESSION['user']['api_key'] ?? '']),
            ],
            [
                'name' => 'smsc',
                'title' => 'Identifiant unique du SMSC',
                'description' => 'Identifiant du SMSC (sms-id) à utiliser pour envoyer le message.<br/>
                                  <b>Laissez vide pour laisser Kannel décider du routage vers le SMSC.</b>',
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
            //As kannel does not return uid of the SMS when sending it, we create our own uid and will pass it to kannel's delivery report url
            //in order to retrieve it in raspisms and update the status
            $sms_uid = Tool::random_uuid();


            //Forge dlr Url by adding new query parts to url provided within phone settings
            $dlr_url_parts = parse_url($this->dlr_url);

            //Append sms uid and delivery report value to the original dlr_url query parts
            $dlr_url_parts['query'] = $dlr_url_parts['query'] ?? '';
            $dlr_url_query_parts = [];
            parse_str($dlr_url_parts['query'], $dlr_url_query_parts);
            unset($dlr_url_query_parts['type']);
            $dlr_url_query_parts['sms_uid'] = $sms_uid; //Pass uid as param so raspisms can identify sms to update
            $dlr_url_parts['query'] = http_build_query($dlr_url_query_parts) . '&type=%d'; //Kannel will replace %d by the delivery report value. We cannot set type in bild query or it get double encoded

            $forged_dlr_url = Tool::unparse_url($dlr_url_parts);
            

            $data = [
                'username' => $this->username,
                'password' => $this->password,
                'text' => $text,
                'to' => $destination,
                'from' => $this->from,
                'dlr-mask' => self::KANNEL_DLR_BITMASK,
                'dlr-url' => $forged_dlr_url,
            ];

            //If necessary, use utf8 sms to represent special chars
            $use_utf8_sms = !Quota::is_gsm0338($text);
            if ($use_utf8_sms)
            {
                $data['coding'] = self::KANNEL_CODING_8_BITS;
            }

            if ($this->smsc)
            {
                $data['smsc'] = $this->smsc;
            }

            $endpoint = $this->kannel_sendsms_url . '?' . http_build_query($data);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            $curl_response = curl_exec($curl);
            $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (false === $curl_response)
            {
                $response['error'] = true;
                $response['error_message'] = 'HTTP query failed.';

                return $response;
            }

            if (!in_array($http_code, [self::KANNEL_SENDSMS_HTTP_CODE_ACCEPTED, self::KANNEL_SENDSMS_HTTP_CODE_QUEUED]))
            {
                $response['error'] = true;
                $response['error_message'] = 'Response error with HTTP code : ' . $http_code . ' -> ' . $curl_response;

                return $response;
            }

            $response['uid'] = $sms_uid;

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
            if (!$this->username || !$this->password || !$this->from || !$this->dlr_url)
            {
                return false;
            }

            //Check kannel url is a valid http/https url to protect against ssrf
            //This is mainly cosmetic, the real protection is in CURLOPT_PROTOCOLS
            if (!mb_ereg_match('^http(s?)://', $this->kannel_sendsms_url))
            {
                return false;
            }

            //Check credentials and kannel url
            $data = [
                'username' => $this->username,
                'password' => $this->password,
            ];

            $endpoint = $this->kannel_sendsms_url . '?' . http_build_query($data);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS); //Protect curl against non http(s) queries and redirects
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            $curl_response = curl_exec($curl);
            $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (false === $curl_response)
            {
                return false;
            }

            switch (true)
            {
                case 403 == $http_code : //Bad credentials
                case 404 == $http_code : //Cannot find url
                    return false;

                case $http_code >= 500 : //Server error
                    return false;
            }

            if (!filter_var($this->dlr_url, FILTER_VALIDATE_URL))
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
        $status = $_GET['type'] ?? false;
        $uid = $_GET['sms_uid'] ?? false;
        
        if (!$status || !$uid)
        {
            return false;
        }

        switch ((int) $status)
        {
            case 1:
                $status = \models\Sended::STATUS_DELIVERED;

                break;

            case 2:
            case 16:
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

        $text = file_get_contents('php://input');
        $number = $_SERVER['HTTP_X_KANNEL_TO'] ?? false;
        $at = $_SERVER['HTTP_X_KANNEL_TIME'] ?? false;

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
