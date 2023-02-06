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
    const HMAC_ALGO = 'sha256';

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

        $data = [
            'url' => $url,
            'type' => $type,
        ];

        return $this->get_model()->update_for_user($id_user, $id, $data);
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
     * @param array  $body    : The body, an array depending on webhook type
     *
     * @return bool : False if no trigger, true else
     */
    public function trigger(int $id_user, string $type, array $body)
    {
        $internal_setting = new Setting($this->bdd);
        $internal_user = new User($this->bdd);
        $settings = $internal_setting->gets_for_user($id_user);

        if (!$settings['webhook'] ?? false)
        {
            return false;
        }

        $user = $internal_user->get($id_user);
        if (!$user)
        {
            return false;
        }

        $webhooks = $this->gets_for_type_and_user($id_user, $type);
        foreach ($webhooks as $webhook)
        {
            $timestamp = time();
            $webhook_random_id = $timestamp . '-' . bin2hex(openssl_random_pseudo_bytes(16));

            //signature is hexa string representing hmac sha256 of webhook_random_id
            $webhook_signature = hash_hmac(self::HMAC_ALGO, $webhook_random_id, $user['api_key']);

            $message = [
                'url' => $webhook['url'],
                'data' => [
                    'webhook_timestamp' => $timestamp,
                    'webhook_type' => $webhook['type'],
                    'webhook_random_id' => $webhook_random_id,
                    'webhook_signature' => $webhook_signature,
                    'body' => json_encode($body),
                ],
            ];

            $error_code = null;
            $queue = msg_get_queue(QUEUE_ID_WEBHOOK);
            msg_send($queue, QUEUE_TYPE_WEBHOOK, $message, true, true, $error_code);
        }

        return true;
    }

    /**
     * Get the model for the Controller.
     */
    protected function get_model(): \models\Webhook
    {
        $this->model = $this->model ?? new \models\Webhook($this->bdd);

        return $this->model;
    }
}
