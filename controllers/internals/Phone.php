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
         * Return a phone
         * @param int $id :  id of the phone
         * @return array
         */
        public function get (int $id)
        {
            $phone = $this->model_phone->get($id);

            if (!$phone)
            {
                return false;
            }

            $phone['plateform_datas'] = json_decode($phone['plateform_datas'], true);
            return $phone;
        }
        
        
        /**
         * Return phones of a user
         * @param int $id_user :  id of the user
         * @return array
         */
        public function gets_for_user (int $id_user)
        {
            $phones = $this->model_phone->gets($id_user);

            if (!$phone)
            {
                return false;
            }

            foreach ($phones as &$phone)
            {
                $phone['plateform_datas'] = json_decode($phone['plateform_datas'], true);
            }

            return $phones;
        }
        
        
        /**
         * Return all phones
         * @return array
         */
        public function get_all ()
        {
            $phones = $this->model_phone->get_all();

            if (!$phone)
            {
                return false;
            }

            foreach ($phones as &$phone)
            {
                $phone['plateform_datas'] = json_decode($phone['plateform_datas'], true);
            }

            return $phones;
        }


        /**
         * Delete a phone
         * @param int $id : Phone id
         * @return bool
         */
        public function delete (int $id) : boolean
        {
            return (bool) $this->model_phone->delete($id);
        }

        
        /**
         * Create a phone
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $platform : The platform to use the phone
         * @param array $platform_datas : An array of the datas of the platform (for example credentials for an api)
         * @return bool : false on error, true on success
         */
        public function insert (int $id_user, string $number, string $platform, array $platform_datas) : boolean
        {
            $phone = [
                'id_user' => $id_user,
                'number' => $number,
                'platform' => $platform,
                'platform_datas' => json_encode($platform_datas),
            ];

            return (bool) $this->model_phone->insert($phone);
        }


        /**
         * Update a phone
         * @param int $id : Phone id
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $platform : The platform to use the phone
         * @param array $platform_datas : An array of the datas of the platform (for example credentials for an api)
         * @return bool : false on error, true on success
         */
        public function update (int $id, int $id_user, string $number, string $platform, array $platform_datas) : boolean
        {
            $phone = [
                'id_user' => $id_user,
                'number' => $number,
                'platform' => $platform,
                'platform_datas' => json_encode($platform_datas),
            ];

            return (bool) $this->model_phone->update($id, $phone);
        }

    }
