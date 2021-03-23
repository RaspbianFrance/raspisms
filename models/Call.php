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
     * Manage bdd operations for calls
     */ 
    class Call extends StandardModel
    {
        const DIRECTION_INBOUND = 'inbound';
        const DIRECTION_OUTBOUND = 'outbound';

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'call';
        }
    }
