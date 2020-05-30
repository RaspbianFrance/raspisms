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

    class Scheduled extends StandardController
    {
        protected $model;

        /**
         * Create a scheduled.
         *
         * @param int $id_user : User to insert scheduled for
         * @param $at : Scheduled date to send
         * @param string $text                  : Text of the message
         * @param ?int   $id_phone              : Id of the phone to send message with, null by default
         * @param bool   $flash                 : Is the sms a flash sms, by default false
         * @param array  $numbers               : Numbers to send message to
         * @param array  $contacts_ids          : Contact ids to send message to
         * @param array  $groups_ids            : Group ids to send message to
         * @param array  $conditional_group_ids : Conditional Groups ids to send message to
         *
         * @return bool : false on error, new id on success
         */
        public function create(int $id_user, $at, string $text, ?int $id_phone = null, bool $flash = false, array $numbers = [], array $contacts_ids = [], array $groups_ids = [], array $conditional_group_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'id_phone' => $id_phone,
                'flash' => $flash,
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

            $id_scheduled = $this->get_model()->insert($scheduled);
            if (!$id_scheduled)
            {
                return false;
            }

            foreach ($numbers as $number)
            {
                $this->get_model()->insert_scheduled_number($id_scheduled, $number);
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
         * @param bool   $flash                 : Is the sms a flash sms, by default false
         * @param array  $numbers               : Numbers to send message to
         * @param array  $contacts_ids          : Contact ids to send message to
         * @param array  $groups_ids            : Group ids to send message to
         * @param array  $conditional_group_ids : Conditional Groups ids to send message to
         *
         * @return bool : false on error, new id on success
         */
        public function update_for_user(int $id_user, int $id_scheduled, $at, string $text, ?string $id_phone = null, bool $flash = false, array $numbers = [], array $contacts_ids = [], array $groups_ids = [], array $conditional_group_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'id_phone' => $id_phone,
                'flash' => $flash,
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

            $success = (bool) $this->get_model()->update_for_user($id_user, $id_scheduled, $scheduled);

            $this->get_model()->delete_scheduled_numbers($id_scheduled);
            $this->get_model()->delete_scheduled_contact_relations($id_scheduled);
            $this->get_model()->delete_scheduled_group_relations($id_scheduled);
            $this->get_model()->delete_scheduled_conditional_group_relations($id_scheduled);

            foreach ($numbers as $number)
            {
                $this->get_model()->insert_scheduled_number($id_scheduled, $number);
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

            return true;
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
         * Get all messages to send and the number to use to send theme.
         *
         * @return array : [['id_scheduled', 'text', 'id_phone', 'destination', 'flash'], ...]
         */
        public function get_smss_to_send()
        {
            $smss_to_send = [];

            $internal_templating = new \controllers\internals\Templating();
            $internal_setting = new \controllers\internals\Setting($this->bdd);
            $internal_group = new \controllers\internals\Group($this->bdd);
            $internal_conditional_group = new \controllers\internals\ConditionalGroup($this->bdd);
            $internal_phone = new \controllers\internals\Phone($this->bdd);

            $users_settings = [];
            $users_phones = [];

            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $scheduleds = $this->get_model()->gets_before_date($now);
            foreach ($scheduleds as $scheduled)
            {
                if (!isset($users_settings[$scheduled['id_user']]))
                {
                    $users_settings[$scheduled['id_user']] = [];

                    $settings = $internal_setting->gets_for_user($scheduled['id_user']);
                    foreach ($settings as $name => $value)
                    {
                        $users_settings[$scheduled['id_user']][$name] = $value;
                    }
                }

                if (!isset($users_phones[$scheduled['id_user']]))
                {
                    $phones = $internal_phone->gets_for_user($scheduled['id_user']);
                    $users_phones[$scheduled['id_user']] = $phones ? $phones : [];
                }

                $phone_to_use = null;
                foreach ($users_phones[$scheduled['id_user']] as $phone)
                {
                    if ($phone['id'] !== $scheduled['id_phone'])
                    {
                        continue;
                    }

                    $phone_to_use = $phone;
                }

                if (null === $phone_to_use)
                {
                    $rnd_key = array_rand($users_phones[$scheduled['id_user']]);
                    $phone_to_use = $users_phones[$scheduled['id_user']][$rnd_key];
                }

                $messages = [];

                //Add messages for numbers
                $numbers = $this->get_numbers($scheduled['id']);
                foreach ($numbers as $number)
                {
                    $message = [
                        'id_user' => $scheduled['id_user'],
                        'id_scheduled' => $scheduled['id'],
                        'id_phone' => $phone_to_use['id'],
                        'destination' => $number['number'],
                        'flash' => $scheduled['flash'],
                    ];

                    if ((int) ($users_settings[$scheduled['id_user']]['templating'] ?? false))
                    {
                        $render = $internal_templating->render($scheduled['text']);

                        if (!$render['success'])
                        {
                            continue;
                        }

                        $message['text'] = $render['result'];
                    }
                    else
                    {
                        $message['text'] = $scheduled['text'];
                    }

                    $messages[] = $message;
                }

                //Add messages for contacts
                $contacts = $this->get_contacts($scheduled['id']);

                $groups = $this->get_groups($scheduled['id']);
                foreach ($groups as $group)
                {
                    $contacts_to_add = $internal_group->get_contacts($group['id']);
                    $contacts = array_merge($contacts, $contacts_to_add);
                }

                $conditional_groups = $this->get_conditional_groups($scheduled['id']);
                foreach ($conditional_groups as $conditional_group)
                {
                    $contacts_to_add = $internal_conditional_group->get_contacts_for_condition_and_user($scheduled['id_user'], $conditional_group['condition']);
                    $contacts = array_merge($contacts, $contacts_to_add);
                }

                $added_contacts = [];
                foreach ($contacts as $contact)
                {
                    if ($added_contacts[$contact['id']] ?? false)
                    {
                        continue;
                    }

                    $added_contacts[$contact['id']] = true;

                    $message = [
                        'id_user' => $scheduled['id_user'],
                        'id_scheduled' => $scheduled['id'],
                        'id_phone' => $phone_to_use['id'],
                        'destination' => $contact['number'],
                        'flash' => $scheduled['flash'],
                    ];

                    if ((int) ($users_settings[$scheduled['id_user']]['templating'] ?? false))
                    {
                        $contact['datas'] = json_decode($contact['datas'], true);
                        $datas = ['contact' => $contact['datas']];
                        $render = $internal_templating->render($scheduled['text'], $datas);

                        if (!$render['success'])
                        {
                            continue;
                        }

                        $message['text'] = $render['result'];
                    }
                    else
                    {
                        $message['text'] = $scheduled['text'];
                    }

                    $messages[] = $message;
                }

                foreach ($messages as $message)
                {
                    //Remove empty messages
                    if ('' === trim($message['text']))
                    {
                        continue;
                    }

                    $smss_to_send[] = $message;
                }
            }

            return $smss_to_send;
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
         *
         * @return \descartes\Model
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Scheduled($this->bdd);

            return $this->model;
        }
    }
