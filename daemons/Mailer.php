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
 * Phone daemon class.
 */
class Mailer extends AbstractDaemon
{
    private $mailer_queue;
    private $last_message_at;
    private $bdd;

    /**
     * Constructor.
     *
     * @param array $phone : A phone table entry
     */
    public function __construct()
    {
        $name = 'RaspiSMS Daemon Mailer';
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/daemons.log', Logger::DEBUG));
        $pid_dir = PWD_PID;
        $no_parent = false; //Rattach to parent so parent can stop it
        $additional_signals = [];
        $uniq = true; //Sender should be uniq

        //Construct the daemon
        parent::__construct($name, $logger, $pid_dir, $no_parent, $additional_signals, $uniq);

        parent::start();
    }

    public function run()
    {
        $find_message = true;
        while ($find_message)
        {
            //Call message
            $msgtype = null;
            $maxsize = 409600;
            $message = null;

            $error_code = null;
            $success = msg_receive($this->mailer_queue, QUEUE_TYPE_EMAIL, $msgtype, $maxsize, $message, true, MSG_IPC_NOWAIT, $error_code); //MSG_IPC_NOWAIT == dont wait if no message found
            if (!$success && MSG_ENOMSG !== $error_code)
            {
                $this->logger->critical('Error for mailer queue reading, error code : ' . $error_code);
                $find_message = false;

                continue;
            }

            if (!$message)
            {
                $find_message = false;

                continue;
            }

            $this->logger->info('Try sending email : ' . json_encode($message));

            $mailer = new \controllers\internals\Mailer();
            $success = $mailer->send($message['destinations'], $message['subject'], $message['body'], $message['alt_body']);
            if (!$success)
            {
                $this->logger->error('Failed sending email');

                continue;
            }

            $this->logger->info('Success sending email');
        }

        //Send mail every 5 seconds
        usleep(5 * 1000000);
    }

    public function on_start()
    {
        //Set last message at to construct time
        $this->mailer_queue = msg_get_queue(QUEUE_ID_EMAIL);

        $this->logger->info('Starting Mailer daemon with pid ' . getmypid());
    }

    public function on_stop()
    {
        //Delete queue on daemon close
        $this->logger->info('Closing queue : ' . QUEUE_ID_EMAIL);
        msg_remove_queue($this->mailer_queue);

        $this->logger->info('Stopping Mailer daemon with pid ' . getmypid());
    }

    public function handle_other_signals($signal)
    {
        $this->logger->info('Signal not handled by ' . $this->name . ' Daemon : ' . $signal);
    }
}
