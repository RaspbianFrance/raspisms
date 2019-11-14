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

    class Phone extends StandardController
    {
        protected $model = null;

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        protected function get_model () : \descartes\Model
        {
            $this->model = $this->model ?? new \models\Phone($this->$bdd);
            return $this->model;
        } 

        /**
         * Return a phone for a user by a number
         * @param int $id_user : user id
         * @param string $number : Phone number
         * @return array
         */
        public function get_by_number_and_user(int $id_user, string $number)
        {
            return $this->model_phone->get_by_number_and_user($id_user, $number);
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
         * @param int $id_user : User to insert phone for
         * @param int $id : Phone id
         * @param string $number : The number of the phone
         * @param string $adapter : The adapter to use the phone
         * @param array $adapter_datas : An array of the datas of the adapter (for example credentials for an api)
         * @return bool : false on error, true on success
         */
        public function update_for_user (int $id_user, int $id, string $number, string $adapter, array $adapter_datas) : bool
        {
            $phone = [
                'id_user' => $id_user,
                'number' => $number,
                'adapter' => $adapter,
                'adapter_datas' => json_encode($adapter_datas),
            ];

            return (bool) $this->model_phone->update_for_user($id_user, $id, $phone);
        }
    }
