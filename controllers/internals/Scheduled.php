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
        protected $model = null;

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        protected function get_model () : \descartes\Model
        {
            $this->model = $this->model ?? new \models\Scheduled($this->bdd);
            return $this->model;
        } 

        /**
         * Create a scheduled
         * @param int $id_user : User to insert scheduled for
         * @param $at : Scheduled date to send
         * @param string $text : Text of the message
         * @param ?string $origin : Origin number of the message, null by default
         * @param bool $flash : Is the sms a flash sms, by default false
         * @param array $numbers : Numbers to send message to
         * @param array $contacts_ids : Contact ids to send message to
         * @param array $groups_ids : Group ids to send message to
         * @return bool : false on error, new id on success
         */
        public function create (int $id_user, $at, string $text, ?string $origin = null, bool $flash = false, array $numbers = [], array $contacts_ids = [], array $groups_ids = [])
        {
            $scheduled = [ 
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'origin' => $origin,
                'flash' => $flash,
            ];

            if ($origin)
            {
                $internal_phone = new Phone($this->bdd);
                $find_phone = $internal_phone->get_by_number_and_user($id_user, $origin);

                if (!$find_phone)
                {
                    return false;
                }
            }
            
            
            $id_scheduled = $this->get_model()->insert($scheduled);
            if (!$id_scheduled)
            {
                $date = date('Y-m-d H:i:s');
                $internal_event = new Event($this->bdd);
                $internal_event->create($id_user, 'SCHEDULED_ADD', 'Ajout d\'un Sms pour le ' . $date . '.');
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

            return $id_scheduled;
        }


        /**
         * Update a scheduled
         * @param int $id_user : User to insert scheduled for
         * @param int $id_scheduled : Scheduled id
         * @param $at : Scheduled date to send
         * @param string $text : Text of the message
         * @param ?string $origin : Origin number of the message, null by default
         * @param bool $flash : Is the sms a flash sms, by default false
         * @param array $numbers : Numbers to send message to
         * @param array $contacts_ids : Contact ids to send message to
         * @param array $groups_ids : Group ids to send message to
         * @return bool : false on error, new id on success
         */
        public function update_for_user (int $id_user, int $id_scheduled, $at, string $text, ?string $origin = null, bool $flash = false, array $numbers = [], array $contacts_ids = [], array $groups_ids = [])
        {
            $scheduled = [ 
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'origin' => $origin,
                'flash' => $flash,
            ];


            if ($origin)
            {
                $internal_phone = new Phone($this->bdd);
                $find_phone = $internal_phone->get_by_number_and_user($id_user, $origin);

                if (!$find_phone)
                {
                    return false;
                }
            }

            $success = (bool) $this->get_model()->update_for_user($id_user, $id_scheduled, $scheduled);

            $this->get_model()->delete_scheduled_numbers($id_scheduled);
            $this->get_model()->delete_scheduled_contact_relations($id_scheduled);
            $this->get_model()->delete_scheduled_group_relations($id_scheduled);

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

            return true;
        }


        /**
         * Get messages scheduled before a date for a number and a user
         * @param int $id_user : User id
         * @param $date : Date before which we want messages
         * @param string $number : Number for which we want messages
         * @return array
         */
        public function gets_before_date_for_number_and_user (int $id_user, $date, string $number)
        {
            return $this->get_model()->gets_before_date_for_number_and_user($id_user, $date, $number);
        }

        
        /**
         * Return numbers for a scheduled message
         * @param int $id_scheduled : Scheduled id
         * @return array
         */
        public function get_numbers(int $id_scheduled)
        {
            return $this->get_model()->get_numbers($id_scheduled);
        }


        /**
         * Return contacts for a scheduled message
         * @param int $id_scheduled : Scheduled id
         * @return array
         */
        public function get_contacts(int $id_scheduled)
        {
            return $this->get_model()->get_contacts($id_scheduled);
        }


        /**
         * Return groups for a scheduled message
         * @param int $id_scheduled : Scheduled id
         * @return array
         */
        public function get_groups(int $id_scheduled)
        {
            return $this->get_model()->get_groups($id_scheduled);
        }
    }
