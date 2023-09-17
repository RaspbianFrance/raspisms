<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

    class Scheduled extends StandardController
    {
        protected $model;

        /**
         * Create a scheduled.
         *
         * @param int $id_user : User to insert scheduled for
         * @param $at : Scheduled date to send
         * @param string  $text                  : Text of the message
         * @param ?int    $id_phone              : Id of the phone to send message with, null by default
         * @param ?int    $id_phone_group        : Id of the phone group to send message with, null by default
         * @param bool    $flash                 : Is the sms a flash sms, by default false
         * @param bool    $mms                   : Is the sms a mms, by default false
         * @param ?string $tag                   : A string tag to associate to sended SMS
         * @param array   $numbers               : Array of numbers to send message to, a number is an array ['number' => '+33XXX', 'data' => '{"key":"value", ...}']
         * @param array   $contacts_ids          : Contact ids to send message to
         * @param array   $groups_ids            : Group ids to send message to
         * @param array   $conditional_group_ids : Conditional Groups ids to send message to
         * @param array   $media_ids             : Ids of the medias to link to scheduled message
         *
         * @return bool : false on error, new id on success
         */
        public function create(int $id_user, $at, string $text, ?int $id_phone = null, ?int $id_phone_group = null, bool $flash = false, bool $mms = false, ?string $tag = null, array $numbers = [], array $contacts_ids = [], array $groups_ids = [], array $conditional_group_ids = [], array $media_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'id_phone' => $id_phone,
                'id_phone_group' => $id_phone_group,
                'flash' => $flash,
                'mms' => $mms,
                'tag' => $tag,
            ];

            if ('' === $text)
            {
                return false;
            }

            if (null !== $id_phone)
            {
                $internal_phone = new Phone($this->bdd);
                $find_phone = $internal_phone->get_for_user($id_user, $id_phone);

                if (!$find_phone)
                {
                    return false;
                }
            }

            if (null !== $id_phone_group)
            {
                $internal_phone_group = new PhoneGroup($this->bdd);
                $find_phone_group = $internal_phone_group->get_for_user($id_user, $id_phone_group);

                if (!$find_phone_group)
                {
                    return false;
                }
            }

            //Use transaction to garanty atomicity
            $this->bdd->beginTransaction();

            $id_scheduled = $this->get_model()->insert($scheduled);
            if (!$id_scheduled)
            {
                $this->bdd->rollBack();

                return false;
            }

            $internal_media = new Media($this->bdd);
            foreach ($media_ids as $media_id)
            {
                $id_media_scheduled = $internal_media->link_to($media_id, 'scheduled', $id_scheduled);
                if (!$id_media_scheduled)
                {
                    $this->bdd->rollBack();

                    return false;
                }
            }

            foreach ($numbers as $number)
            {
                $this->get_model()->insert_scheduled_number($id_scheduled, $number['number'], $number['data']);
            }

            $internal_contact = new Contact($this->bdd);
            foreach ($contacts_ids as $contact_id)
            {
                $find_contact = $internal_contact->get_for_user($id_user, $contact_id);
                if (!$find_contact)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_contact_relation($id_scheduled, $contact_id);
            }

            $internal_group = new Group($this->bdd);
            foreach ($groups_ids as $group_id)
            {
                $find_group = $internal_group->get_for_user($id_user, $group_id);
                if (!$find_group)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_group_relation($id_scheduled, $group_id);
            }

            $internal_conditional_group = new ConditionalGroup($this->bdd);
            foreach ($conditional_group_ids as $conditional_group_id)
            {
                $find_group = $internal_conditional_group->get_for_user($id_user, $conditional_group_id);
                if (!$find_group)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_conditional_group_relation($id_scheduled, $conditional_group_id);
            }

            $success = $this->bdd->commit();
            if (!$success)
            {
                return false;
            }

            $date = date('Y-m-d H:i:s');
            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'SCHEDULED_ADD', 'Ajout d\'un Sms pour le ' . $date . '.');

            return $id_scheduled;
        }

        /**
         * Update a scheduled.
         *
         * @param int $id_user      : User to insert scheduled for
         * @param int $id_scheduled : Scheduled id
         * @param $at : Scheduled date to send
         * @param string $text                  : Text of the message
         * @param ?int   $id_phone              : Id of the phone to send message with, null by default
         * @param ?int   $id_phone_group        : Id of the phone group to send message with, null by default
         * @param bool   $flash                 : Is the sms a flash sms, by default false
         * @param bool   $mms                   : Is the sms a mms, by default false
         * @param ?string $tag                   : A string tag to associate to sended SMS
         * @param array  $numbers               : Array of numbers to send message to, a number is an array ['number' => '+33XXX', 'data' => '{"key":"value", ...}']
         * @param array  $contacts_ids          : Contact ids to send message to
         * @param array  $groups_ids            : Group ids to send message to
         * @param array  $conditional_group_ids : Conditional Groups ids to send message to
         * @param array  $media_ids             : Ids of the medias to link to scheduled message
         *
         * @return bool : false on error, true on success
         */
        public function update_for_user(int $id_user, int $id_scheduled, $at, string $text, ?int $id_phone = null, ?int $id_phone_group = null, bool $flash = false, bool $mms = false, ?string $tag = null, array $numbers = [], array $contacts_ids = [], array $groups_ids = [], array $conditional_group_ids = [], array $media_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'id_phone' => $id_phone,
                'id_phone_group' => $id_phone_group,
                'mms' => $mms,
                'flash' => $flash,
                'tag' => $tag,
            ];

            if (null !== $id_phone)
            {
                $internal_phone = new Phone($this->bdd);
                $find_phone = $internal_phone->get_for_user($id_user, $id_phone);

                if (!$find_phone)
                {
                    return false;
                }
            }

            if (null !== $id_phone_group)
            {
                $internal_phone_group = new PhoneGroup($this->bdd);
                $find_phone_group = $internal_phone_group->get_for_user($id_user, $id_phone_group);

                if (!$find_phone_group)
                {
                    return false;
                }
            }

            //Ensure atomicity
            $this->bdd->beginTransaction();

            $success = (bool) $this->get_model()->update_for_user($id_user, $id_scheduled, $scheduled);

            $this->get_model()->delete_scheduled_numbers($id_scheduled);
            $this->get_model()->delete_scheduled_contact_relations($id_scheduled);
            $this->get_model()->delete_scheduled_group_relations($id_scheduled);
            $this->get_model()->delete_scheduled_conditional_group_relations($id_scheduled);
            $internal_media = new Media($this->bdd);
            $internal_media->unlink_all_of('scheduled', $id_scheduled);

            foreach ($media_ids as $media_id)
            {
                $id_media_scheduled = $internal_media->link_to($media_id, 'scheduled', $id_scheduled);
                if (!$id_media_scheduled)
                {
                    $this->bdd->rollBack();

                    return false;
                }
            }

            foreach ($numbers as $number)
            {
                $this->get_model()->insert_scheduled_number($id_scheduled, $number['number'], $number['data']);
            }

            $internal_contact = new Contact($this->bdd);
            foreach ($contacts_ids as $contact_id)
            {
                $find_contact = $internal_contact->get_for_user($id_user, $contact_id);
                if (!$find_contact)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_contact_relation($id_scheduled, $contact_id);
            }

            $internal_group = new Group($this->bdd);
            foreach ($groups_ids as $group_id)
            {
                $find_group = $internal_group->get_for_user($id_user, $group_id);
                if (!$find_group)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_group_relation($id_scheduled, $group_id);
            }

            $internal_conditional_group = new ConditionalGroup($this->bdd);
            foreach ($conditional_group_ids as $conditional_group_id)
            {
                $find_group = $internal_conditional_group->get_for_user($id_user, $conditional_group_id);
                if (!$find_group)
                {
                    continue;
                }

                $this->get_model()->insert_scheduled_conditional_group_relation($id_scheduled, $conditional_group_id);
            }

            return $this->bdd->commit();
        }

        /**
         * Get messages scheduled before a date for a number and a user.
         *
         * @param int $id_user : User id
         * @param $date : Date before which we want messages
         * @param string $number : Number for which we want messages
         *
         * @return array
         */
        public function gets_before_date_for_number_and_user(int $id_user, $date, string $number)
        {
            return $this->get_model()->gets_before_date_for_number_and_user($id_user, $date, $number);
        }

        /**
         * Get messages scheduled after a date for a number and a user.
         *
         * @param int $id_user : User id
         * @param $date : Date after which we want messages
         * @param string $number : Number for which we want messages
         *
         * @return array
         */
        public function gets_after_date_for_number_and_user(int $id_user, $date, string $number)
        {
            return $this->get_model()->gets_after_date_for_number_and_user($id_user, $date, $number);
        }

        /**
         * Parse a CSV file of numbers, potentially associated with datas.
         *
         * @param resource $file_handler : File handler pointing to the file
         *
         * @throws Exception : raise exception if file is not valid
         *
         * @return mixed : array of numbers ['number' => '+XXXX...', 'data' => ['key' => 'value', ...]]
         */
        public function parse_csv_numbers_file($file_handler)
        {
            $numbers = [];

            $head = null;
            $line_nb = 0;
            while ($line = fgetcsv($file_handler))
            {
                ++$line_nb;
                if (null === $head)
                {
                    $head = $line;

                    continue;
                }

                //Padding line with '' entries to make sure its same length as head
                //this allow to mix users with data with users without data
                $line = array_pad($line, \count($head), '');

                $line = array_combine($head, $line);
                if (false === $line)
                {
                    continue;
                }

                $phone_number = \controllers\internals\Tool::parse_phone($line[array_keys($line)[0]] ?? '');
                if (!$phone_number)
                {
                    throw new \Exception('Erreur à la ligne ' . $line_nb . ' colonne 1, numéro de téléphone invalide.');
                }

                $data = [];
                $i = 0;
                foreach ($line as $key => $value)
                {
                    ++$i;
                    if ($i < 2)
                    { // Ignore first column
                        continue;
                    }

                    if ('' === $value)
                    {
                        continue;
                    }

                    $key = mb_ereg_replace('[\W]', '', $key);
                    $data[$key] = $value;
                }

                $numbers[] = ['number' => $phone_number, 'data' => $data];
            }

            return $numbers;
        }

        /**
         * Return numbers for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_numbers(int $id_scheduled)
        {
            return $this->get_model()->get_numbers($id_scheduled);
        }

        /**
         * Return contacts for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_contacts(int $id_scheduled)
        {
            return $this->get_model()->get_contacts($id_scheduled);
        }

        /**
         * Return groups for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_groups(int $id_scheduled)
        {
            return $this->get_model()->get_groups($id_scheduled);
        }

        /**
         * Return conditional groups for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_conditional_groups(int $id_scheduled)
        {
            return $this->get_model()->get_conditional_groups($id_scheduled);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\Scheduled
        {
            $this->model = $this->model ?? new \models\Scheduled($this->bdd);

            return $this->model;
        }


        /**
         * Get all messages to send and the number to use to send theme.
         *
         * @return array : List of smss to send at this time per scheduled id ['1' => [['id_scheduled', 'text', 'id_phone', 'destination', 'flash', 'mms', 'medias'], ...], ...]
         */
        public function get_smss_to_send()
        {
            $sms_per_scheduled = [];

            $internal_templating = new \controllers\internals\Templating();
            $internal_setting = new \controllers\internals\Setting($this->bdd);
            $internal_group = new \controllers\internals\Group($this->bdd);
            $internal_conditional_group = new \controllers\internals\ConditionalGroup($this->bdd);
            $internal_phone = new \controllers\internals\Phone($this->bdd);
            $internal_phone_group = new \controllers\internals\PhoneGroup($this->bdd);
            $internal_smsstop = new \controllers\internals\SmsStop($this->bdd);
            $internal_sended = new \controllers\internals\Sended($this->bdd);

            $users_smsstops = [];
            $users_settings = [];
            $users_phones = [];
            $users_phone_groups = [];
            $shortlink_cache = [];  

            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $scheduleds = $this->get_model()->gets_before_date($now);
            foreach ($scheduleds as $scheduled)
            {
                $id_scheduled = $scheduled['id'];
                $id_user = $scheduled['id_user'];

                $sms_per_scheduled[$id_scheduled] = [];

                // Forge cache of data about users, sms stops, phones, etc.
                if (!isset($users_settings[$id_user]))
                {
                    $users_settings[$id_user] = [];

                    $settings = $internal_setting->gets_for_user($id_user);
                    foreach ($settings as $name => $value)
                    {
                        $users_settings[$id_user][$name] = $value;
                    }
                }

                if (!isset($users_smsstops[$id_user]) && $users_settings[$id_user]['smsstop'])
                {
                    $users_smsstops[$id_user] = [];

                    $smsstops = $internal_smsstop->gets_for_user($id_user);
                    foreach ($smsstops as $smsstop)
                    {
                        $users_smsstops[$id_user][] = $smsstop['number'];
                    }
                }

                if (!isset($users_phones[$id_user]))
                {
                    $users_phones[$id_user] = [];

                    $phones = $internal_phone->gets_for_user($id_user);
                    foreach ($phones as &$phone)
                    {
                        $limits = $internal_phone->get_limits($phone['id']);

                        $remaining_volume = PHP_INT_MAX;
                        foreach ($limits as $limit)
                        {
                            $startpoint = new \DateTime($limit['startpoint']);
                            $consumed = $internal_sended->count_since_for_phone_and_user($id_user, $phone['id'], $startpoint);
                            $remaining_volume = min(($limit['volume'] - $consumed), $remaining_volume);
                        }

                        $phone['remaining_volume'] = $remaining_volume;
                        $users_phones[$id_user][$phone['id']] = $phone;
                    }
                }

                if (!isset($users_phone_groups[$id_user]))
                {
                    $users_phone_groups[$id_user] = [];

                    $phone_groups = $internal_phone_group->gets_for_user($id_user);
                    foreach ($phone_groups as $phone_group)
                    {
                        $phones = $internal_phone_group->get_phones($phone_group['id']);
                        $phone_group['phones'] = [];
                        foreach ($phones as $phone) 
                        {
                            $phone_group['phones'][] = $phone['id'];
                        }

                        $users_phone_groups[$id_user][$phone_group['id']] = $phone_group;
                    }
                }

                //Add medias to mms
                $scheduled['medias'] = [];
                if ($scheduled['mms'])
                {
                    $internal_media = new Media($this->bdd);
                    $scheduled['medias'] = $internal_media->gets_for_scheduled($id_scheduled);
                }

                $phone_to_use = null;
                if ($scheduled['id_phone'])
                {
                    $phone_to_use = $users_phones[$id_user][$scheduled['id_phone']] ?? null;
                }

                $phone_group_to_use = null;
                if ($scheduled['id_phone_group'])
                {
                    $phone_group_to_use = $users_phone_groups[$id_user][$scheduled['id_phone_group']] ?? null;
                }


                // We turn all contacts, groups and conditional groups into just contacts
                $contacts = $this->get_contacts($id_scheduled);

                $groups = $this->get_groups($id_scheduled);
                foreach ($groups as $group)
                {
                    $contacts_to_add = $internal_group->get_contacts($group['id']);
                    $contacts = array_merge($contacts, $contacts_to_add);
                }

                $conditional_groups = $this->get_conditional_groups($id_scheduled);
                foreach ($conditional_groups as $conditional_group)
                {
                    $contacts_to_add = $internal_conditional_group->get_contacts_for_condition_and_user($id_user, $conditional_group['condition']);
                    $contacts = array_merge($contacts, $contacts_to_add);
                }


                // We turn all numbers and contacts into simple targets with number, data and meta so we can forge all messages from onlye one data source
                $targets = [];
                
                $numbers = $this->get_numbers($id_scheduled);
                foreach ($numbers as $number)
                {
                    $metas = ['number' => $number['number']];
                    $targets[] = [
                        'number' => $number['number'],
                        'data' => $number['data'],
                        'metas' => $metas,
                    ];
                }

                foreach ($contacts as $contact)
                {
                    $metas = $contact;
                    unset($metas['data'], $metas['id_user']);

                    $targets[] = [
                        'number' => $contact['number'],
                        'data' => $contact['data'],
                        'metas' => $metas,
                    ];
                }

                
                // Pass on all targets to deduplicate destinations, remove number in sms stops, etc.
                $used_destinations = [];
                foreach ($targets as $key => $target)
                {
                    if (in_array($target['number'], $used_destinations))
                    {
                        unset($targets[$key]);
                        continue;
                    }

                    //Remove messages to smsstops numbers
                    if (($users_smsstops[$id_user] ?? false) && in_array($target['number'], $users_smsstops[$id_user]))
                    {
                        continue;
                    }

                    $used_destinations[] = $target['number'];
                }
                
                
                // Finally, we forge all messages and select phone to use
                foreach ($targets as $target)
                {
                    // Forge message if templating enable
                    $text = $scheduled['text'];
                    if ((int) ($users_settings[$id_user]['templating'] ?? false)) // Cast to int because it is more reliable than bool on strings
                    {
                        $target['data'] = json_decode($target['data'], true);
                        $data = ['contact' => $target['data'], 'contact_metas' => $target['metas']];

                        $render = $internal_templating->render($scheduled['text'], $data);

                        if (!$render['success'])
                        {
                            continue;
                        }

                        $text = $render['result'];
                    }

                    // Ignore empty messages
                    if ('' === trim($text) && !$scheduled['medias'])
                    {
                        continue;
                    }

                    // If we must force GSM 7 alphabet
                    if ((int) ($users_settings[$id_user]['force_gsm_alphabet'] ?? false))
                    {
                        $text = Tool::convert_to_gsm0338($text);
                    }

                    // If the text contain http links we must replace them
                    if (ENABLE_URL_SHORTENER && ((int) ($users_settings[$id_user]['shorten_url'] ?? false)))
                    {
                        $http_links = Tool::search_http_links($text);
                        if ($http_links !== false)
                        {
                            foreach ($http_links as $http_link)
                            {
                                if (!array_key_exists($http_link, $shortlink_cache))
                                {
                                    $shortlkink = LinkShortener::shorten($http_link);

                                    // If link shortening failed, keep original one 
                                    if ($shortlkink === false)
                                    {
                                        continue;
                                    }

                                    $shortlink_cache[$http_link] = $shortlkink;
                                }

                                $shortlink = $shortlink_cache[$http_link];
                                $text = str_replace($http_link, $shortlink, $text);
                            }
                        }
                    }

                    /*
                        Choose phone if no phone defined for message
                        Phones are choosen using type, priority and remaining volume :
                            1 - If sms is a mms, try to use mms phone if any available. If mms phone available use mms phone, else use default.
                            2 - In group of phones, keep only phones with remaining volume. If no phones with remaining volume, use all phones instead.
                            3 - Groupe keeped phones by priority get group with biggest priority.
                            4 - Get a random phone in this group.
                            5 - If their is no phone matching, keep phone at null so sender will directly mark it as failed
                    */
                    $random_phone = null;
                    if (null === $phone_to_use)
                    {
                        $phones_subset = $users_phones[$id_user];

                        if ($phone_group_to_use)
                        {
                            $phones_subset = array_filter($phones_subset, function ($phone) use ($phone_group_to_use) {
                                return in_array($phone['id'], $phone_group_to_use['phones']);
                            });
                        }

                        if ($scheduled['mms'])
                        {
                            $mms_only = array_filter($phones_subset, function ($phone) {
                                return $phone['adapter']::meta_support_mms_sending();
                            });

                            $phones_subset = $mms_only ?: $phones_subset;
                        }

                        // Keep only available phones
                        $remaining_volume_phones = array_filter($phones_subset, function ($phone) {
                            return $phone['status'] == \models\Phone::STATUS_AVAILABLE;
                        });
                        $phones_subset = $remaining_volume_phones ?: $phones_subset;

                        
                        // Keep only phones with remaining volume
                        if ((int) ($users_settings[$id_user]['phone_limit'] ?? false))
                        {
                            $remaining_volume_phones = array_filter($phones_subset, function ($phone) {
                                return $phone['remaining_volume'] > 0;
                            });
                            $phones_subset = $remaining_volume_phones ?: $phones_subset;
                        }

                        if ((int) ($users_settings[$id_user]['phone_priority'] ?? false))
                        {
                            $max_priority_phones = [];
                            $max_priority = PHP_INT_MIN;
                            foreach ($phones_subset as $phone)
                            {
                                if ($phone['priority'] < $max_priority)
                                {
                                    continue;
                                }
                                elseif ($phone['priority'] == $max_priority)
                                {
                                    $max_priority_phones[] = $phone;
                                }
                                elseif ($phone['priority'] > $max_priority)
                                {
                                    $max_priority_phones = [$phone];
                                    $max_priority = $phone['priority'];
                                }
                            }

                            $phones_subset = $max_priority_phones;
                        }
                        
                        if ($phones_subset)
                        {
                            $random_phone = $phones_subset[array_rand($phones_subset)];
                        }
                    }

                    // This should only happen if the user try to send a message without any phone in his account, then we simply ignore.
                    if (!$random_phone && !$phone_to_use)
                    {
                        continue;
                    }
                    
                    $id_phone = $phone_to_use['id'] ?? $random_phone['id'];
                    $sms_per_scheduled[$id_scheduled][] = [
                        'id_user' => $id_user,
                        'id_scheduled' => $id_scheduled,
                        'id_phone' => $id_phone,
                        'destination' => $target['number'],
                        'flash' => $scheduled['flash'],
                        'mms' => $scheduled['mms'],
                        'tag' => $scheduled['tag'],
                        'medias' => $scheduled['medias'],
                        'text' => $text,
                    ];

                    // Consume one sms from remaining volume of phone
                    $users_phones[$id_user][$id_phone]['remaining_volume'] --;
                }
            }

            return $sms_per_scheduled;
        }
    }
