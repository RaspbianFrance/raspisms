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

class Quota extends StandardController
{
    protected $model;

    /**
     * Create a new quota.
     *
     * @param int       $id_user                  : User id
     * @param int       $credit                   : Credit for this quota
     * @param int       $additional               : Additionals credits
     * @param bool      $report_unused            : Should unused credits be re-credited
     * @param bool      $report_unused_additional : Should unused additional credits be re-credited
     * @param bool      $auto_renew               : Should the quota be automatically renewed after expiration_date
     * @param string    $renew_interval           : Period to use for setting new expiration_date on renewal (format ISO_8601#Durations)
     * @param \DateTime $start_date               : Starting date for the quota
     * @param \DateTime $expiration_date          : Ending date for the quota
     *
     * @return mixed bool|int : False if cannot create quota, id of the new quota else
     */
    public function create(int $id_user, int $credit, int $additional, bool $report_unused, bool $report_unused_additional, bool $auto_renew, string $renew_interval, \DateTime $start_date, \DateTime $expiration_date)
    {
        $quota = [
            'id_user' => $id_user,
            'credit' => $credit,
            'report_unused' => $report_unused,
            'report_unused_additional' => $report_unused_additional,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'expiration_date' => $expiration_date->format('Y-m-d H:i:s'),
            'auto_renew' => $auto_renew,
            'renew_interval' => $renew_interval,
            'additional' => $additional,
        ];

        return $this->get_model()->insert($quota);
    }

    /**
     * Update a quota.
     *
     * @param int   $id_user  : User id
     * @param int   $id_quota : Quota to update id
     * @param array $quota    : Fields to update whith new values
     *
     * @return int : number of updated lines
     */
    public function update_for_user(int $id_user, $id_quota, array $quota)
    {
        return $this->get_model()->update_for_user($id_user, $id_quota, $quota);
    }

    /**
     * Check if we have enough credit.
     *
     * @param int $id_user : User id
     * @param int $needed  : Number of credits we need
     *
     * @return bool : true if we have enough credit, false else
     */
    public function has_enough_credit(int $id_user, int $needed)
    {
        $remaining_credit = $this->get_model()->get_remaining_credit($id_user, new \DateTime());

        return $remaining_credit >= $needed;
    }

    /**
     * Consume some credit.
     *
     * @param int $id_user  : User id
     * @param int $quantity : Number of credits to consume
     *
     * @return bool : True on success, false else
     */
    public function consume_credit(int $id_user, int $quantity)
    {
        $result = $this->get_model()->consume_credit($id_user, $quantity);

        //Write event
        $internal_event = new Event($this->bdd);
        $internal_event->create($id_user, 'QUOTA_CONSUME', 'Consume ' . $quantity . ' credits of SMS quota.');

        return $result;
    }

    /**
     * Get quota usage percentage.
     *
     * @param int $id_user : User id
     *
     * @return float : percentage of quota used
     */
    public function get_usage_percentage(int $id_user)
    {
        return $this->get_model()->get_usage_percentage($id_user, new \DateTime());
    }

