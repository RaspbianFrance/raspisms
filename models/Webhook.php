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

    class Webhook extends StandardModel
    {
        const TYPE_SEND_SMS = 'send_sms';
        const TYPE_RECEIVE_SMS = 'receive_sms';
        const TYPE_INBOUND_CALL = 'inbound_call';

        /**
         * Find all webhooks for a user and for a type of webhook.
         *
         * @param int    $id_user : User id
         * @param string $type    : Webhook type
         *
         * @return array
         */
        public function gets_for_type_and_user(int $id_user, string $type)
        {
            return $this->_select($this->get_table_name(), ['id_user' => $id_user, 'type' => $type]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'webhook';
        }
    }
