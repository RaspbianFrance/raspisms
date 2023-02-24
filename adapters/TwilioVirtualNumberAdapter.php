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

use Twilio\Rest\Client;

/**
 * Twilio SMS service with a virtual number adapter.
 */
class TwilioVirtualNumberAdapter implements AdapterInterface
{
    /**
     * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
     */
    private $data;

    /**
     * Twilio Api client.
     */
    private $client;

    /**
     * Twilio virtual number to use.
     */
    private $number;

    /**
     * Callback address Twilio must call on SMS status change.
     */
    private $status_change_callback;

    /**
     * Adapter constructor, called when instanciated by RaspiSMS.
     *
     * @param string      $number : Phone number the adapter is used for
     * @param json string $data   : JSON string of the data to configure interaction with the implemented service
     */
    public function __construct(string $data)
    {
        $this->data = json_decode($data, true);

        $this->client = new Client(
            $this->data['account_sid'],
            $this->data['auth_token']
        );

        $this->number = $this->data['number'];
        $this->status_change_callback = $this->data['status_change_callback'];
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
        return 'twilio_virtual_number_adapter';
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
        return 'Twilio Numéro virtuel';
    }

    /**
     * Description of the adapter.
     * A short description of the service the adapter implements.
     */
    public static function meta_description(): string
    {
        $credentials_url = 'https://www.twilio.com/console';

        return '
                Solution de SMS avec numéro virtuel proposé par <a target="_blank" href="https://www.twilio.com/sms">Twilio</a>. Pour trouver vos clés API Twilio <a target="_blank" href="' . $credentials_url . '">cliquez ici.</a><br/>
                Pour plus d\'information sur l\'utilisation de ce téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/twilio_virtual_number.html" target="_blank">la documentation sur le téléphone "Twilio Numéro Virtuel".</a>
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
                'name' => 'account_sid',
                'title' => 'Account SID',
                'description' => 'Identifiant unique Twilio, trouvable sur la page d\'accueil de la console Twilio.',
                'required' => true,
            ],
            [
                'name' => 'auth_token',
                'title' => 'Auth Token',
                'description' => 'Jeton d\'identification Twilio, trouvable sous le Account SID.',
                'required' => true,
            ],
            [
                'name' => 'number',
                'title' => 'Numéro de téléphone virtuel',
                'description' => 'Numéro de téléphone virtuel Twilio à utiliser parmis les numéro actifs (format international), <a href="https://www.twilio.com/console/phone-numbers/incoming" target="_blank">voir la liste ici</a>.',
                'required' => true,
                'type' => 'phone_number',
            ],
            [
                'name' => 'status_change_callback',
                'title' => 'Callback de changement de status',
                'description' => 'L\'adresse que Twilio devra appeler pour signaler le changement de statut d\'un SMS. Laissez tel quel par défaut.',
                'required' => true,
                'default_value' => \descartes\Router::url('Callback', 'update_sended_status', ['adapter_uid' => self::meta_uid()], ['api_key' => $_SESSION['user']['api_key'] ?? '<your_api_key>']),
            ],
        ];
    }

    /**
     * Does the implemented service support reading smss.
     */
    public static function meta_support_read(): bool
    {
        return true;
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
        return false;
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
            $message = $this->client->messages->create(
                $destination,
                [
                    'from' => $this->number,
                    'body' => $text,
                    'statusCallback' => $this->status_change_callback,
                ]
            );

            if (null !== $message->errorCode)
            {
                $response['error'] = true;
                $response['error_message'] = $message->errorMessage;

                return $response;
            }

            $response['uid'] = $message->sid;

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
        $response = [
            'error' => false,
            'error_message' => null,
            'smss' => [],
        ];

        try
        {
            $messages = $this->client->messages->read([
                'to' => $this->number,
            ], 20);

            foreach ($messages as $record)
            {
                if ('inbound' !== $record->direction)
                {
                    continue;
                }

                $timezone = date_default_timezone_get();
                $record->dateCreated->setTimezone(new \DateTimeZone($timezone));

                $response['smss'][] = [
                    'at' => $record->dateCreated->format('Y-m-d H:i:s'),
                    'text' => $record->body,
                    'origin' => $record->from,
                ];

                //Remove sms to prevent double reading
                $this->client->messages($record->sid)->delete();
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
            $phone_numbers = $this->client->incomingPhoneNumbers->read(['phoneNumber' => $this->number], 20);

            foreach ($phone_numbers as $record)
            {
                //If not the same number, return false
                if ($record->phoneNumber !== $this->number)
                {
                    continue;
                }

                return true; //Same number, its all ok we can return true
            }

            return false;
        }
        catch (\Throwable $t)
        {
            return false;
        }
    }

    public static function status_change_callback()
    {
        $sid = $_REQUEST['MessageSid'] ?? false;
        $status = $_REQUEST['MessageStatus'] ?? false;

        if (!$sid || !$status)
        {
            return false;
        }

        switch ($status)
        {
        case 'delivered':
            $status = \models\Sended::STATUS_DELIVERED;

            break;

        case 'failed':
            $status = \models\Sended::STATUS_FAILED;

            break;

        default:
            $status = \models\Sended::STATUS_UNKNOWN;

            break;
        }

        return ['uid' => $sid, 'status' => $status];
    }

    public static function reception_callback(): array
    {
        return [];
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
