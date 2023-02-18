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
        const MMS_SENDING = 'sending';
        const MMS_RECEPTION = 'reception';
        const MMS_BOTH = 'both';

        protected $model;

        /**
         * Return all phones for active users.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function get_all_for_active_users()
        {
            return $this->get_model()->get_all_for_active_users();
        }

        /**
         * Return all phones of a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            return $this->get_model()->gets_for_user($id_user);
        }

        /**
         * Return a list of phone limits
         *
         * @param int $id_phone : Phone id
         *
         * @return array
         */
        public function get_limits(int $id_phone)
        {
            return $this->get_model()->get_limits($id_phone);
        }

        /**
         * Check if a phone support mms.
         *
         * @param int $id : id of the phone to check
         * @param $type : type of sms support, a const from Phone, MMS_SENDING, MMS_RECEPTION or MMS_BOTH
         *
         * @return bool : true if support, false else
         */
        public function support_mms(int $id, string $type)
        {
            $phone = $this->get_model()->get($id);
            if (!$phone)
            {
                return false;
            }

            switch ($type)
            {
                case self::MMS_SENDING:
                    return $phone['adapter']::meta_support_mms_sending();

                    break;

                case self::MMS_RECEPTION:
                    return $phone['adapter']::meta_support_mms_reception();

                    break;

                case self::MMS_BOTH:
                    return $phone['adapter']::meta_support_mms_sending() && $phone['adapter']::meta_support_mms_reception();

                    break;

                default:
                    return false;
            }
        }

        /**
         * Get all phones supporting mms for a user.
         *
         * @param int $id_user : id of the user
         * @param $type : type of sms support, a const from Phone, MMS_SENDING, MMS_RECEPTION or MMS_BOTH
         *
         * @return array : array of phones supporting mms
         */
        public function gets_phone_supporting_mms_for_user(int $id_user, string $type)
        {
            $phones = $this->get_model()->gets_for_user($id_user);

            $valid_phones = [];
            foreach ($phones as $phone)
            {
                if ($this->support_mms($phone['id'], $type))
                {
                    $valid_phones[] = $phone;
                }
            }

            return $valid_phones;
        }

        /**
         * Return a phone for a user by a name.
         *
         * @param int    $id_user : user id
         * @param string $name    : Phone name
         *
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_and_user($id_user, $name);
        }

        /**
         * Create a phone.
         *
         * @param int         $id_user      : User to insert phone for
         * @param string      $name         : The name of the phone
         * @param string      $adapter      : The adapter to use the phone
         * @param string json $adapter_data : A JSON string representing adapter's data (for example credentials for an api)
         * @param int         $priority     : Priority with which to use phone to send SMS. Default 0.
         * @param array       $limits       : An array of limits for this phone. Each limit must be an array with a key volume and a key startpoint
         *
         * @return bool|int : false on error, new id on success
         */
        public function create(int $id_user, string $name, string $adapter, string $adapter_data, int $priority = 0, array $limits = [])
        {
            $phone = [
                'id_user' => $id_user,
                'name' => $name,
                'priority' => $priority,
                'adapter' => $adapter,
                'adapter_data' => $adapter_data,
            ];

            //Use transaction to garanty atomicity
            $this->bdd->beginTransaction();

            $new_phone_id = $this->get_model()->insert($phone);
            if (!$new_phone_id)
            {
                $this->bdd->rollBack();

                return false;
            }

            foreach ($limits as $limit)
            {
                $limit_id = $this->get_model()->insert_phone_limit($new_phone_id, $limit['volume'], $limit['startpoint']);
                
                if (!$limit_id)
                {
                    $this->bdd->rollBack();

                    return false;
                }
            }

            $success = $this->bdd->commit();
            return ($success ? $new_phone_id : false);
        }

        /**
         * Update a phone.
         *
         * @param int    $id_user      : User to insert phone for
         * @param int    $id           : Phone id
         * @param string $name         : The name of the phone
         * @param string $adapter      : The adapter to use the phone
         * @param string json $adapter_data : A JSON string representing adapter's data (for example credentials for an api)
         * @param int         $priority     : Priority with which to use phone to send SMS. Default 0.
         * @param array  $limits       : An array of limits for this phone. Each limit must be an array with a key volume and a key startpoint
         *
         * @return bool : false on error, true on success
         */
        public function update_for_user(int $id_user, int $id, string $name, string $adapter, string $adapter_data, int $priority = 0, array $limits = []): bool
        {
            $phone = [
                'id_user' => $id_user,
                'name' => $name,
                'adapter' => $adapter,
                'adapter_data' => $adapter_data,
                'priority' => $priority,
            ];

            //Use transaction to garanty atomicity
            $this->bdd->beginTransaction();
            
            $nb_delete = $this->get_model()->delete_phone_limits($id);

            foreach ($limits as $limit)
            {
                $limit_id = $this->get_model()->insert_phone_limit($id, $limit['volume'], $limit['startpoint']);
                
                if (!$limit_id)
                {
                    $this->bdd->rollBack();

                    return false;
                }
            }

            $nb_update = $this->get_model()->update_for_user($id_user, $id, $phone);
            
            $success = $this->bdd->commit();

            if (!$success)
            {
                return false;
            }

            if ($nb_update == 0 && count($limits) == 0)
            {
                return false;
            }

            return true;
        }

        /**
         * Update a phone status.
         *         
         * @param int    $id           : Phone id
         * @param string $status      : The new status of the phone
         *
         * @return bool : false on error, true on success
         */
        public function update_status(int $id, string $status) : bool
        {
            return (bool) $this->get_model()->update($id, ['status' => $status]);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \models\Phone
        {
            $this->model = $this->model ?? new \models\Phone($this->bdd);

            return $this->model;
        }
    }
