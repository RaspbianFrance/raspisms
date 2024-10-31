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
 *
 */
interface Queue
{
    /**
     * A FIFO Queue to exchange messages, the backend mechanism can be whatever we want, but the queue take message, tag for routing is optionnal
     * @param string $id : A unique identifier for the queue
     */
    public function __construct($id);

    /**
     * Add a message to the queue
     * 
     * @param string $message : The message to add to the queue, must be a string, for complex data just use json
     * @param ?string $tag : A tag to associate to the message for routing purposes, if not set will add to general queue
     */
    public function push($message, ?string $tag = null);

    /**
     * Read the older message in the queue (non-blocking)
     * @param ?string $tag : A tag to associate to the message for routing purposes, if not set will read from general queue
     * @return ?string $message : The oldest message or null if no message found, can be anything
     */
    public function read(?string $tag = null);
}
