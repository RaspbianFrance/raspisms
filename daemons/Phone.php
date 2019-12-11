<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Phone daemon class
 */
class Phone extends AbstractDaemon
{
    private $msg_queue;

    public function __construct($phone)
    {
        $name = 'Phone ' . $phone['number'];
        $pid_dir = PWD_PID;
        $additional_signals = [SIGUSR1, SIGUSR2];
        $uniq = true; //Main server should be uniq
        
        $queue_id = (int) mb_substr($phone['number'], 1);
        $this->msg_queue = msg_get_queue($queue_id);

        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/raspisms.log', Logger::DEBUG));

        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct($name, $logger, $pid_dir, $additional_signals, $uniq);

        //Start the daemon
        parent::start();
    }


    public function run()
    {
        $msgtype = null;
        $maxsize = 409600;
        $message = null;

        msg_receive($this->msg_queue, SEND_MSG, $msgtype, $maxsize, $message);

        if (!$message)
        {
            return true;
        }

        $this->logger->debug(json_encode($message));
    }


    public function on_start()
    {
        $this->logger->info("Starting " . $this->name . " with pid " . getmypid());
    }


    public function on_stop() 
    {
        $this->logger->info("Stopping " . $this->name . " with pid " . getmypid ());
    }


    public function handle_other_signals($signal)
    {
        $this->logger->info("Signal not handled by " . $this->name . " Daemon : " . $signal);
    }
}
