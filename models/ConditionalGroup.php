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

    class ConditionalGroup extends StandardModel
    {
        /**
         * Return a conditional group by his name for a user.
         *
         * @param int    $id_user : User id
         * @param string $name    : Group name
         *
         * @return array
         */
        public function get_by_name_for_user(int $id_user, string $name)
        {
            return $this->_select_one($this->get_table_name(), ['id_user' => $id_user, 'name' => $name]);
        }

        /**
         * Return table name.
         *
         * @return string
         */
        protected function get_table_name(): string
        {
            return 'conditional_group';
        }
    }
