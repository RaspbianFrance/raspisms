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

    class Phone extends \descartes\InternalController
    {
        private $model_phone;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_phone = new \models\Phone($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }
        
        /**
         * Return list of phones
         * @param int $id_user : User id
         * @param mixed(int|bool) $nb_entry : Number of entry to return
         * @param mixed(int|bool) $page     : Numero of page
         *
         * @return array|bool : List of user or false
         */
        public function list(int $id_user, ?int $nb_entry = null, ?int $page = null)
        {
            return $this->model_phone->list($id_user, $nb_entry, $page * $nb_entry);
        }

        /**
         * Return a phone
         * @param int $id :  id of the phone
         * @return array
         */
        public function get (int $id)
        {
            return $this->model_phone->get($id);
        }
        
        
        /**
         * Return a phone by is number
         * @param string $number :  phone number
         * @return array
         */
        public function get_by_number (string $number)
        {
            return $this->model_phone->get_by_number($number);
        }
        
        
        /**
         * Return a phone by his number and user
         * @param string $number :  phone number
         * @param int $id_user : user id
         * @return array
         */
        public function get_by_number_and_user (string $number, int $id_user)
        {
            return $this->model_phone->get_by_number_and_user($number, $id_user);
        }
        
        
        /**
         * Return phones of a user
         * @param int $id_user :  id of the user
         * @return array
         */
        public function gets_for_user (int $id_user)
        {
            return $this->model_phone->gets_for_user($id_user);
        }
        
        
        /**
         * Return all phones
         * @return array
         */
        public function get_all ()
        {
            return $this->model_phone->get_all();
        }


        /**
         * Delete a phone
         * @param int $id : Phone id
         * @return bool
         */
        public function delete (int $id) : bool
        {
            return (bool) $this->model_phone->delete($id);
        }

        
        /**
         * Create a phone
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $adapter : The adapter to use the phone
         * @param ?string json $adapter_datas : A JSON string representing adapter's datas (for example credentials for an api)
         * @return bool : false on error, true on success
         */
        public function create (int $id_user, string $number, string $adapter, ?string $adapter_datas) : bool
        {
            $phone = [
                'id_user' => $id_user,
                'number' => $number,
                'adapter' => $adapter,
                'adapter_datas' => $adapter_datas,
            ];

            return (bool) $this->model_phone->insert($phone);
        }


        /**
         * Update a phone
         * @param int $id : Phone id
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $adapter : The adapter to use the phone
         * @param array $adapter_datas : An array of the datas of the adapter (for example credentials for an api)
         * @return bool : false on error, true on success
         */
        public function update (int $id, int $id_user, string $number, string $adapter, array $adapter_datas) : bool
        {
            $phone = [
                'id_user' => $id_user,
                'number' => $number,
                'adapter' => $adapter,
                'adapter_datas' => json_encode($adapter_datas),
            ];

            return (bool) $this->model_phone->update($id, $phone);
        }

    }
