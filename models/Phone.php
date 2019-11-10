<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    /**
     * Allow phone database management 
     */
    class Phone extends \descartes\Model
    {
        /**
         * Return a phone by his id
         * @param int $id : Phone id
         * @return array
         */
        public function get (int $id)
        {
            return $this->_select_one('phone', ['id' => $id]);
        }

        /**
         * Find phones of a user
         * @param string $id_user : user's id
         * @return array 
         */
        public function gets_for_user (int $id_user)
        {
            return $this->_select('phone', ['id_user' => $id_user]);
        }


        /**
         * Find all phones
         * @return array
         */
        public function get_all ()
        {
            return $this->_select('phone');
        }

        /**
         * Delete a phone
         * @param int $id : phone id
         * @return array
         */
        public function delete ($id)
        {
            return $this->_delete('phone', ['id' => $id]);
        }


        /**
         * Create a phone
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $platform : The platform to use the phone
         * @param string JSON $platform_datas : A json string representing the datas of the platform (for exemple credentials of an api)
         * @return mixed bool : false on error, true on success
         */
        public function insert($phone)
        {
            return (bool) $this->_insert('phone', $phone);
        }
        
        
        /**
         * Update a phone
         * @param int $id : Id of the phone
         * @param int $id_user : User to insert phone for
         * @param string $number : The number of the phone
         * @param string $platform : The platform to use the phone
         * @param string JSON $platform_datas : A json string representing the datas of the platform (for exemple credentials of an api)
         * @return mixed bool : false on error, true on success
         */
        public function update ($id, $phone)
        {
            return (bool) $this->_update('phone', $phone, ['id' => $id]);
        }
    }
