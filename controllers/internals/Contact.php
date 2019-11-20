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

    class Contact extends StandardController
    {
        protected $model = null;

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        protected function get_model () : \descartes\Model
        {
            $this->model = $this->model ?? new \models\Contact($this->bdd);
            return $this->model;
        } 

        /**
         * Return a contact for a user by a number
         * @param int $id_user : user id
         * @param string $number : Contact number
         * @return array
         */
        public function get_by_number_and_user(int $id_user, string $number)
        {
            return $this->get_model()->get_by_number_and_user($id_user, $number);
        }


        /**
         * Return a contact by his name for a user
         * @param int $id_user : User id
         * @param string $name : Contact name
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_and_user($id_user, $name);
        }
        
        
        /**
         * Return all contacts of a user.
         * @param int $id_user : user id
         * @return array
         */
        public function gets_for_user (int $id_user)
        {
            return $this->get_model()->gets_for_user($id_user);
        }


        /**
         * Create a new contact
         * @param int $id_user : User id
         * @param string $number : Contact number
         * @param string $name : Contact name
         * @param string $datas : Contact datas
         * @return mixed bool|int : False if cannot create contact, id of the new contact else
         */
        public function create($id_user, $number, $name, $datas)
        {
            $contact = [
                'id_user' => $id_user,
                'number' => $number,
                'name' => $name,
                'datas' => $datas,
            ];

            $result = $this->get_model()->insert($contact);
            if (!$result)
            {
                return $result;
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'CONTACT_ADD', 'Ajout contact : '.$name.' ('.\controllers\internals\Tool::phone_format($number).')');

            return $result;
        }


        /**
         * Update a contact
         * @param int $id_user : User id
         * @param int $id : Contact id
         * @param string $number : Contact number
         * @param string $name : Contact name
         * @param ?string $datas : Contact datas
         * @return int : number of modified rows
         */
        public function update_for_user(int $id_user, int $id, string $number, string $name, ?string $datas)
        {
            $contact = [
                'number' => $number,
                'name' => $name,
                'datas' => $datas,
            ];

            return $this->get_model()->update_for_user($id_user, $id, $contact);
        }
    }
