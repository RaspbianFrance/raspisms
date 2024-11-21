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
class SystemVQueue implements Queue
{
    private $id;
    private $queue;

    /**
     * A queue using System V message queues to store and exchange messages
     * routing is based on queue id and message type
     * 
     * ** Attention : Instead of string, all ids and tags must be numbers, its the system v queues works, no reliable way arround it**
     * @param int $id : A unique identifier for the queue, *this must be generated with ftok*

     */
    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    /**
     * Function to close the system v queue on destruction
     */
    public function close()
    {
        if ($this->queue)
        {
            msg_remove_queue($this->queue);
        }
    }

    /**
     * Function to get the message queue and ensure it is open, we should always call it during push/read just to 
     * make sure another process didn't close the queue
     */
    private function get_queue()
    {
        $this->queue = msg_get_queue($this->id);

        if (!$this->queue)
        {
            throw new \Exception('Impossible to get a System V message queue for id ' . $this->id);
        }
    }

    /**
     * Add a message to the queue
     * 
     * @param string $message : The message to add to the queue
     * @param ?string $tag : A tag to associate to the message for routing purposes. 
     * Though this is a string, we MUST pass a valid number, its the way System V queue works
     */
    public function push($message, ?string $tag = '0')
    {   
        $tag = (int) $tag;
        
        $this->get_queue();
        $error_code = null;
        $success = msg_send($this->queue, $tag, $message, true, true, $error_code);
        if (!$success)
        {
            throw new \Exception('Impossible to send the message on system V queue, error code : ' . $error_code);
        }

        return true;
    }

    /**
     * Read the older message in the queue
     * 
     * @param ?string $tag : A tag to associate to the message for routing purposes
     * Though this is a string, we MUST pass a valid number, its the way System V queue works
     * 
     * @return mixed $message : The oldest message or null if no message found, can be anything
     */
    public function read(?string $tag = '0')
    {
        $tag = (int) $tag;

        $msgtype = null;
        $maxsize = 409600;
        $message = null;

        // Message type is forged from a prefix concat with the phone ID
        $error_code = null;
        $this->get_queue();
        $success = msg_receive($this->queue, $tag, $msgtype, $maxsize, $message, true, MSG_IPC_NOWAIT, $error_code); //MSG_IPC_NOWAIT == dont wait if no message found

        if (!$success && MSG_ENOMSG !== $error_code)
        {
            throw new \Exception('Impossible to read messages on system V queue, error code : ' . $error_code);
        }

        if (!$message)
        {
            return null;
        }

        return $message;
    }
}