    /**
     * Check if a message can be encoded as gsm0338 or if it must be UTF8.
     *
     * @param string $text : Message to send
     *
     * @return bool : True if gsm0338, false if UTF8
     */
    public static function is_gsm0338($text)
    {
        //Gsm 03.38 charset to detect if message is compatible or must use utf8
        $gsm0338 = [
            '@', 'Δ', ' ', '0', '¡', 'P', '¿', 'p',
            '£', '_', '!', '1', 'A', 'Q', 'a', 'q',
            '$', 'Φ', '"', '2', 'B', 'R', 'b', 'r',
            '¥', 'Γ', '#', '3', 'C', 'S', 'c', 's',
            'è', 'Λ', '¤', '4', 'D', 'T', 'd', 't',
            'é', 'Ω', '%', '5', 'E', 'U', 'e', 'u',
            'ù', 'Π', '&', '6', 'F', 'V', 'f', 'v',
            'ì', 'Ψ', '\'', '7', 'G', 'W', 'g', 'w',
            'ò', 'Σ', '(', '8', 'H', 'X', 'h', 'x',
            'Ç', 'Θ', ')', '9', 'I', 'Y', 'i', 'y',
            "\n", 'Ξ', '*', ':', 'J', 'Z', 'j', 'z',
            'Ø', "\x1B", '+', ';', 'K', 'Ä', 'k', 'ä',
            'ø', 'Æ', ',', '<', 'L', 'Ö', 'l', 'ö',
            "\r", 'æ', '-', '=', 'M', 'Ñ', 'm', 'ñ',
            'Å', 'ß', '.', '>', 'N', 'Ü', 'n', 'ü',
            'å', 'É', '/', '?', 'O', '§', 'o', 'à',
        ];

        $is_gsm0338 = true;

        $len = mb_strlen($text);
        for ($i = 0; $i < $len; ++$i)
        {
            if (!in_array(mb_substr($text, $i, 1), $gsm0338))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Compute how many credit a message represent
     * this function count 160 chars per SMS if it can be send as GSM 03.38 encoding and 70 chars per SMS if it can only be send as UTF8.
     *
     * @param string $text : Message to send
     *
     * @return int : Number of credit to send this message
     */
    public static function compute_credits_for_message($text)
    {
        $len = mb_strlen($text);
        $is_gsm0338 = self::is_gsm0338($text);

        return $is_gsm0338 ? ceil($len / 160) : ceil($len / 70);
    }

    /**
     * Do email alerting for quotas limit close and quotas limit reached.
     */
    public function alerting_for_limit_close_and_reached()
    {
        $internal_user = new User($this->bdd);
        $internal_event = new Event($this->bdd);

        $quotas_limit_close = $this->get_model()->get_quotas_for_limit_close();
        $quotas_limit_reached = $this->get_model()->get_quotas_for_limit_reached();

        foreach ($quotas_limit_close as $quota)
        {
            $user = $internal_user->get($quota['id_user']);

            if (!$user)
            {
                continue;
            }

            $quota_percentage = $quota['consumed'] / ($quota['credit'] + $quota['additional']);

            $mailer = new \controllers\internals\Mailer();
            $success = $mailer->enqueue($user['email'], EMAIL_QUOTA_LIMIT_CLOSE, ['percent' => $quota_percentage]);

            if (!$success)
            {
                echo 'Cannot enqueue alert for quota limit close for quota : ' . $quota['id'] . "\n";

                continue;
            }

            echo 'Enqueue alert for quota limit close for quota : ' . $quota['id'] . "\n";
            $internal_event->create($quota['id_user'], 'QUOTA_LIMIT_CLOSE', round($quota_percentage * 100, 2) . '% of SMS quota limit reached.');
        }

        foreach ($quotas_limit_reached as $quota)
        {
            $user = $internal_user->get($quota['id_user']);

            if (!$user)
            {
                continue;
            }

            $quota_percentage = $quota['consumed'] / ($quota['credit'] + $quota['additional']);

            $mailer = new \controllers\internals\Mailer();
            $success = $mailer->enqueue($user['email'], EMAIL_QUOTA_LIMIT_REACHED, ['expiration_date' => $quota['expiration_date']]);

            if (!$success)
            {
                echo 'Cannot enqueue alert for quota limit reached for quota : ' . $quota['id'] . "\n";

                continue;
            }

            echo 'Enqueue alert for quota limit reached for quota : ' . $quota['id'] . "\n";
            $internal_event->create($quota['id_user'], 'QUOTA_LIMIT_REACHED', 'Reached SMS quota limit.');
        }
    }

    /**
     * Do quota renewing.
     */
    public function renew_quotas()
    {
        $internal_user = new User($this->bdd);
        $internal_event = new Event($this->bdd);
        $quotas = $this->get_model()->get_quotas_to_be_renewed(new \DateTime());

        foreach ($quotas as $quota)
        {
            $user = $internal_user->get($quota['id_user']);

            if (!$user)
            {
                continue;
            }

            $unused_credit = $quota['credit'] - $quota['consumed'];
            $unused_additional = $unused_credit > 0 ? $quota['additional'] : $quota['additional'] + $unused_credit;

            $renew_interval = $quota['renew_interval'] ?? 'P0D';
            $new_start_date = new \DateTime($quota['expiration_date']);
            $new_expiration_date = clone $new_start_date;
            $new_expiration_date->add(new \DateInterval($renew_interval));

            $report = 0;
            if ($quota['report_unused'] && $unused_credit > 0)
            {
                $report += $unused_credit;
            }

            if ($quota['report_unused_additional'] && $unused_additional > 0)
            {
                $report += $unused_additional;
            }

            $updated_fields = [
                'start_date' => $new_start_date->format('Y-m-d H:i:s'),
                'expiration_date' => $new_expiration_date->format('Y-m-d H:i:s'),
                'additional' => $report,
                'consumed' => 0,
            ];

            $success = $this->update_for_user($user['id'], $quota['id'], $updated_fields);

            if (!$success)
            {
                echo 'Cannot update quota : ' . $quota['id'] . "\n";

                continue;
            }

            echo 'Update quota : ' . $quota['id'] . "\n";
            $internal_event->create($quota['id_user'], 'QUOTA_RENEWAL', 'Renew quota and report ' . $report . ' credits.');
        }
    }

    /**
     * Return the quota for a user if it exists.
     *
     * @param int $id_user : user id
     *
     * @return array
     */
    public function get_user_quota(int $id_user)
    {
        return $this->get_model()->get_user_quota($id_user);
    }

    /**
     * Get the model for the Controller.
     */
    protected function get_model(): \models\Quota
    {
        $this->model = $this->model ?? new \models\Quota($this->bdd);

        return $this->model;
    }
}
