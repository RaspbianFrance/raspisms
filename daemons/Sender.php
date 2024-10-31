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
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Main daemon class.
 */
class Sender extends AbstractDaemon
{
    private $internal_scheduled;
    private $bdd;
    private ?Queue $queue;

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
                $msg = [
                    'id_user' => $sms['id_user'],
                    'id_scheduled' => $sms['id_scheduled'],
                    'text' => $sms['text'],
                    'id_phone' => $sms['id_phone'],
                    'destination' => $sms['destination'],
                    'flash' => $sms['flash'],
                    'mms' => $sms['mms'],
                    'tag' => $sms['tag'],
                    'medias' => $sms['medias'] ?? [],
                ];


                // Message type is forged from a prefix concat with the phone ID
                $message_type = (int) QUEUE_TYPE_SEND_MSG_PREFIX . $sms['id_phone'];
                $this->queue->push(json_encode($msg), $message_type);
                $this->logger->info('Transmit sms send signal to phone ' . $sms['id_phone'] . ' on queue ' . QUEUE_ID_PHONE . ' with message type ' . $message_type . '.');
            }

            $this->logger->info('Scheduled ' . $id_scheduled . ' treated and deleted.');
            $this->internal_scheduled->delete($id_scheduled);
        }
    }

    public function on_start()
    {
        try
        {
            $this->logger->info('Starting Sender with pid ' . getmypid());
            $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->queue = new Queue(QUEUE_ID_PHONE);
        }
        catch (Exception $e)
        {
            $this->logger->error('Failed to start sender daemon : ' . $e->getMessage());
        }
        
    }

    public function on_stop()
    {
        //Delete queue on daemon close
        $this->logger->info('Closing queue : ' . QUEUE_ID_PHONE);
        $this->queue->close();
        
        $this->logger->info('Stopping Sender with pid ' . getmypid());
    }

    public function handle_other_signals($signal)
    {
        $this->logger->info('Signal not handled by ' . $this->name . ' Daemon : ' . $signal);
    }
}
