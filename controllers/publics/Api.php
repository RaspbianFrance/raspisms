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
        ];

        const ERROR_MESSAGES = [
            'INVALID_CREDENTIALS' => 'Invalid API Key. Please provide a valid API key as GET or POST parameter "api_key".',
            'INVALID_PARAMETER' => 'You have specified an invalid parameter : ',
            'MISSING_PARAMETER' => 'One require parameter is missing : ',
            'CANNOT_CREATE' => 'Cannot create a new entry.',
            'SUSPENDED_USER' => 'This user account is currently suspended.',
            'CANNOT_DELETE' => 'Cannot delete this entry.',
        ];

        private $internal_user;
        private $internal_phone;
        private $internal_received;
        private $internal_sended;
        private $internal_scheduled;
        private $internal_contact;
        private $internal_group;
        private $internal_conditional_group;
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
                $this->auto_http_code(false);
                $this->json($return);

                exit(self::ERROR_CODES['INVALID_CREDENTIALS']);
            }

            if (\models\User::STATUS_ACTIVE !== $this->user['status'])
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['SUSPENDED_USER'];
                $return['message'] = self::ERROR_MESSAGES['SUSPENDED_USER'];
                $this->auto_http_code(false);
                $this->json($return);

                exit(self::ERROR_CODES['SUSPENDED_USER']);
            }
        }

        /**
         * List all entries of a certain type for the current user, sorted by id.
         *
         * @param string $entry_type : Type of entries we want to list ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone']
         * @param int    $page       : Pagination number, Default = 0. Group of 25 results.
         *
         * @return : List of entries
         */
        public function get_entries(string $entry_type, int $page = 0)
        {
            $entry_types = ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone'];

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
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];
            $conditional_groups = $_POST['conditional_groups'] ?? [];

            $numbers = \is_array($numbers) ? $numbers : [$numbers];
            $contacts = \is_array($contacts) ? $contacts : [$contacts];
            $groups = \is_array($groups) ? $groups : [$groups];
            $conditional_groups = \is_array($conditional_groups) ? $conditional_groups : [$conditional_groups];

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

            if ($id_phone && !$this->internal_phone->get_for_user($this->user['id'], $id_phone))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'id_phone : You must specify an id_phone number among thoses of user phones.';
                $this->auto_http_code(false);

                return $this->json($return);
            }

            $scheduled_id = $this->internal_scheduled->create($this->user['id'], $at, $text, $id_phone, $flash, $numbers, $contacts, $groups, $conditional_groups);
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
    }
