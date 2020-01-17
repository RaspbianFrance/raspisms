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

    class Command extends StandardModel
    {
        /**
         * Return table name.
         *
         * @return string
         */
        protected function get_table_name(): string
        {
            return 'command';
        }
    }
