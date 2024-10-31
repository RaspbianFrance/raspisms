<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace daemons;

use controllers\internals\Queue;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Phone daemon class.
 */
class Phone extends AbstractDaemon
{
    private $max_inactivity = 5 * 60;
    private $read_delay = 20 / 0.5;
    private $read_tick = 0;
    private ?Queue $queue;
    private $webhook_queue;
    private $last_message_at;
    private $phone;
    private $adapter;
    private $bdd;

    /**
     * Constructor.
     *
     * @param array $phone : A phone table entry
     */
    public function __construct(array $phone)
    {
        $this->phone = $phone;

        $name = 'RaspiSMS Daemon Phone ' . $this->phone['id'];
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/daemons.log', Logger::DEBUG));
        $pid_dir = PWD_PID;
        $no_parent = false; //Phone should be rattach to manager, so manager can stop him easily
        $additional_signals = [];
        $uniq = true; //Each phone should be uniq

        //Construct the daemon
        parent::__construct($name, $logger, $pid_dir, $no_parent, $additional_signals, $uniq);

        parent::start();
    }

    public function run()
    {
        usleep(0.5 * 1000000); //Micro sleep for perfs

        $this->read_tick += 1;

        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

        //Send smss in queue
        $this->send_smss();

        //Read only every x ticks (x/2 seconds) to prevent too many call
        if ($this->read_tick >= $this->read_delay)
        {
            //Read received smss
            $this->read_smss();
            $this->read_tick = 0;
        }

        //Stop after 5 minutes of inactivity to avoid useless daemon
        if ((microtime(true) - $this->last_message_at) > $this->max_inactivity)
        {
            posix_kill(getmypid(), SIGTERM); //Send exit signal to the current process

            return false;
        }
    }

    public function on_start()
    {
        //Set last message at to construct time
        $this->last_message_at = microtime(true);

        $this->queue = new Queue(QUEUE_ID_PHONE);

        //Instanciate adapter
        $adapter_class = $this->phone['adapter'];
        $this->adapter = new $adapter_class($this->phone['adapter_data']);

        $this->logger->info('Starting Phone daemon with pid ' . getmypid());
    }

    public function on_stop()
    {
        $this->logger->info('Stopping Phone daemon with pid ' . getmypid());
    }

    public function handle_other_signals($signal)
    {
        $this->logger->info('Signal not handled by ' . $this->name . ' Daemon : ' . $signal);
    }

    /**
     * Send sms.
     */
    private function send_smss()
    {
        $internal_sended = new \controllers\internals\Sended($this->bdd);

        $find_message = true;
        while ($find_message)
        {
            $message_type = (int) QUEUE_TYPE_SEND_MSG_PREFIX . $this->phone['id'];
            $message = $this->queue->read($message_type);

            if ($message === null)
            {
                $find_message = false;
                continue;
            }

            $message = json_decode($message, true);

            //Update last message time
            $this->last_message_at = microtime(true);

            //Do message sending
            $this->logger->info('Try send message : ' . json_encode($message));

            $response = $internal_sended->send($this->adapter, $this->phone['id_user'], $this->phone['id'], $message['text'], $message['destination'], $message['flash'], $message['mms'], $message['tag'], $message['medias'], $message['id_scheduled']);
            if ($response['error'])
            {
                $this->logger->error('Failed send message : ' . json_encode($message) . ' with error : ' . $response['error_message']);

                continue;
            }

            $this->logger->info('Successfully send message : ' . json_encode($message));
        }
    }

    /**
     * Read smss for a phone.
     */
    private function read_smss()
    {
        $internal_received = new \controllers\internals\Received($this->bdd);

        if (!$this->adapter->meta_support_read())
        {
            return true;
        }

        $response = $this->adapter->read();

        if ($response['error'])
        {
            $this->logger->info('Error reading received smss : ' . $response['error_message']);

            return false;
        }

        if (!$response['smss'])
        {
            return true;
        }

        //Process smss
        foreach ($response['smss'] as $sms)
        {
            $this->logger->info('Receive message : ' . json_encode($sms));
            $response = $internal_received->receive($this->phone['id_user'], $this->phone['id'], $sms['text'], $sms['origin'], $sms['at'], \models\Received::STATUS_UNREAD, $sms['mms'] ?? false, $sms['medias'] ?? []);

            if ($response['error'])
            {
                $this->logger->error('Failed receive message : ' . json_encode($sms) . ' with error : ' . $response['error_message']);

                continue;
            }

            $this->logger->info('Message received successfully.');
        }
    }
}
