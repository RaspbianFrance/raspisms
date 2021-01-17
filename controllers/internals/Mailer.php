<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mailing class.
 */
class Mailer extends \descartes\Controller
{
    private $log;
    private $mail;

    public function __construct()
    {
        $this->log = new Logger('Mailer');
        $this->log->pushHandler(new StreamHandler(PWD_LOGS . '/mail.log', Logger::DEBUG));

        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'utf-8';
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->isSMTP();
        $this->mail->Host = MAIL['SMTP']['HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = MAIL['SMTP']['USER'];
        $this->mail->Password = MAIL['SMTP']['PASS'];
        $this->mail->Port = MAIL['SMTP']['PORT'];
        $this->mail->setFrom(MAIL['FROM']);

        if (MAIL['SMTP']['TLS'])
        {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
    }

    /**
     * Send email.
     *
     * @param array   $destinations : Destinations address
     * @param string  $subject      : Message subject
     * @param string  $message      : Message
     * @param ?string $alt_message  : Alt Message for clients with no html support. Use default (null) if mail to send is textonly and not html.
     * @param array   $attachments  : List of path to attachment files
     *
     * @return bool : false on error, true else
     */
    public function send(array $destinations, string $subject, string $message, ?string $alt_message = null, array $attachments = [])
    {
        try
        {
            $mail = clone $this->mail;

            foreach ($destinations as $destination)
            {
                //Only use bcc to avoid leak
                $mail->addBCC($destination);
            }

            foreach ($attachments as $attachment)
            {
                $mail->addAttachment($attachment);
            }

            $mail->Subject = $subject;
            $mail->Body = $message;

            if ($alt_message)
            {
                $mail->isHTML(true);
                $mail->AltBody = $alt_message;
            }

            $mail->send();

            return true;
        }
        catch (\Throwable $t)
        {
            $this->log->error('Error sending mail : ' . $t);

            return false;
        }
    }

    /**
     * Enqueue an email for later sending.
     *
     * @param string $destination : email address to send email to
     * @param array  $settings    : Email settings
     * @param array  $data       : Data to inject into email template
     *
     * @return bool : true on success, false on error
     */
    public function enqueue(string $destination, array $settings, array $data): bool
    {
        $response = $this->generate_body($settings, $data);

        $message = [
            'destinations' => [$destination],
            'subject' => $settings['subject'],
            'body' => $response['body'],
            'alt_body' => $response['alt_body'],
        ];

        $error_code = null;
        $queue = msg_get_queue(QUEUE_ID_EMAIL);
        $success = msg_send($queue, QUEUE_TYPE_EMAIL, $message, true, true, $error_code);

        return (bool) $success;
    }

    /**
     * Generate an email body.
     *
     * @param array $settings : [
     *                        string 'type' => Internal RaspiSMS email type,
     *                        string 'subject' => Email subject,
     *                        string 'template' => Email template to use
     *                        ?string 'alt_template' => Template to use for alt message, if null ignore
     *                        ]
     * @param array : Data to inject into email template
     *
     * @return array [
     *               string 'body' => email body
     *               ?string 'alt_body' => email alternative body if needed
     *               ]
     */
    private function generate_body(array $settings, array $data): array
    {
        //Generate body of email
        ob_start();
        $this->render($settings['template'], $data);
        $body = ob_get_clean();

        //Generate alt body if needed
        $alt_body = null;
        if ($settings['alt_template'] ?? false)
        {
            ob_start();
            $this->render($settings['alt_template'], $data);
            $alt_body = ob_get_clean();
        }

        return [
            'body' => $body,
            'alt_body' => $alt_body,
        ];
    }
}
