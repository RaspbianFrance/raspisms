<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Main daemon class
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
        $logger = new Logger('Daemon Sender');
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/raspisms.log', Logger::DEBUG));

        $name = "RaspiSMS Daemon Sender";
        $pid_dir = PWD_PID;
        $additional_signals = [];
        $uniq = true; //Sender should be uniq

        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct($name, $logger, $pid_dir, $additional_signals, $uniq);

        parent::start();
    }


    public function run()
    {
        //Create the internal controllers
        $this->internal_scheduled = new \controllers\internals\Scheduled($this->bdd);

        //Get smss and transmit order to send to appropriate phone daemon
        $smss = $this->internal_scheduled->get_smss_to_send();
        $this->transmit_smss($smss); //Add new queues to array of queues

        usleep(0.5 * 1000000);
    }


    /**
     * Function to get messages to send and transfer theme to phones daemons
     * @param array $smss : Smss to send
     */
    public function transmit_smss (array $smss) : void
    {
        foreach ($smss as $sms)
        {
            //If queue not already exists
            $queue_id = (int) mb_substr($sms['origin'], 1);
            if (!msg_queue_exists($queue_id) || !isset($queues[$queue_id]))
            {
                $this->queues[$queue_id] = msg_get_queue($queue_id);
            }

            $msg = [
                'id_user' => $sms['id_user'],
                'id_scheduled' => $sms['id_scheduled'],
                'text' => $sms['text'],
                'origin' => $sms['origin'],
                'destination' => $sms['destination'],
                'flash' => $sms['flash'],
            ];

            msg_send($this->queues[$queue_id], QUEUE_TYPE_SEND_MSG, $msg);

            $this->internal_scheduled->delete($sms['id_scheduled']);
        }
    }


    public function on_start()
    {
        $this->logger->info("Starting Sender with pid " . getmypid());
        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
    }


    public function on_stop() 
    {
        $this->logger->info("Stopping Sender with pid " . getmypid ());

        //Delete queues on daemon close
        foreach ($this->queues as $queue_id => $queue)
        {
            $this->logger->info("Closing queue : " . $queue_id);
            msg_remove_queue($queue);
        }
    }


    public function handle_other_signals($signal)
    {
        $this->logger->info("Signal not handled by " . $this->name . " Daemon : " . $signal);
    }
}
