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
use GuzzleHttp\Promise\Utils;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Phone daemon class.
 */
class Webhook extends AbstractDaemon
{
    private ?Queue $webhook_queue;
    private $guzzle_client;

    /**
     * Constructor.
     *
     * @param array $phone : A phone table entry
     */
    public function __construct()
    {
        $name = 'RaspiSMS Daemon Webhook';
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/daemons.log', Logger::DEBUG));
        $pid_dir = PWD_PID;
        $no_parent = false; //Sended should be rattach to manager, so manager can stop him easily
        $additional_signals = [];
        $uniq = true; //Sender should be uniq

        $this->guzzle_client = new \GuzzleHttp\Client();

        //Construct the daemon
        parent::__construct($name, $logger, $pid_dir, $no_parent, $additional_signals, $uniq);

        parent::start();
    }

    public function run()
    {
        $find_message = true;
        $promises = [];
        while ($find_message)
        {
            $message = $this->webhook_queue->read(QUEUE_TYPE_WEBHOOK);

            if ($message === null)
            {
                $find_message = false;
                continue;
            }

            $this->logger->info('Trigger webhook : ' . $message);

            $message = json_decode($message, true);
            $promises[] = $this->guzzle_client->postAsync($message['url'], ['form_params' => $message['data']]);
        }

        try
        {
            $responses = Utils::unwrap($promises);
        }
        catch (\Exception $e)
        {
            $this->logger->info('Webhook : ' . json_encode($message) . 'failed with ' . $e->getMessage());
        }

        usleep(0.5 * 1000000);
    }

    public function on_start()
    {
        try{
            $this->webhook_queue = new Queue(QUEUE_ID_WEBHOOK);
        }
        catch (\Exception $e)
        {
            $this->logger->info('Webhook : failed with ' . $e->getMessage());
        }

        $this->logger->info('Starting Webhook daemon with pid ' . getmypid());
    }

    public function on_stop()
    {
        //Delete queue on daemon close
        $this->logger->info('Closing queue : ' . QUEUE_ID_WEBHOOK);
        unset($this->webhook_queue);

        $this->logger->info('Stopping Webhook daemon with pid ' . getmypid());
    }

    public function handle_other_signals($signal)
    {
        $this->logger->info('Signal not handled by ' . $this->name . ' Daemon : ' . $signal);
    }
}
