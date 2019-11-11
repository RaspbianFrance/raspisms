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
     * Cette classe gère les accès bdd pour les settinges.
     */
    class Setting extends \descartes\Model
    {
        /**
         * Return array of all settings.
         * @param int $id_user : user id
         * @return array
         */
        public function gets_for_user (int $id_user): array
        {
            return $this->_select('setting', ['id_user' => $id_user]);
        }


        /**
         * Create a new setting
         * @param array $setting
         * @return bool
         */
        public function insert (array $setting) : bool
        {
            return (bool) $this->_insert('setting', $setting);
        }


        /**
         * Update a setting by his name.
         * @param int $id_user : user id
         * @param string $name : setting name
         * @param mixed $value
         * @return int : number of modified lines
         */
        public function update (int $id_user, string $name, $value): int
        {
            return $this->_update('setting', ['value' => $value], ['id_user' => $id_user, 'name' => $name]);
        }
    }
