<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Main daemon class
 */
class Server extends AbstractDaemon
{
    private $internal_user;
    private $internal_phone;
    private $internal_scheduled;
    private $bdd;

    public function __construct()
    {
        $logger = new Logger('server');
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/raspisms.log', Logger::DEBUG));

        $name = "RaspiSMS Server";
        $pid_dir = PWD_PID;
        $additional_signals = [];
        $uniq = true; //Main server should be uniq

        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct($name, $logger, $pid_dir, $additional_signals, $uniq);

        parent::start();
    }


    public function run()
    {
        //Create the internal controllers
        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
        $this->internal_user = new \controllers\internals\User($this->bdd);
        $this->internal_phone = new \controllers\internals\Phone($this->bdd);
        $this->internal_scheduled = new \controllers\internals\Scheduled($this->bdd);


        //Start all phones daemons
        $phones = $this->internal_phone->get_all();
        foreach ($phones as $phone)
        {
            $phone_name = 'RaspiSMS Phone ' . $phone['number'];
            $pid_file = PWD_PID . '/' . $phone_name . '.pid';
            
            if (file_exists($pid_file))
            {
                continue;
            }

            //Create a new daemon for the phone
            exec('php ' . PWD . '/console.php controllers/internals/Console.php phone number=\'' . $phone['number'] . '\' > /dev/null &');
        }

        $queues = [];

        //Get all sms to send
        $smss = $this->internal_scheduled->get_smss_to_send();
        foreach ($smss as $sms)
        {
            //If queue has been deleted or does not exist, create a new one
            $queue_id = (int) mb_substr($sms['origin'], 1);
            if (!msg_queue_exists($queue_id))
            {
                $queues[$queue_id] = msg_get_queue($queue_id);
            }
            elseif (!isset($queues[$queue_id]))
            {
                $queues[$queue_id] = msg_get_queue($queue_id);
            }

            $queue = $queues[$queue_id];

            $msg = [
                'text' => (string) $sms['text'],
                'origin' => (string) $sms['origin'],
                'destination' => (string) $sms['destination'],
                'flash' => (bool) $sms['flash'],
            ];

            msg_send($queue, SEND_MSG, $msg);

            //Delete the scheduled sms after sending
            $this->internal_scheduled->delete($sms['id_scheduled']);
        }

        sleep(0.5);
    }


    public function on_start()
    {
        $this->logger->info("Starting Server with pid " . getmypid());
    }


    public function on_stop() 
    {
        $this->logger->info("Stopping Server with pid " . getmypid ());
    }


    public function handle_other_signals($signal)
    {
        $this->logger->info("Signal not handled by " . $this->name . " Daemon : " . $signal);
    }
}
