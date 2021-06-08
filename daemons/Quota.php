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
 * Quota daemon class.
 */
class Quota extends AbstractDaemon
{
    private $quota_queue;
    private $last_message_at;
    private $bdd;

    /**
     * Constructor.
     *
     * @param array $phone : A phone table entry
     */
    public function __construct()
    {
        $name = 'RaspiSMS Daemon Quota';
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/daemons.log', Logger::DEBUG));
        $pid_dir = PWD_PID;
        $no_parent = false; //Rattach to parent so parent can stop it
        $additional_signals = [];
        $uniq = true; //Quota should be uniq

        //Construct the daemon
        parent::__construct($name, $logger, $pid_dir, $no_parent, $additional_signals, $uniq);

        parent::start();
    }

    public function run()
    {
        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');

        $find_message = true;
        while ($find_message)
        {
            //Call message
            $maxsize = 409600;
            $message = null;

            $error_code = null;
            $success = msg_receive($this->quota_queue, QUEUE_TYPE_QUOTA, $msgtype, $maxsize, $message, true, MSG_IPC_NOWAIT, $error_code); //MSG_IPC_NOWAIT == dont wait if no message found
            if (!$success && MSG_ENOMSG !== $error_code)
            {
                $this->logger->critical('Error for quota queue reading, error code : ' . $error_code);
                $find_message = false;

                continue;
            }

            if (!$message)
            {
                $find_message = false;

                continue;
            }

            $this->logger->info('Check alert level for quota : ' . json_encode($message['id']));

            $internal_settings = new \controllers\internals\Setting($this->bdd);
            $settings = $internal_user->gets_for_user($message['id_user']);

            $quota_alert_level = false;
            foreach ($settings as $name => $value)
            {
                if ('quota_alert_level', $name)
                {
                    $quota_alert_level = (float) $value;
                    break;
                }
            }

            if (!$quota_alert_level)
            {
                $this->logger->info('Alert is disabled for quota : ' . json_encode($message['id']));
                continue;
            }

            $internal_quota = new \controllers\internals\Quota($this->bdd);
            $usage_percentage = $internal_quota->get_usage_percentage($message['id_user']);
            if ($usage_percentage < $quota_alert_level)
            {
                continue;
            }

            //If already an alert event since quota start_date, then ignore alert
            $internal_event = new \controllers\internals\Event($this->bdd);
            $alert_events = $internal_event->get_events_by_type_and_date_for_user($message['id_user'], 'QUOTA_USAGE_CLOSE', new \DateTime($message['start_date']));
            if (count($alert_events))
            {
                continue;
            }

            //Alert level reached and no previous alert, we create a new alert
            $this->logger->info('Trigger alert for quota : ' . json_encode($message['id']));
            $internal_event->create($message['id_user'], 'QUOTA_USAGE_CLOSE', 'Reached ' . ($usage_percentage * 100) . '% of SMS quota.');

            $user = $internal_user->get($message['id_user']);
            if (!$user)
            {
                $this->logger->info('Cannot find user with id : ' . json_encode($message['id_user']));
                continue;
            }

            $mailer = new \controllers\internals\Mailer();
            $success = $mailer->enqueue($user['email'], EMAIL_QUOTA_USAGE_CLOSE, ['percent' => $usage_percentage]);
            if (!$success)
            {
                $this->logger->error('Cannot enqueue alerting email for quota usage.');

                continue;
            }

            $this->logger->info('Success sending email');
        }

        //Check quotas every 60 seconds
        usleep(60 * 1000000);
    }

    public function on_start()
    {
        //Set last message at to construct time
        $this->quota_queue = msg_get_queue(QUEUE_ID_QUOTA);

        $this->logger->info('Starting Quota daemon with pid ' . getmypid());
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
