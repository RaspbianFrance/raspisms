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
     * Handler for HTTP errors page
     * Not a standard controller as it's not linked to a model in any way.
     */
    class HttpError extends \descartes\InternalController
    {
        /**
         * Return 404 error page.
         */
        public function _404()
        {
            http_response_code(404);
            $this->render('error/404');
        }

        /**
         * Return unknown error page
         */
        public function unknown ()
        {
            http_response_code(500);
            $this->render('error/unknown');
        }
    }
