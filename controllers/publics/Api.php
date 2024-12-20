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
            'CANNOT_UPDATE' => 128,
        ];

        const ERROR_MESSAGES = [
            'INVALID_CREDENTIALS' => 'Invalid API Key. Please provide a valid API key as GET or POST parameter "api_key" or a HTTP "X-Api-Key".',
            'INVALID_PARAMETER' => 'You have specified an invalid parameter : ',
            'MISSING_PARAMETER' => 'One require parameter is missing : ',
            'CANNOT_CREATE' => 'Cannot create a new entry.',
            'SUSPENDED_USER' => 'This user account is currently suspended.',
            'CANNOT_DELETE' => 'Cannot delete this entry.',
            'CANNOT_UPLOAD_FILE' => 'Failed to upload or save an uploaded file : ',
            'CANNOT_UPDATE' => 'Cannot update this entry : ',
        ];

        private $internal_user;
        private $internal_phone;
        private $internal_phone_group;
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
            $this->internal_phone_group = new \controllers\internals\PhoneGroup($bdd);
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
            elseif ($_SESSION['user'] ?? false)
            {
                $this->user = $this->internal_user->get($_SESSION['user']['id']);
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
         * @param string $entry_type : Type of entries we want to list ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone', 'phone_group', 'media']
         * @param int    $page       : Pagination number, Default = 0. Group of 25 results.
         * @param ?int   $after_id   : If provided use where id > $after_id instead of offset based on page, more performant 
         * @param ?int   $before_id  : If provided use where id < $before_id instead of offset based on page, more performant
         *
         *
         * @return : List of entries
         */
        public function get_entries(string $entry_type, int $page = 0, ?int $after_id = null, ?int $before_id = null)
        {
            $entry_types = ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone', 'phone_group', 'media'];

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
            $entries = $controller->list_for_user($this->user['id'], $limit, $page, $after_id, $before_id);

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
            // Special case for phone as we might need to remove adapter_data for security reason
            elseif ('phone' == $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    if (!$entry['adapter']::meta_hide_data())
                    {
                        continue;
                    }

                    unset($entries[$key]['adapter_data']);
                }
            }
            // Special case for phone group we must add phones because its a join
            elseif ('phone_group' === $entry_type)
            {
                foreach ($entries as $key => $entry)
                {
                    $phones = $this->internal_phone_group->get_phones($entry['id']);
                    // Hide meta data of phones if needed
                    foreach ($phones as &$phone)
                    {
                        if (!$phone['adapter']::meta_hide_data())
                        {
                            continue;
                        }

                        unset($phone['adapter_data']);
                    }

                    $entries[$key]['phones'] = $phones;
                }
            }

            $return = self::DEFAULT_RETURN;
            $return['response'] = $entries;

            if (\count($entries) === $limit || ($entries && $before_id))
            {
                $last_entry = end($entries);
                $return['next'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'after_id' => $last_entry['id']], ['api_key' => $this->user['api_key']]);
            }

            if ($page > 0 || ($entries && ($after_id || $before_id)))
            {
                $first_entry = $entries[0];
                $return['prev'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'before_id' => $first_entry['id']], ['api_key' => $this->user['api_key']]);
            }

            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Return info about volume of sms sended for a period
         *
         * @param ?string $_POST['start']   : Date from which to get sms volume, format Y-m-d H:i:s. Default to null.
         * @param ?string $_POST['end']     : Date up to which to get sms volume, format Y-m-d H:i:s. Default to null.
         * @param ?string $_POST['tag']    : Tag to filter SMS by. If set, only sended sms with a matching tag will be counted. Default to null.
         *
         * @return : List of entries
         */
        public function get_usage()
        {
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $tag = $_GET['tag'] ?? null;

            $return = self::DEFAULT_RETURN;

            if ($start)
            {
                if (!\controllers\internals\Tool::validate_date($start, 'Y-m-d H:i:s'))
                {
                    $return = self::DEFAULT_RETURN;
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'start must be a date of format "Y-m-d H:i:s".';
                    $this->auto_http_code(false);

                    return $this->json($return);
                }

                $start = new \DateTime($start);
            }

            if ($end)
            {
                if (!\controllers\internals\Tool::validate_date($end, 'Y-m-d H:i:s'))
                {
                    $return = self::DEFAULT_RETURN;
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'end must be a date of format "Y-m-d H:i:s".';
                    $this->auto_http_code(false);

                    return $this->json($return);
                }

                $end = new \DateTime($end);
            }

            $total_sended = 0;
            $phones_volumes = [];

            $phones = $this->internal_phone->gets_for_user($this->user['id']);
            foreach ($phones as $phone)
            {
                $nb_sended = $this->internal_sended->count_since_for_phone_and_user($this->user['id'], $phone['id'], $start, $end, $tag);
                $total_sended += $nb_sended;
                $phones_volumes[$phone['id']] = $nb_sended;
            }

            $return['response'] = [
                'total' => $total_sended,
                'phones_volumes' => $phones_volumes,
            ];

            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Simplest method to send a SMS immediately with nothing but a URL and a GET query
         * @param string $_GET['to'] = Phone number to send sms to
         * @param string $_GET['text'] = Text of the SMS
         * @param ?int   $_GET['id_phone'] = Id of the phone to use, if null use a random phone
         */
        public function get_send_sms()
        {
            $to = \controllers\internals\Tool::parse_phone($_GET['to'] ?? '');
            $text = $_GET['text'] ?? false;
            $id_phone = empty($_GET['id_phone']) ? null : $_GET['id_phone'];

            if (!$to || !$text)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ($to ? '' : 'to ') . ($text ? '' : 'text');
                $this->auto_http_code(false);

                return $this->json($return);
            }
            $at = (new \DateTime())->format('Y-m-d H:i:s');


            $scheduled_id = $this->internal_scheduled->create(
                $this->user['id'], 
                $at, 
                $text, 
                $id_phone, 
                null,
                false,
                false,
                null,
                [['number' => $to, 'data' => '[]']]
            );
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
         * Schedule a message to be send.
         *
         * @param string $_POST['at']                 : Date to send message at format Y-m-d H:i:s
         * @param string $_POST['text']               : Text of the message to send
         * @param string $_POST['id_phone']           : Default null. Id of phone to send the message from. If null and id_phone_group null, use a random phone
         * @param string $_POST['id_phone_group']     : Default null. Id of phone group to send the message from. If null abd id_phone null, use a random phone
         * @param string $_POST['flash']              : Default false. Is the sms a flash sms.
         * @param string $_POST['mms']                : Default false. Is the sms a mms.
         * @param string $_POST['tag']                : Default null. Tag to associate to every sms of the campaign.
         * @param string $_POST['numbers']            : Array of numbers to send message to
         * @param string $_POST['contacts']           : Array of ids of contacts to send message to
         * @param string $_POST['groups']             : Array of ids of groups to send message to
         * @param string $_POST['conditional_groups'] : Array of ids of conditional groups to send message to
         * @param string $_POST['numbers_csv']        : CSV file with numbers and potentially data associated with numbers for templating to send the sms to
         *
         * @return : Id of scheduled created
         */
        public function post_scheduled()
        {
            $at = $_POST['at'] ?? false;
            $text = $_POST['text'] ?? false;
            $id_phone = empty($_POST['id_phone']) ? null : $_POST['id_phone'];
            $id_phone_group = empty($_POST['id_phone_group']) ? null : $_POST['id_phone_group'];
            $flash = (bool) ($_POST['flash'] ?? false);
            $mms = (bool) ($_POST['mms'] ?? false);
            $tag = $_POST['tag'] ?? null;
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];
            $conditional_groups = $_POST['conditional_groups'] ?? [];
            $files = $_FILES['medias'] ?? false;
            $csv_file = $_FILES['numbers_csv'] ?? false;

            $numbers = \is_array($numbers) ? $numbers : [$numbers];
            $contacts = \is_array($contacts) ? $contacts : [$contacts];
            $groups = \is_array($groups) ? $groups : [$groups];
            $conditional_groups = \is_array($conditional_groups) ? $conditional_groups : [$conditional_groups];

            //Iterate over files to re-create individual $_FILES array
            $files_arrays = [];

            if (false === $files)
            {
                $files_arrays = [];
            }
            elseif (!is_array($files['name']))
            { //Only one file uploaded
                $files_arrays[] = $files;
            }
            else
            { //multiple files
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

            if (!is_string($at))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' : at must be a string.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if (!is_string($text))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' : text must be a string.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if (mb_strlen($text) > \models\Scheduled::SMS_LENGTH_LIMIT)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' : text must be less than ' . \models\Scheduled::SMS_LENGTH_LIMIT . ' char.';
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

            $at = (string) $at;
            $text = (string) $text;

            if ($mms && !(int)($this->user['settings']['mms'] ?? false))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'mms is set to true, but mms are disabled in settings.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($csv_file)
            {
                $uploaded_file = \controllers\internals\Tool::read_uploaded_file($csv_file);
                if (!$uploaded_file['success'])
                {
                    $return = self::DEFAULT_RETURN;
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'csv : ' . $uploaded_file['content'];
                    $this->auto_http_code(false);

                    return $this->json($return);
                }

                try
                {
                    $csv_numbers = $this->internal_scheduled->parse_csv_numbers_file($uploaded_file['content'], true);
                    if (!$csv_numbers)
                    {
                        throw new \Exception('no valid number in csv file.');
                    }

                    foreach ($csv_numbers as $csv_number)
                    {
                        $csv_number['data'] = json_encode($csv_number['data']);
                        $numbers[] = $csv_number;
                    }
                }
                catch (\Exception $e)
                {
                    $return = self::DEFAULT_RETURN;
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'csv : ' . $e->getMessage();
                    $this->auto_http_code(false);

                    return $this->json($return);
                }
            }

            foreach ($numbers as $key => $number)
            {
                // If number is not an array turn it into an array
                $number = is_array($number) ? $number : ['number' => $number, 'data' => '[]'];
                $number['data'] = $number['data'] ?? '[]';
                $number['number'] = \controllers\internals\Tool::parse_phone($number['number'] ?? '');

                if (!$number['number'])
                {
                    unset($numbers[$key]);

                    continue;
                }

                if (null === json_decode($number['data']))
                {
                    $return = self::DEFAULT_RETURN;
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'number data must be a valid json or leave not set.';
                    $this->auto_http_code(false);

                    return $this->json($return);
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

            if ($id_phone && $id_phone_group)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'id_phone, id_phone_group : You must specify at most one of id_phone or id_phone_group, not both.';
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

            $phone_group = null;
            if ($id_phone_group)
            {
                $phone_group = $this->internal_phone_group->get_for_user($this->user['id'], $id_phone_group);
            }

            if ($id_phone_group && !$phone_group)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'id_phone_group : You must specify an id_phone_group number among thoses of user phone groups.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($mms)
            {
                foreach ($files_arrays as $file)
                {
                    try
                    {
                        $new_media_id = $this->internal_media->create_from_uploaded_file_for_user($this->user['id'], $file);
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

            $scheduled_id = $this->internal_scheduled->create($this->user['id'], $at, $text, $id_phone, $id_phone_group, $flash, $mms, $tag, $numbers, $contacts, $groups, $conditional_groups, $media_ids);
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
         * @param int    $priority              : Priority with which to use phone to send SMS. Default 0.
         * @param ?array $_POST['limits']       : Array of limits in number of SMS for a period to be applied to this phone.
         *
         * @return int : id phone the new phone on success
         */
        public function post_phone()
        {
            $return = self::DEFAULT_RETURN;

            $name = $_POST['name'] ?? false;
            $adapter = $_POST['adapter'] ?? false;
            $adapter_data = !empty($_POST['adapter_data']) ? $_POST['adapter_data'] : [];
            $priority = $_POST['priority'] ?? 0;
            $priority = max(((int) $priority), 0);
            $limits = $_POST['limits'] ?? [];
            $limits = is_array($limits) ? $limits : [$limits];

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

            $name_exist = $this->internal_phone->get_by_name_and_user($this->user['id'], $name);
            if ($name_exist)
            {
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' This name is already used for another phone.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($limits)
            {
                foreach ($limits as $key => $limit)
                {
                    if (!is_array($limit))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                    
                    $startpoint = $limit['startpoint'] ?? false;
                    $volume = $limit['volume'] ?? false;

                    if (!$startpoint || !$volume)
                    {
                        unset($limits[$key]);
                        continue;
                    }

                    $volume = (int) $volume;
                    $limits[$key]['volume'] = max($volume, 1);

                    if (!\controllers\internals\Tool::validate_relative_date($startpoint))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                }
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

            $phone_id = $this->internal_phone->create($this->user['id'], $name, $adapter, $adapter_data, $priority, $limits);
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
         * Update an existing phone.
         *
         * @param int $id : Id of phone to update
         * @param string (optionnal) $_POST['name']         : New phone name
         * @param string (optionnal) $_POST['adapter']      : New phone adapter
         * @param array  (optionnal) $_POST['adapter_data'] : New phone adapter data
         * @param int         $priority     : Priority with which to use phone to send SMS. Default 0.
         *
         * @return int : id phone the new phone on success
         */
        public function post_update_phone(int $id)
        {
            $return = self::DEFAULT_RETURN;

            $phone = $this->internal_phone->get_for_user($this->user['id'], $id);
            if (!$phone)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'] . ' No phone with this id.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $limits = $this->internal_phone->get_limits(($phone['id']));

            $name = $_POST['name'] ?? $phone['name'];
            $priority = $_POST['priority'] ?? $phone['priority'];
            $priority = max(((int) $priority), 0);
            $adapter = $_POST['adapter'] ?? $phone['adapter'];
            $adapter_data = !empty($_POST['adapter_data']) ? $_POST['adapter_data'] : json_decode($phone['adapter_data'], true);
            $adapter_data = is_array($adapter_data) ? $adapter_data : [$adapter_data];
            $limits = $_POST['limits'] ?? $limits;
            $limits = is_array($limits) ? $limits : [$limits];


            if (!$name && !$adapter && !$adapter_data)
            {
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . ' You must specify at least one name, adapter or adapter_data.';
                $this->auto_http_code(false);

                return $this->json($return);
            }


            $phone_with_same_name = $this->internal_phone->get_by_name_and_user($this->user['id'], $name);
            if ($phone_with_same_name && $phone_with_same_name['id'] != $phone['id'])
            {
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' This name is already used for another phone.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($limits)
            {
                foreach ($limits as $key => $limit)
                {
                    if (!is_array($limit))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                    
                    $startpoint = $limit['startpoint'] ?? false;
                    $volume = $limit['volume'] ?? false;

                    if (!$startpoint || !$volume)
                    {
                        unset($limits[$key]);
                        continue;
                    }

                    $volume = (int) $volume;
                    $limits[$key]['volume'] = max($volume, 1);

                    if (!\controllers\internals\Tool::validate_relative_date($startpoint))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                }
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

            $adapter_data_json = json_encode($adapter_data);

            //Check adapter is working correctly with thoses names and data
            $adapter_classname = $find_adapter['meta_classname'];
            $adapter_instance = new $adapter_classname($adapter_data_json);
            $adapter_working = $adapter_instance->test();

            if (!$adapter_working)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'] . ' : Impossible to validate this phone, verify adapters parameters.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $success = $this->internal_phone->update_for_user($this->user['id'], $phone['id'], $name, $adapter, $adapter_data_json, $priority, $limits);
            if (!$success)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return['response'] = $success;
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

        /**
         * Trigger re-checking of a phone status
         *
         * @param int $id : Id of phone to re-check status
         */
        public function post_update_phone_status ($id)
        {
            $return = self::DEFAULT_RETURN;

            $phone = $this->internal_phone->get_for_user($this->user['id'], $id);
            if (!$phone)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            if ($phone['status'] === \models\Phone::STATUS_DISABLED)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'] . 'Phone have been manually disabled, you need to re-enable it manually.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            // If user have activated phone limits, check if RaspiSMS phone limit have already been reached
            $limit_reached = false;
            if ((int) ($this->user['settings']['phone_limit'] ?? false))
            {
                $limits = $this->internal_phone->get_limits($id);

                $remaining_volume = PHP_INT_MAX;
                foreach ($limits as $limit)
                {
                    $startpoint = new \DateTime($limit['startpoint']);
                    $consumed = $this->internal_sended->count_since_for_phone_and_user($this->user['id'], $id, $startpoint);
                    $remaining_volume = min(($limit['volume'] - $consumed), $remaining_volume);
                }

                if ($remaining_volume < 1)
                {
                    $limit_reached = true;
                }
            }

            if ($limit_reached)
            {
                $new_status = \models\Phone::STATUS_LIMIT_REACHED;
            }
            else
            {
                //Check status on provider side 
                $adapter_classname = $phone['adapter'];
                $adapter_instance = new $adapter_classname($phone['adapter_data']);
                $new_status = $adapter_instance->check_phone_status();
            }

            $status_update = $this->internal_phone->update_status($id, $new_status);
            $return['response'] = $new_status;
            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Manually disable/enable phones
         * @param int id : id of phone we want to update status
         * @param string $_POST['new_status'] : New status of the phone, either 'disabled' or 'available'
         * @param $csrf : CSRF token
         */
        public function post_change_phone_status ($id)
        {
            $new_status = $_POST['status'] ?? '';

            if (!in_array($new_status, [\models\Phone::STATUS_AVAILABLE, \models\Phone::STATUS_DISABLED]))
            {
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . ' "status" must be "disabled" or "available".';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $phone = $this->internal_phone->get_for_user($this->user['id'], $id);
            if (!$phone)
            {
                $return['error'] = self::ERROR_CODES['CANNOT_UPDATE'];
                $return['message'] = self::ERROR_MESSAGES['CANNOT_UPDATE'];
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $status_update = $this->internal_phone->update_status($id, $new_status);
            $return['response'] = $new_status;
            $this->auto_http_code(true);

            return $this->json($return);
        }


        /**
         * Return statistics about status of sended sms for a period by phone
         *
         * @param string $_GET['start']   : Date from which to get sms volume, format Y-m-d H:i:s.
         * @param string $_GET['end']     : Date up to which to get sms volume, format Y-m-d H:i:s.
         * @param ?int $_GET['id_phone']  : Id of the phone we want to check the status for. Default to null will return stats for all phone.
         *
         * @return : List of entries
         */
        public function get_sms_status_stats()
        {
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $id_phone = $_GET['id_phone'] ?? null;

            if (!$start || !$end)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . 'start and end date are required.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $return = self::DEFAULT_RETURN;

            if (!\controllers\internals\Tool::is_valid_date($start))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'start must be a date of format "Y-m-d H:i:s".';
                $this->auto_http_code(false);

                return $this->json($return);
            }
            $start = new \DateTime($start);

            if (!\controllers\internals\Tool::is_valid_date($end))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'end must be a date of format "Y-m-d H:i:s".';
                $this->auto_http_code(false);

                return $this->json($return);
            }
            $end = new \DateTime($end);

            if ($id_phone)
            {
                $phone = $this->internal_phone->get_for_user($this->user['id'], $id_phone);
                if (!$phone)
                {
                    $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                    $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'phone with id ' . $id_phone . ' does not exists.';
                    $this->auto_http_code(false);

                    return $this->json($return);
                }
            }
            
            $stats = $this->internal_sended->get_sended_status_stats($this->user['id'], $start, $end, $id_phone);

            $return = self::DEFAULT_RETURN;
            $return['response'] = $stats;
            $this->auto_http_code(true);

            return $this->json($return);
        }

        /**
         * Return statistics about invalid numbers
         *
         * @param int $page                    : Pagination number, Default = 0. Group of 25 results.
         * @param int $_GET['volume']          : Minimum number of SMS sent to the number
         * @param int $_GET['percent_failed']  : Minimum percentage of failed SMS to the number
         * @param int $_GET['percent_unknown'] : Minimum percentage of unknown SMS to the number
         *
         * @return : List of entries
         */
        public function get_invalid_numbers($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $volume = $_GET['volume'] ?? false;
            $percent_failed = $_GET['percent_failed'] ?? false;
            $percent_unknown = $_GET['percent_unknown'] ?? false;

            if ($volume === false || $percent_failed === false || $percent_unknown === false)
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['MISSING_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['MISSING_PARAMETER'] . 'volume, percent_failed and percent_unknown are required.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $volume = (int) $volume;
            $percent_failed = ((float) $percent_failed) / 100;
            $percent_unknown = ((float) $percent_unknown) / 100;

            $return = self::DEFAULT_RETURN;
            
            $invalid_numbers = $this->internal_sended->get_invalid_numbers($this->user['id'], $volume, $percent_failed, $percent_unknown, $limit, $page);

            $return = self::DEFAULT_RETURN;

            if (\count($invalid_numbers) === $limit)
            {
                $return['next'] = \descartes\Router::url('Api', __FUNCTION__, ['page' => $page + 1], [
                    'api_key' => $this->user['api_key'], 
                    'volume' => $volume, 
                    'percent_failed' => $percent_failed * 100, 
                    'percent_unknown' => $percent_unknown * 100
                ]);    
            }

            if ($page > 0)
            {
                $return['prev'] = \descartes\Router::url('Api', __FUNCTION__, ['page' => $page - 1], [
                    'api_key' => $this->user['api_key'], 
                    'volume' => $volume, 
                    'percent_failed' => $percent_failed * 100, 
                    'percent_unknown' => $percent_unknown * 100
                ]);
            }

            $return['response'] = $invalid_numbers;
            $this->auto_http_code(true);

            return $this->json($return, false);
        }
    }
