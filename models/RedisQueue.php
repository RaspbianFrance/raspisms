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

use Exception;

/**
 *
 */
class RedisQueue implements Queue
{
    private \Redis $redis;
    private $group;
    private $consumer;
    private $id;

    /**
     * A Redis queue to store and exchange messages using redis streams
     * routing is based on queue uniq id as stream name, combined with ':tag' if routing is needed, messages are stored as json
     * @param string $id : A unique identifier for the queue
     * @param array $redis_parameters : Parameters for the redis server, such as host, port, etc. Default to a basic local redis on port 6379
     * @param string $group : Name to use for the redis group that must read this queue, default to 'default'
     * @param string $consumer : Name to use for the redis consumer in the group that must read this queue, default to 'default'
     */
    public function __construct($id, $redis_parameters = [], $group = 'default', $consumer = 'default')
    {
        $this->id = $id;
        $this->redis = new \Redis();
        $success = $this->redis->connect($redis_parameters['host'], intval($redis_parameters['port']), 1, '', 0, 0, ['auth' => $redis_parameters['auth']]);
        
        if (!$success) 
        {
            throw new \Exception('Failed to connect to redis server !');
        }
        
        $this->group = $group;
        $this->consumer = $consumer;
    }

    /**
     * Add a message to the queue
     * 
     * @param string $message : The message to add to the queue
     * @param ?string $tag : A tag to associate to the message for routing purposes, if null will add to general queue
     */
    public function push($message, ?string $tag = null)
    {
        $stream = $this->id . ($tag !== null ? ":$tag" : '');
        $success = $this->redis->xAdd($stream, '*', ['message' => $message]);

        if (!$success) 
        {
            throw new \Exception('Failed to push a message !');
        }

        return true;
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
        $stream = $this->id . ($tag !== null ? ":$tag" : '');

        // Create the consumer group if it doesn't already exist
        try 
        {
            $this->redis->xGroup('CREATE', $stream, $this->group, '$', true);
        } 
        catch (Exception $e) 
        {
            // Ignore error if the group already exists
        }

        // Read a single message starting from the oldest (>)
        $messages = $this->redis->xReadGroup($this->group, $this->consumer, [$stream => '>'], 1);
        if (!count($messages))
        {
            return null;
        }

        // Find the message, acknowledge it and return it
        foreach ($messages as $stream_name => $entries) 
        {
            foreach ($entries as $message_id => $message) 
            {
                $success = $this->redis->xAck($stream, $this->group, [$message_id]);
                return $message['message'];
            }
        }

        return null;
    }
}
