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

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Main daemon class.
 */
class Sender extends AbstractDaemon
{
    private $internal_phone;
    private $internal_scheduled;
    private $internal_received;
    private $bdd;
    private $queues = [];

    public function __construct()
    {
        $name = 'RaspiSMS Daemon Sender';
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/daemons.log', Logger::DEBUG));
        $pid_dir = PWD_PID;
        $no_parent = false; //Webhook should be rattach to manager, so manager can stop him easily
        $additional_signals = [];
        $uniq = true; //Webhook should be uniq

        //Construct the daemon
        parent::__construct($name, $logger, $pid_dir, $no_parent, $additional_signals, $uniq);

        parent::start();
    }

    public function run()
    {
        //Create the internal controllers
        $this->internal_scheduled = new \controllers\internals\Scheduled($this->bdd);

        //Get smss and transmit order to send to appropriate phone daemon
        $smss_per_scheduled = $this->internal_scheduled->get_smss_to_send();
        $this->transmit_smss($smss_per_scheduled); //Add new queue to array of queues

        usleep(0.5 * 1000000);
    }

    /**
     * Function to transfer smss to send to phones daemons.
     *
     * @param array $smss_per_scheduled : Smss to send per scheduled id
     */
    public function transmit_smss(array $smss_per_scheduled): void
    {
        foreach ($smss_per_scheduled as $id_scheduled => $smss)
        {
            foreach ($smss as $sms)
            {
                //If queue not already exists
                $queue_id = (int) (QUEUE_ID_PHONE_PREFIX . $sms['id_phone']);
                if (!msg_queue_exists($queue_id) || !isset($queues[$queue_id]))
                {
                    $this->queues[$queue_id] = msg_get_queue($queue_id);
                }

                $msg = [
                    'id_user' => $sms['id_user'],
                    'id_scheduled' => $sms['id_scheduled'],
                    'text' => $sms['text'],
                    'id_phone' => $sms['id_phone'],
                    'destination' => $sms['destination'],
                    'flash' => $sms['flash'],
                    'mms' => $sms['mms'],
                    'medias' => $sms['medias'] ?? [],
                ];

                msg_send($this->queues[$queue_id], QUEUE_TYPE_SEND_MSG, $msg);
                $this->logger->info('Transmit sms send signal to phone ' . $sms['id_phone'] . ' on queue ' . $queue_id . '.');
            }

            $this->logger->info('Scheduled ' . $id_scheduled . ' treated and deleted.');
            $this->internal_scheduled->delete($id_scheduled);
        }
    }

    public function on_start()
    {
        $this->logger->info('Starting Sender with pid ' . getmypid());
        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
    }

    public function on_stop()
    {
        $this->logger->info('Stopping Sender with pid ' . getmypid());
    }

    public function handle_other_signals($signal)
    {
        $this->logger->info('Signal not handled by ' . $this->name . ' Daemon : ' . $signal);
    }
}
