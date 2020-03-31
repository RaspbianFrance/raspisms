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
class Phone extends AbstractDaemon
{
    private $max_inactivity = 5 * 60;
    private $msg_queue;
    private $msg_queue_id;
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
        $this->msg_queue_id = (int) (QUEUE_ID_PHONE_PREFIX . $this->phone['id']);

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

        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');

        //Send smss in queue
        $this->send_smss();

        //Read received smss
        $this->read_smss();

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

        $this->msg_queue = msg_get_queue($this->msg_queue_id);
        $this->webhook_queue = msg_get_queue(QUEUE_ID_WEBHOOK);

        //Instanciate adapter
        $adapter_class = $this->phone['adapter'];
        $this->adapter = new $adapter_class($this->phone['adapter_datas']);

        $this->logger->info('Starting Phone daemon with pid ' . getmypid());
    }

    public function on_stop()
    {
        //Delete queue on daemon close
        $this->logger->info('Closing queue : ' . $this->msg_queue_id);
        msg_remove_queue($this->msg_queue);

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
        $find_message = true;
        while ($find_message)
        {
            //Call message
            $msgtype = null;
            $maxsize = 409600;
            $message = null;

            $error_code = null;
            $success = msg_receive($this->msg_queue, QUEUE_TYPE_SEND_MSG, $msgtype, $maxsize, $message, true, MSG_IPC_NOWAIT, $error_code); //MSG_IPC_NOWAIT == dont wait if no message found

            if (!$success && MSG_ENOMSG !== $error_code)
            {
                $this->logger->critical('Error reading MSG SEND Queue, error code : ' . $error_code);

                return false;
            }

            if (!$message)
            {
                $find_message = false;

                continue;
            }

            $internal_sended = new \controllers\internals\Sended($this->bdd);

            //Update last message time
            $this->last_message_at = microtime(true);

            $now = new \DateTime();
            $at = $now->format('Y-m-d H:i:s');

            $message['at'] = $at;

            $message['id_phone'] = $this->phone['id'];

            $this->logger->info('Try send message : ' . json_encode($message));

            $sended_sms_uid = $this->adapter->send($message['destination'], $message['text'], $message['flash']);
            if (!$sended_sms_uid)
            {
                $this->logger->error('Failed send message : ' . json_encode($message));
                $internal_sended->create($this->phone['id_user'], $this->phone['id'], $at, $message['text'], $message['destination'], $sended_sms_uid, $this->phone['adapter'], $message['flash'], 'failed');

                continue;
            }

            //Run webhook
            $internal_setting = new \controllers\internals\Setting($this->bdd);
            $user_settings = $internal_setting->gets_for_user($this->phone['id_user']);
            $this->process_for_webhook($message, 'send_sms', $user_settings);

            $this->logger->info('Successfully send message : ' . json_encode($message));

            $internal_sended->create($this->phone['id_user'], $this->phone['id'], $at, $message['text'], $message['destination'], $sended_sms_uid, $this->phone['adapter'], $message['flash']);
        }
    }

    /**
     * Read smss for a phone.
     */
    private function read_smss()
    {
        $internal_received = new \controllers\internals\Received($this->bdd);
        $internal_setting = new \controllers\internals\Setting($this->bdd);

        if (!$this->adapter->meta_support_read())
        {
            return true;
        }
        
        $smss = $this->adapter->read();
        if (!$smss)
        {
            return true;
        }

        //Get users settings
        $user_settings = $internal_setting->gets_for_user($this->phone['id_user']);

        //Process smss
        foreach ($smss as $sms)
        {
            $this->logger->info('Receive message : ' . json_encode($sms));

            $command_result = $this->process_for_command($sms);
            $this->logger->info('after command');
            $sms['text'] = $command_result['text'];
            $is_command = $command_result['is_command'];

            $this->process_for_webhook($sms, 'receive_sms', $user_settings);

            $this->process_for_transfer($sms, $user_settings);

            $internal_received->create($this->phone['id_user'], $this->phone['id'], $sms['at'], $sms['text'], $sms['origin'], 'unread', $is_command);
        }
    }

    /**
     * Process a sms to find if its a command and so execute it.
     *
     * @param array $sms : The sms
     *
     * @return array : ['text' => new sms text, 'is_command' => bool]
     */
    private function process_for_command(array $sms)
    {
        $internal_command = new \controllers\internals\Command($this->bdd);

        $is_command = false;
        $command = $internal_command->check_for_command($this->phone['id_user'], $sms['text']);
        if ($command)
        {
            $is_command = true;
            $sms['text'] = $command['updated_text'];
            exec($command['command']);
        }

        return ['text' => $sms['text'], 'is_command' => $is_command];
    }

    /**
     * Process a sms to transmit a webhook query to webhook daemon if needed.
     *
     * @param array  $sms           : The sms
     * @param string $webhook_type  : Type of webhook to trigger
     * @param array  $user_settings : Use settings
     */
    private function process_for_webhook(array $sms, string $webhook_type, array $user_settings)
    {
        if (!$user_settings['webhook'])
        {
            return false;
        }

        $internal_webhook = new \controllers\internals\Webhook($this->bdd);

        $webhooks = $internal_webhook->gets_for_type_and_user($this->phone['id_user'], $webhook_type);
        foreach ($webhooks as $webhook)
        {
            $message = [
                'url' => $webhook['url'],
                'datas' => [
                    'webhook_type' => $webhook['type'],
                    'at' => $sms['at'],
                    'text' => $sms['text'],
                    'origin' => $sms['origin'],
                    'destination' => $sms['destination'],
                ],
            ];

            $error_code = null;
            $success = msg_send($this->webhook_queue, QUEUE_TYPE_WEBHOOK, $message, true, true, $error_code);
            if (!$success)
            {
                $this->logger->critical('Failed send webhook message in queue, error code : ' . $error_code);
            }
        }
    }

    /**
     * Process a sms to transfer it by mail.
     *
     * @param array $sms           : The sms
     * @param array $user_settings : Use settings
     */
    private function process_for_transfer(array $sms, array $user_settings)
    {
        if (!$user_settings['transfer'])
        {
            return false;
        }

        $internal_user = new \controllers\internals\User($this->bdd);
        $user = $internal_user->get($this->phone['id_user']);

        if (!$user)
        {
            return false;
        }

        $this->logger->info('Transfer sms to ' . $user['email'] . ' : ' . json_encode($sms));

        \controllers\internals\Tool::send_email($user['email'], EMAIL_TRANSFER_SMS, ['sms' => $sms]);
    }
}
