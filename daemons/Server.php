<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Main daemon class
 */
class Server extends AbstractDaemon
{
    private $internal_phone;
    private $internal_scheduled;
    private $internal_received;
    private $bdd;
    private $daemons_queues = [];

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
        $this->internal_phone = new \controllers\internals\Phone($this->bdd);
        $this->internal_scheduled = new \controllers\internals\Scheduled($this->bdd);
        $this->internal_received = new \controllers\internals\Received($this->bdd);


        //Start all phones daemons
        $phones = $this->internal_phone->get_all();
        $this->start_daemons($phones);


        //Send smss
        $smss = $this->internal_scheduled->get_smss_to_send();
        $this->daemons_queues = $this->send_smss($this->daemons_queues, $smss, $this->internal_scheduled); //Add new queues to array of queues


        //Read smss
        //$this->read_smss($this->internal_received);

        sleep(0.5);
    }


    /**
     * Function to start phones daemons
     * @param array $phones : Phones to start daemon for if the daemon is not already started
     * @return void
     */
    public function start_daemons (array $phones) : void
    {
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
    }


    /**
     * Function to get messages to send and transfer theme to daemons
     * @param array $queues : Queues for phones
     * @param array $smss : Smss to send
     * @param \controllers\internals\Scheduled $internal_scheduled : Internal Scheduled
     * @return array : array of queues with new queues appened
     */
    public function send_smss (array $queues, array $smss, \controllers\internals\Scheduled $internal_scheduled) : array
    {
        foreach ($smss as $sms)
        {
            //If the queue has been deleted or does not exist, create a new one
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
                'id_scheduled' => $sms['id_scheduled'],
                'text' => $sms['text'],
                'origin' => $sms['origin'],
                'destination' => $sms['destination'],
                'flash' => $sms['flash'],
            ];

            msg_send($queue, SEND_MSG, $msg);
        }

        return $queues;
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
