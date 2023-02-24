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

    use Ovh\Api;

    /**
     * OVH SMS service with a virtual number adapter.
     */
    class OvhSmsVirtualNumberAdapter implements AdapterInterface
    {
        /**
         * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $data;

        /**
         * OVH Api instance.
         */
        private $api;

        /**
         * Number used.
         */
        private $number;

        /**
         * Number formated to be compatible with http query according to the ovh way.
         */
        private $formatted_number;

        /**
         * Adapter constructor, called when instanciated by RaspiSMS.
         *
         * @param json string $data : JSON string of the data to configure interaction with the implemented service
         */
        public function __construct(string $data)
        {
            $this->data = json_decode($data, true);

            $this->api = new Api(
                $this->data['app_key'],
                $this->data['app_secret'],
                'ovh-eu',
                $this->data['consumer_key']
            );

            $this->number = $this->data['number'];
            $this->formatted_number = str_replace('+', '00', $this->number);
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
            return 'ovh_sms_virtual_number_adapter';
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
            return 'OVH SMS Numéro virtuel';
        }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description(): string
        {
            $generate_credentials_url = 'https://eu.api.ovh.com/createToken/index.cgi?GET=/sms&GET=/sms/*&POST=/sms/*&PUT=/sms/*&DELETE=/sms/*&';

            return '
                Solution de SMS proposé par le groupe <a target="_blank" href="https://www.ovhtelecom.fr/sms/">OVH</a>. Pour générer les clefs API OVH, <a target="_blank" href="' . $generate_credentials_url . '">cliquez ici.</a><br/>
                Pour plus d\'information sur l\'utilisation de ce téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/ovh_virtual_number.html" target="_blank">la documentation sur le téléphone "OVH Numéro Virtuel".</a>
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
                    'name' => 'service_name',
                    'title' => 'Service Name',
                    'description' => 'Service Name de votre service SMS chez OVH. Il s\'agit du nom associé à votre service SMS dans la console OVH, probablement quelque chose comme "sms-xxxxx-1" ou "xxxx" est votre identifiant client OVH.',
                    'required' => true,
                ],
                [
                    'name' => 'number',
                    'title' => 'Numéro',
                    'description' => 'Numéro de téléphone virtuel chez OVH.',
                    'required' => true,
                    'type' => 'phone_number',
                ],
                [
                    'name' => 'app_key',
                    'title' => 'Application Key',
                    'description' => 'Paramètre "Application Key" obtenu lors de la génération de la clef API OVH.',
                    'required' => true,
                ],
                [
                    'name' => 'app_secret',
                    'title' => 'Application Secret',
                    'description' => 'Paramètre "Application Secret" obtenu lors de la génération de la clef API OVH.',
                    'required' => true,
                ],
                [
                    'name' => 'consumer_key',
                    'title' => 'Consumer Key',
                    'description' => 'Paramètre "Consumer Key" obtenu lors de la génération de la clef API OVH.',
                    'required' => true,
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
                $success = true;

                $endpoint = '/sms/' . $this->data['service_name'] . '/virtualNumbers/' . $this->formatted_number . '/jobs';
                $params = [
                    'message' => $text,
                    'receivers' => [$destination],
                ];

                $response = $this->api->post($endpoint, $params);

                $nb_invalid_receivers = \count(($response['invalidReceivers'] ?? []));
                if ($nb_invalid_receivers > 0)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'Invalid receiver';

                    return $response;
                }

                $uid = $response['ids'][0] ?? false;
                if (!$uid)
                {
                    $response['error'] = true;
                    $response['error_message'] = 'Cannot retrieve uid';

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
            $response = [
                'error' => false,
                'error_message' => null,
                'smss' => [],
            ];

            try
            {
                $endpoint = '/sms/' . $this->data['service_name'] . '/virtualNumbers/' . $this->formatted_number . '/incoming';
                $uids = $this->api->get($endpoint);

                if (!\is_array($uids) || !$uids)
                {
                    return $response;
                }

                foreach ($uids as $uid)
                {
                    $endpoint = '/sms/' . $this->data['service_name'] . '/virtualNumbers/' . $this->formatted_number . '/incoming/' . $uid;
                    $sms_details = $this->api->get($endpoint);

                    if (!isset($sms_details['creationDatetime'], $sms_details['message'], $sms_details['sender']))
                    {
                        continue;
                    }

                    $response['smss'][] = [
                        'at' => (new \DateTime($sms_details['creationDatetime']))->format('Y-m-d H:i:s'),
                        'text' => $sms_details['message'],
                        'origin' => $sms_details['sender'],
                    ];

                    //Remove the sms to prevent double reading as ovh do not offer a filter for unread messages only
                    $endpoint = '/sms/' . $this->data['service_name'] . '/virtualNumbers/' . $this->formatted_number . '/incoming/' . $uid;
                    $this->api->delete($endpoint);
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
                return true;
                $success = true;

                //Check service name
                $endpoint = '/sms/' . $this->data['service_name'];
                $response = $this->api->get($endpoint);
                $success = $success && (bool) $response;

                //Check virtualnumber
                $endpoint = '/sms/virtualNumbers/' . $this->formatted_number;
                $response = $this->api->get($endpoint);

                return $success && (bool) $response;
            }
            catch (\Throwable $t)
            {
                return false;
            }
        }

        public static function status_change_callback()
        {
            $uid = $_GET['id'] ?? false;
            $dlr = $_GET['dlr'] ?? false;

            if (false === $uid || false === $dlr)
            {
                return false;
            }

            switch ($dlr)
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
