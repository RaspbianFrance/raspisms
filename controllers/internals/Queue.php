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

use Exception;
use models\RedisQueue;
use models\SystemVQueue;

class Queue extends \descartes\InternalController
{
    private $queue;

    /**
     * A class to interact with queue, the class is in charge to choose the type of queue (redis/system v) to use
     */
    public function __construct($id)
    {
        if (USE_REDIS_QUEUES ?? false)
        {
            $params = [];
            if (REDIS_HOST ?? false)
            {
                $params['host'] = REDIS_HOST;
            }

            if (REDIS_PORT ?? false)
            {
                $params['port'] = REDIS_PORT;
            }

            if (REDIS_PASSWORD ?? false)
            {
                $params['auth'] = REDIS_PASSWORD;
            }

            $this->queue = new RedisQueue($id, $params, 'raspisms', 'raspisms');
        }
        else
        {
            $this->queue = new SystemVQueue($id);
        }
    }

    /**
     * Add a message to the queue
     * 
     * @param string $message : The message to add to the queue
     * @param ?string $tag : A tag to associate to the message for routing purposes, if null will add to general queue
     */
    public function push($message, ?string $tag = null)
    {
        return $this->queue->push($message, $tag);
    }

    /**
     * Read the older message in the queue
     * 
     * @return mixed $message : The oldest message or null if no message found, can be anything
     * @param ?string $tag : A tag to associate to the message for routing purposes, if null will read from general queue
     * @param mixed : The message return from the queue, can be anything, null if no message found
     */
    public function read(?string $tag = null)
    {
        return $this->queue->read($tag);
    }

    /**
     * Function to close system V queue for cleaning resources, usefull only if system V queue
     */
    public function close()
    {
        if ($this->queue instanceof SystemVQueue)
        {
            $this->queue->close();
        }
    }
}
