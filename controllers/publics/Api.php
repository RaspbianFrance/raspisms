<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    /**
     * Api to interact with raspisms.
     */
    class Api extends \descartes\ApiController
    {
        const DEFAULT_RETURN = [
            'error' => 0, //Error code
            'message' => null, //Any message to describe a potential error
            'response' => null, //The content of the response
            'next' => null, //Link to the next results
            'prev' => null, //Link to the previous results
        ];

        const ERROR_CODES = [
            'NONE' => 0,
            'INVALID_CREDENTIALS' => 1,
            'INVALID_PARAMETER' => 2,
            'MISSING_PARAMETER' => 4,
            'CANNOT_CREATE' => 8,
            'SUSPENDED_USER' => 16,
            'CANNOT_DELETE' => 32,
            'CANNOT_UPLOAD_FILE' => 64,
        ];

        const ERROR_MESSAGES = [
            'INVALID_CREDENTIALS' => 'Invalid API Key. Please provide a valid API key as GET or POST parameter "api_key" or a HTTP "X-Api-Key".',
            'INVALID_PARAMETER' => 'You have specified an invalid parameter : ',
            'MISSING_PARAMETER' => 'One require parameter is missing : ',
            'CANNOT_CREATE' => 'Cannot create a new entry.',
            'SUSPENDED_USER' => 'This user account is currently suspended.',
            'CANNOT_DELETE' => 'Cannot delete this entry.',
            'CANNOT_UPLOAD_FILE' => 'Failed to upload or save an uploaded file : ',
        ];

        private $internal_user;
        private $internal_phone;
        private $internal_received;
        private $internal_sended;
        private $internal_scheduled;
        private $internal_contact;
        private $internal_group;
        private $internal_conditional_group;
        private $internal_adapter;
        private $internal_media;
        private $internal_setting;
        private $user;

        /**
         * Construct the object and quit if failed authentication.
         *
         * @return void;
         */
        public function __construct()
        {
            parent::__construct();

            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_user = new \controllers\internals\User($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);
            $this->internal_received = new \controllers\internals\Received($bdd);
            $this->internal_sended = new \controllers\internals\Sended($bdd);
            $this->internal_scheduled = new \controllers\internals\Scheduled($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_group = new \controllers\internals\Group($bdd);
            $this->internal_conditional_group = new \controllers\internals\ConditionalGroup($bdd);
            $this->internal_adapter = new \controllers\internals\Adapter();
            $this->internal_media = new \controllers\internals\Media($bdd);
            $this->internal_setting = new \controllers\internals\Setting($bdd);

            //If no user, quit with error
            $this->user = false;
            $api_key = $_GET['api_key'] ?? $_POST['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? false;
            if ($api_key)
            {
                $this->user = $this->internal_user->get_by_api_key($api_key);
            }

            if (!$this->user)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_CREDENTIALS'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_CREDENTIALS'];
                $this->set_http_code(401);
                $this->json($return);

                exit(self::ERROR_CODES['INVALID_CREDENTIALS']);
            }

            $this->user['settings'] = $this->internal_setting->gets_for_user($this->user['id']);

            if (\models\User::STATUS_ACTIVE !== $this->user['status'])
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['SUSPENDED_USER'];
                $return['message'] = self::ERROR_MESSAGES['SUSPENDED_USER'];
                $this->set_http_code(403);
                $this->json($return);

                exit(self::ERROR_CODES['SUSPENDED_USER']);
            }
        }

        /**
         * List all entries of a certain type for the current user, sorted by id.
         *
         * @param string $entry_type : Type of entries we want to list ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone', 'media']
         * @param int    $page       : Pagination number, Default = 0. Group of 25 results.
         *
         * @return : List of entries
         */
        public function get_entries(string $entry_type, int $page = 0)
        {
            $entry_types = ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone', 'media'];

            if (!\in_array($entry_type, $entry_types, true))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'entry_type must be one of : ' . implode(', ', $entry_types) . '.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $controller_str = 'internal_' . $entry_type;
            $controller = $this->{$controller_str};

            $page = (int) $page;
            $limit = 25;
            $entries = $controller->list_for_user($this->user['id'], $limit, $page);

            //Special case for scheduled, we must add numbers because its a join
            if ('scheduled' === $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    $entries[$key]['numbers'] = $this->internal_scheduled->get_numbers($entry['id']);
                    $entries[$key]['contacts'] = $this->internal_scheduled->get_contacts($entry['id']);
                    $entries[$key]['groups'] = $this->internal_scheduled->get_groups($entry['id']);
                    $entries[$key]['conditional_groups'] = $this->internal_scheduled->get_conditional_groups($entry['id']);
                    $entries[$key]['medias'] = $this->internal_media->gets_for_scheduled($entry['id']);
                }
            }
            elseif ('received' === $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    $entries[$key]['medias'] = $this->internal_media->gets_for_received($entry['id']);
                }
            }
            elseif ('sended' === $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    $entries[$key]['medias'] = $this->internal_media->gets_for_sended($entry['id']);
                }
            }
            //Special case for group we must add contact because its a join
            elseif ('group' === $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    $entries[$key]['contacts'] = $this->internal_group->get_contacts($entry['id']);
                }
            }

            $return = self::DEFAULT_RETURN;
            $return['response'] = $entries;

            if (\count($entries) === $limit)
            {
                $return['next'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'page' => $page + 1], ['api_key' => $this->user['api_key']]);
            }

            if ($page > 0)
            {
                $return['prev'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'page' => $page - 1], ['api_key' => $this->user['api_key']]);
            }

            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Schedule a message to be send.
         *
         * @param string $_POST['at']                 : Date to send message at format Y-m-d H:i:s
         * @param string $_POST['text']               : Text of the message to send
         * @param string $_POST['id_phone']           : Default null. Id of phone to send the message from. If null use a random phone
         * @param string $_POST['flash']              : Default false. Is the sms a flash sms.
         * @param string $_POST['mms']                : Default false. Is the sms a mms.
         * @param string $_POST['numbers']            : Array of numbers to send message to
         * @param string $_POST['contacts']           : Array of ids of contacts to send message to
         * @param string $_POST['groups']             : Array of ids of groups to send message to
         * @param string $_POST['conditional_groups'] : Array of ids of conditional groups to send message to
         *
         * @return : Id of scheduled created
         */
        public function post_scheduled()
        {
            $at = $_POST['at'] ?? false;
            $text = $_POST['text'] ?? false;
            $id_phone = empty($_POST['id_phone']) ? null : $_POST['id_phone'];
            $flash = (bool) ($_POST['flash'] ?? false);
            $mms = (bool) ($_POST['mms'] ?? false);
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];
            $conditional_groups = $_POST['conditional_groups'] ?? [];
            $files = $_FILES['medias'] ?? false;

            $numbers = \is_array($numbers) ? $numbers : [$numbers];
            $contacts = \is_array($contacts) ? $contacts : [$contacts];
            $groups = \is_array($groups) ? $groups : [$groups];
            $conditional_groups = \is_array($conditional_groups) ? $conditional_groups : [$conditional_groups];

            //Iterate over files to re-create individual $_FILES array
            $files_arrays = [];

            if ($files === false)
            {
                $files_arrays = [];
            }
            elseif (!is_array($files['name'])) //Only one file uploaded
            {
                $files_arrays[] = $files;
            }
            else //multiple files
            {
                foreach ($files as $property_name => $files_values)
                {
                    foreach ($files_values as $file_key => $property_value)
                    {
                        if (!isset($files_arrays[$file_key]))
                        {
                            $files_arrays[$file_key] = [];
                        }

                        $files_arrays[$file_key][$property_name] = $property_value;
                    }
                }
            }

            $media_ids = [];

            if (!$at)
            {
                $at = (new \DateTime())->format('Y-m-d H:i:s');
            }

            if (!$at || !$text)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ($at ? '' : 'at ') . ($text ? '' : 'text');
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s'))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'at must be a date of format "Y-m-d H:i:s".';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if (($this->user['settings']['mms'] ?? false) && $mms)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'mms is set to true, but mms are disabled in settings.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            foreach ($numbers as $key => $number)
            {
                $number = \controllers\internals\Tool::parse_phone($number);

                if (!$number)
                {
                    unset($numbers[$key]);

                    continue;
                }

                $numbers[$key] = $number;
            }

            if (!$numbers && !$contacts && !$groups && !$conditional_groups)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . 'You must specify at least one valid number, contact, group or conditional_group.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $phone = null;
            if ($id_phone)
            {
                $phone = $this->internal_phone->get_for_user($this->user['id'], $id_phone);
            } 
            
            if ($id_phone && !$phone)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'id_phone : You must specify an id_phone number among thoses of user phones.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($mms)
            {
                foreach ($files_arrays as $file)
                {
                    try
                    {
                        $new_media_id = $this->internal_media->upload_and_create_for_user($this->user['id'], $file);
                    }
                    catch (\Exception $e)
                    {
                        $return = self::DEFAULT_RETURN;
                        $return['error'] = self::ERROR_CODES['CANNOT_CREATE'];
                        $return['message'] = self::ERROR_MESSAGES['CANNOT_CREATE'] . ' : Cannot upload and create media file ' . $file['name'] . ' : ' . $e->getMessage();
                        $this->auto_http_code(false);

                        return $this->json($return);
                    }

                    $media_ids[] = $new_media_id;
                }
            }

            $scheduled_id = $this->internal_scheduled->create($this->user['id'], $at, $text, $id_phone, $flash, $mms, $numbers, $contacts, $groups, $conditional_groups, $media_ids);
            if (!$scheduled_id)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['CANNOT_CREATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_CREATE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return = self::DEFAULT_RETURN;
            $return['response'] = $scheduled_id;
            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Delete a scheduled message.
         *
         * @param int $id : Id of scheduled message to delete
         *
         * @return bool : void
         */
        public function delete_scheduled(int $id)
        {
            $return = self::DEFAULT_RETURN;
            $success = $this->internal_scheduled->delete_for_user($this->user['id'], $id);

            if (!$success)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_DELETE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_DELETE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return['response'] = true;
            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Create a new phone.
         *
         * @param string $_POST['name']         : Phone name
         * @param string $_POST['adapter']      : Phone adapter
         * @param array  $_POST['adapter_data'] : Phone adapter data
         *
         * @return int : id phone the new phone on success
         */
        public function post_phone()
        {
            $return = self::DEFAULT_RETURN;

            $name = $_POST['name'] ?? false;
            $adapter = $_POST['adapter'] ?? false;
            $adapter_data = !empty($_POST['adapter_data']) ? $_POST['adapter_data'] : [];

            if (!$name)
            {
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ' You must specify phone name.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if (!$adapter)
            {
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ' You must specify adapter name.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $name_exist = $this->internal_phone->get_by_name($name);
            if ($name_exist)
            {
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' This name is already used for another phone.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $adapters = $this->internal_adapter->list_adapters();
            $find_adapter = false;
            foreach ($adapters as $metas)
            {
                if ($metas['meta_classname'] === $adapter)
                {
                    $find_adapter = $metas;

                    break;
                }
            }

            if (!$find_adapter)
            {
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' adapter. Adapter "' . $adapter . '" does not exists.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            //If missing required data fields, error
            foreach ($find_adapter['meta_data_fields'] as $field)
            {
                if (false === $field['required'])
                {
                    continue;
                }

                if (!empty($adapter_data[$field['name']]))
                {
                    continue;
                }

                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ' You must speicify param ' . $field['name'] . ' (' . $field['description'] . ') for this phone.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            //If field phone number is invalid
            foreach ($find_adapter['meta_data_fields'] as $field)
            {
                if (false === ($field['number'] ?? false))
                {
                    continue;
                }

                if (!empty($adapter_data[$field['name']]))
                {
                    $adapter_data[$field['name']] = \controllers\internals\Tool::parse_phone($adapter_data[$field['name']]);

                    if ($adapter_data[$field['name']])
                    {
                        continue;
                    }
                }

                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' field ' . $field['name'] . ' is not a valid phone number.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $adapter_data = json_encode($adapter_data);

            //Check adapter is working correctly with thoses names and data
            $adapter_classname = $find_adapter['meta_classname'];
            $adapter_instance = new $adapter_classname($adapter_data);
            $adapter_working = $adapter_instance->test();

            if (!$adapter_working)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_CREATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_CREATE'] . ' : Impossible to validate this phone, verify adapters parameters.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $phone_id = $this->internal_phone->create($this->user['id'], $name, $adapter, $adapter_data);
            if (false === $phone_id)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_CREATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_CREATE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return['response'] = $phone_id;
            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Delete a phone.
         *
         * @param int $id : Id of phond to delete
         *
         * @return bool : void
         */
        public function delete_phone(int $id)
        {
            $return = self::DEFAULT_RETURN;
            $success = $this->internal_phone->delete_for_user($this->user['id'], $id);

            if (!$success)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_DELETE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_DELETE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return['response'] = true;
            $this->auto_http_code(true);

            return $this->json($return);
        }
    }
