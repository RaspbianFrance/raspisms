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
    class GammuAdapter implements AdapterInterface
    {
        /**
         * Data used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $data;

        /**
         * Adapter constructor, called when instanciated by RaspiSMS.
         *
         * @param json string $data : JSON string of the data to configure interaction with the implemented service
         */
        public function __construct(string $data)
        {
            $this->data = json_decode($data, true);
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
            return 'gammu_adapter';
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
            return 'Gammu';
        }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description(): string
        {
            return 'Utilisation du logiciel Gammu qui doit être installé sur le serveur et configuré. Voir <a target="_blank" href="https://wammu.eu">https://wammu.eu</a>.<br/>
                    Pour plus d\'information sur l\'utilisation de ce type de téléphone, reportez-vous à <a href="https://documentation.raspisms.fr/users/adapters/gammu.html" target="_blank">la documentation sur le téléphone "Gammu".</a>
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
                    'name' => 'config_file',
                    'title' => 'Fichier de configuration',
                    'description' => 'Chemin vers le fichier de configuration que Gammu devra utiliser pour se connecter au téléphone .',
                    'required' => true,
                ],
                [
                    'name' => 'pin',
                    'title' => 'Code PIN',
                    'description' => 'Code PIN devant être utilisé pour activer la carte SIM (laisser vide pour ne pas utiliser de code PIN).',
                    'required' => false,
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

            if (!$this->unlock_sim())
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot unlock SIM.';

                return $response;
            }

            $command_parts = [
                'LC_ALL=C',
                'gammu',
                '--config',
                escapeshellarg($this->data['config_file']),
                'sendsms',
                'TEXT',
                escapeshellarg($destination),
                '-textutf8',
                escapeshellarg($text),
                '-validity',
                'MAX',
                '-autolen',
                mb_strlen($text),
            ];

            if ($flash)
            {
                $command_parts[] = '-flash';
            }

            $result = $this->exec_command($command_parts);
            if (0 !== $result['return'])
            {
                $response['error'] = true;
                $response['error_message'] = 'Gammu command failed.';

                return $response;
            }

            $find_ok = $this->search_for_string($result['output'], 'ok');
            if (!$find_ok)
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot find output OK.';

                return $response;
            }

            $uid = false;
            foreach ($result['output'] as $line)
            {
                $matches = [];
                preg_match('#=([0-9]+)#u', $line, $matches);

                if ($matches[1] ?? false)
                {
                    $uid = $matches[1];

                    break;
                }
            }

            if (false === $uid)
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot retrieve sms uid.';

                return $response;
            }

            $response['uid'] = $uid;

            return $response;
        }

        public function read(): array
        {
            $response = [
                'error' => false,
                'error_message' => null,
                'smss' => [],
            ];

            if (!$this->unlock_sim())
            {
                $response['error'] = true;
                $response['error_message'] = 'Cannot unlock sim.';

                return $response;
            }

            $command_parts = [
                PWD . '/bin/gammu_get_unread_sms.py',
                escapeshellarg($this->data['config_file']),
            ];

            $return = $this->exec_command($command_parts);
            if (0 !== $return['return'])
            {
                $response['error'] = true;
                $response['error_message'] = 'Gammu command return failed.';

                return $response;
            }

            foreach ($return['output'] as $line)
            {
                $decode = json_decode($line, true);
                if (null === $decode)
                {
                    continue;
                }

                $response['smss'][] = [
                    'at' => $decode['at'],
                    'text' => $decode['text'],
                    'origin' => $decode['number'],
                ];
            }

            return $response;
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
            //Always return true as we cannot test because we would be needing a root account
            return true;
        }

        public static function status_change_callback()
        {
            return false;
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

        /**
         * Function to unlock pin.
         *
         * @return bool : False on error, true else
         */
        private function unlock_sim(): bool
        {
            if (!$this->data['pin'])
            {
                return true;
            }
            
            // The command returns 123 on failed execution (even if SIM is already unlocked), and returns 0 if unlock was successful
            // We can directly return true if command was succesful
            $command_parts = [
                'LC_ALL=C',
                'gammu',
                '--config',
                escapeshellarg($this->data['config_file']),
                'entersecuritycode',
                'PIN',
                escapeshellarg($this->data['pin']),
            ];

            $result = $this->exec_command($command_parts);
            if (0 === $result['return'])
            {
                return true;
            }

            //Check security status
            // The command returns 0 regardless of the SIM security state
            $command_parts = [
                'LC_ALL=C',
                'gammu',
                '--config',
                escapeshellarg($this->data['config_file']),
                'getsecuritystatus',
            ];

            $result = $this->exec_command($command_parts);

            if (0 !== $result['return'])
            {
                return false;
            }

            return $this->search_for_string($result['output'], 'nothing');
        }

        /**
         * Function to execute a command and transmit it to Gammu.
         *
         * @param array $command_parts : Commands parts to be join with a space
         *
         * @return array : ['return' => int:return code of command, 'output' => array:each raw is a line of the output]
         */
        private function exec_command(array $command_parts): array
        {
            //Add redirect of error to stdout
            $command_parts[] = '2>&1';

            $command = implode(' ', $command_parts);

            $output = [];
            $return_var = null;
            exec($command, $output, $return_var);

            return ['return' => (int) $return_var, 'output' => $output];
        }

        /**
         * Function to search a string in the output of an executer command.
         *
         * @param array  $output : Text to search in where each raw is a line
         * @param string $search : Text to search for
         *
         * @return bool : True if found, false else
         */
        private function search_for_string(array $output, string $search): bool
        {
            $find = false;
            foreach ($output as $line)
            {
                $find = mb_stristr($line, $search);
                if (false !== $find)
                {
                    break;
                }
            }

            return (bool) $find;
        }
    }
