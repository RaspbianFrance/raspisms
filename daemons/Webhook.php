<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Phone daemon class
 */
class Webhook extends AbstractDaemon
{
    private $webhook_queue;
    private $last_message_at;
    private $phone;
    private $adapter;
    private $bdd;

    /**
     * Constructor
     * @param array $phone : A phone table entry
     */
    public function __construct()
    {
        $name = 'RaspiSMS Daemon Webhook';

        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/raspisms.log', Logger::DEBUG));
        
        $pid_dir = PWD_PID;
        $additional_signals = [];
        $uniq = true; //Main server should be uniq

        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct($name, $logger, $pid_dir, $additional_signals, $uniq);
        
        parent::start();
    }


    public function run()
    {
        //Call message 
        $msgtype = null;
        $maxsize = 409600;
        $message = null;

        $find_message = true;
        while ($find_message)
        {
            msg_receive($this->webhook_queue, QUEUE_ID_WEBHOOK, $msgtype, $maxsize, $message);
            
            if (!$message)
            {
                $find_message = false;
                continue;
            }

            $this->logger->info('Trigger webhook : ' . json_encode($message));

            //Do the webhook http query
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $message['url']);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $message['datas']);
            curl_exec($curl);
            curl_close($curl);
        }

        usleep(0.5 * 1000000);
    }


    public function on_start()
    {
        //Set last message at to construct time
        $this->last_message_at = microtime(true);
        
        $this->webhook_queue = msg_get_queue(QUEUE_ID_WEBHOOK);

        $this->logger->info("Starting Webhook daemon with pid " . getmypid());
    }


    public function on_stop() 
    {
        //Delete queue on daemon close
        $this->logger->info("Closing queue : " . QUEUE_ID_WEBHOOK);
        msg_remove_queue($this->webhook_queue);

        $this->logger->info("Stopping Webhook daemon with pid " . getmypid ());
    }


    public function handle_other_signals($signal)
    {
        $this->logger->info("Signal not handled by " . $this->name . " Daemon : " . $signal);
    }
}
