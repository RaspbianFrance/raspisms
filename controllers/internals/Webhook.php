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

class Webhook extends StandardController
{
    protected $bdd;
    protected $model;

    /**
     * Create a new webhook.
     *
     * @param int    $id_user : User id
     * @param string $url     : Webhook url
     * @param string $type    : Webhook type
     *
     * @return mixed bool|int : False if cannot create webhook, id of the new webhook else
     */
    public function create(int $id_user, string $url, string $type)
    {
        //Must ensure http(s) protocole for protection against ssrf
        if (!mb_ereg_match('^http(s?)://', $url))
        {
            return false;
        }

        $webhook = [
            'id_user' => $id_user,
            'url' => $url,
            'type' => $type,
        ];

        $result = $this->get_model()->insert($webhook);
        if (!$result)
        {
            return false;
        }

        return $result;
    }

    /**
     * Update a webhook.
     *
     * @param int    $id_user : User id
     * @param int    $id      : Webhook id
     * @param string $url     : Webhook url
     * @param string $type    : Webhook type
     *
     * @return mixed bool|int : False if cannot create webhook, id of the new webhook else
     */
    public function update_for_user(int $id_user, int $id, string $url, string $type)
    {
        //Must ensure http(s) protocole for protection against ssrf
        if (!mb_ereg_match('^http(s?)://', $url))
        {
            return false;
        }

        $datas = [
            'url' => $url,
            'type' => $type,
        ];

        return $this->get_model()->update_for_user($id_user, $id, $datas);
    }

    /**
     * Find all webhooks for a user and for a type of webhook.
     *
     * @param int    $id_user : User id
     * @param string $type    : Webhook type
     *
     * @return array
     */
    public function gets_for_type_and_user(int $id_user, string $type)
    {
        return $this->get_model()->gets_for_type_and_user($id_user, $type);
    }

    /**
     * Trigger a webhook and transmit the signal to webhook daemon if needed.
     *
     * @param int    $id_user : User to trigger the webhook for
     * @param string $type    : Type of webhook to trigger
     * @param array  $sms     : The sms [
     *                        int 'id' => SMS id,
     *                        string 'at' => SMS date,
     *                        string 'text' => sms body,
     *                        string 'origin' => sms origin (number or phone id)
     *                        string 'destination' => sms destination (number or phone id)
     *                        ]
     *
     * @return bool : False if no trigger, true else
     */
    public function trigger(int $id_user, string $type, array $sms)
    {
        $internal_setting = new Setting($this->bdd);
        $settings = $internal_setting->gets_for_user($id_user);

        if (!$settings['webhook'] ?? false)
        {
            return false;
        }

        $webhooks = $this->gets_for_type_and_user($id_user, $type);
        foreach ($webhooks as $webhook)
        {
            $message = [
                'url' => $webhook['url'],
                'datas' => [
                    'webhook_type' => $webhook['type'],
                    'id' => $sms['id'],
                    'at' => $sms['at'],
                    'text' => $sms['text'],
                    'origin' => $sms['origin'],
                    'destination' => $sms['destination'],
                ],
            ];

            $error_code = null;
            $queue = msg_get_queue(QUEUE_ID_WEBHOOK);
            $success = msg_send($queue, QUEUE_TYPE_WEBHOOK, $message, true, true, $error_code);

            return (bool) $success;
        }
    }

    /**
     * Get the model for the Controller.
     *
     * @return \descartes\Model
     */
    protected function get_model(): \descartes\Model
    {
        $this->model = $this->model ?? new \models\Webhook($this->bdd);

        return $this->model;
    }
}
