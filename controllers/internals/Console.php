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

    /**
     * Class to call the console scripts 
     */
    class Console extends \descartes\InternalController
    {
        public function server ()
        {
            $server = new \daemons\Server();
        }


        public function phone ($number)
        {
            $server = new \daemons\Phone($number);
        }
    }
