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
     * Api to interact with raspisms
     */
    class Api extends \descartes\ApiController
    {
        CONST DEFAULT_RETURN = [
            'error' => 0, //Error code
            'message' => null, //Any message to describe a potential error
            'response' => null, //The content of the response
            'next' => null, //Link to the next results
            'prev' => null, //Link to the previous results
        ];

        CONST ERROR_CODES = [
            'NONE' => 0,
            'INVALID_CREDENTIALS' => 1,
            'INVALID_PARAMETER' => 2,
        ];

        CONST ERROR_MESSAGES = [
            'INVALID_CREDENTIALS' => 'Invalid API Key. Please provide a valid API as GET parameter "api_key".',
            'INVALID_PARAMETER' => 'You have specified an invalid parameter : ',
        ];
        

        private $internal_user;
        private $internal_phone;
        private $internal_received;
        private $internal_sended;
        private $internal_contact;
        private $internal_group;
        private $user;

        /**
         * Construct the object and quit if failed authentication
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
            $api_key = $_GET['api_key'] ?? false;
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
        }


        /**
         * List all entries of a certain type for the current user, sorted by id.
         * @param string $entry_type : Type of entries we want to list ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone']
         * @param int $page : Pagination number, Default = 0. Group of 25 results.
         * @return List of entries
         */
        public function get_entries (string $entry_type, int $page = 0)
        {
            $entry_types = ['sended', 'received', 'scheduled', 'contact', 'group', 'conditional_group', 'phone'];

            if (!in_array($entry_type, $entry_types))
            {
                $return = self::DEFAULT_RETURN;
                $return['error'] = self::ERROR_CODES['INVALID_PARAMETER'];
                $return['message'] = self::ERROR_MESSAGES['INVALID_PARAMETER'] . 'entry_type must be one of : ' . join(', ', $entry_types) . '.';
                $this->auto_http_code(false);
                $this->json($return);

                return false;
            }

            $controller_str = 'internal_' . $entry_type;
            $controller = $this->$controller_str;

            $page = (int) $page;
            $limit = 25;
            $entries = $controller->list_for_user($this->user['id'], $limit, $page);

            //Special case for scheduled, we must add numbers because its a join
            if ($entry_type === 'scheduled')
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
            elseif ($entry_type === 'group')
            {
                foreach ($entries as $key => $entry)
                {
                    $entries[$key]['contacts'] = $this->internal_group->get_contacts($entry['id']);
                }
            }


            $return = self::DEFAULT_RETURN;
            $return['response'] = $entries;

            if (count($entries) == $limit)
            {
                $return['next'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'page' => $page + 1], ['api_key' => $this->user['api_key']]);
            }

            if ($page > 0)
            {
                $return['next'] = \descartes\Router::url('Api', __FUNCTION__, ['entry_type' => $entry_type, 'page' => $page - 1], ['api_key' => $this->user['api_key']]);
            }

            $this->auto_http_code(true);
            $this->json($return);
        }

    }
