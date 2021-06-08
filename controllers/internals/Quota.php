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
     * @param int $id_user : User id
     * @param int $credit  : Credit for this quota
     * @param bool $report_unused : Should unused credits be re-credited
     * @param bool $report_unused_additional : Should unused additional credits be re-credited
     * @param \DateTime $start_date : Starting date for the quota
     * @param ?\DateTime $expiration_date (optional) : Ending date for the quota
     * @param bool $auto_renew (optional) : Should the quota be automatically renewed after expiration_date
     * @param ?\DateInterval $renew_interval (optional) : Period to use for setting expiration_date on renewal
     * @param int $additional (optional) : Additionals credits
     *
     * @return mixed bool|int : False if cannot create smsstop, id of the new smsstop else
     */
    public function create(int $id_user, int $credit, bool $report_unused, bool $report_unused_additional, \DateTime $start_date, ?\DateTime $expiration_date = null, bool $auto_renew= false, ?\DateInterval $renew_interval = null, int $additional = 0)
    {
        $quota = [
            'id_user' => $id_user,
            'credit' => $credit,
            'report_unused' => $report_unused,
            'report_unused_additional' => $report_unused_additional,
            'start_date' => $start_date,
            'expiration_date' => $expiration_date,
            'auto_renew' => $auto_renew,
            'renew_interval' => $renew_interval,
            'additional' => $additional,
        ];

        return $this->get_model()->insert($quota);
    }

    /**
     * Update a quota.
     *
     *
     * @param int $id_user : User id
     * @param int $id_quota : Id of the quota to update
     * @param int $credit  : Credit for this quota
     * @param bool $report_unused : Should unused credits be re-credited
     * @param bool $report_unused_additional : Should unused additional credits be re-credited
     * @param \DateTime $start_date : Starting date for the quota
     * @param ?\DateTime $expiration_date (optional) : Ending date for the quota
     * @param bool $auto_renew (optional) : Should the quota be automatically renewed after expiration_date
     * @param ?\DateInterval $renew_interval (optional) : Period to use for setting expiration_date on renewal
     * @param int $additional (optional) : Additionals credits
     * @param int $consumed (optional) : Number of consumed credits
     *
     * @return mixed bool|int : False if cannot create smsstop, id of the new smsstop else
     */
    public function update_for_user(int $id_user, int $id_quota, int $credit, bool $report_unused, bool $report_unused_additional, \DateTime $start_date, ?\DateTime $expiration_date = null, bool $auto_renew= false, ?\DateInterval $renew_interval = null, int $additional = 0, int $consumed = 0)
    {
        $quota = [
            'id_user' => $id_user,
            'id_quota' => $id_quota,
            'credit' => $credit,
            'report_unused' => $report_unused,
            'report_unused_additional' => $report_unused_additional,
            'start_date' => $start_date,
            'expiration_date' => $expiration_date,
            'auto_renew' => $auto_renew,
            'renew_interval' => $renew_interval,
            'additional' => $additional,
            'consumed' => $consumed,
        ];

        return $this->get_model()->insert($quota);
    }

    /**
     * Check if we have enough credit
     * @param int $id_user : User id
     * @param int $needed : Number of credits we need
     * @return bool : true if we have enough credit, false else
     */
    public function has_enough_credit(int $id_user, int $needed)
    {
        $remaining_credit = $this->get_model()->get_remaining_credit($id_user, new \DateTime());
        return $remaining_credit >= $needed;
    }

    /**
     * Consume some credit
     * @param int $id_user : User id
     * @param int $quantity : Number of credits to consume
     * @return bool : True on success, false else
     */
    public function consume_credit (int $id_user, int $quantity)
    {
        $result = $this->get_model()->consume_credit($id_user, $quantity);

        //Enqueue verifications for quotas alerting
        $queue = msg_get_queue(QUEUE_ID_QUOTA);
        $message = ['id_user' => $id_user];
        msg_send($queue, QUEUE_TYPE_QUOTA, $message, true, true);

        return $result;
    }

    /**
     * Get quota usage percentage
     * @param int $id_user : User id
     * @return float : percentage of quota used
     */
    public function get_usage_percentage (int $id_user)
    {
        return $this->get_model()->get_usage_percentage($id_user, new \DateTime());
    }

    /**
     * Compute how many credit a message represent
     * this function count 160 chars per SMS if it can be send as GSM 03.38 encoding and 70 chars per SMS if it can only be send as UTF8
     * @param string $text : Message to send
     * @return int : Number of credit to send this message
     */
    public static function compute_credits_for_message ($text)
    {

        //Gsm 03.38 charset to detect if message is compatible or must use utf8
        $gsm0338 = array(
            '@','Δ',' ','0','¡','P','¿','p',
            '£','_','!','1','A','Q','a','q',
            '$','Φ','"','2','B','R','b','r',
            '¥','Γ','#','3','C','S','c','s',
            'è','Λ','¤','4','D','T','d','t',
            'é','Ω','%','5','E','U','e','u',
            'ù','Π','&','6','F','V','f','v',
            'ì','Ψ','\'','7','G','W','g','w',
            'ò','Σ','(','8','H','X','h','x',
            'Ç','Θ',')','9','I','Y','i','y',
            "\n",'Ξ','*',':','J','Z','j','z',
            'Ø',"\x1B",'+',';','K','Ä','k','ä',
            'ø','Æ',',','<','L','Ö','l','ö',
            "\r",'æ','-','=','M','Ñ','m','ñ',
            'Å','ß','.','>','N','Ü','n','ü',
            'å','É','/','?','O','§','o','à'
        );

        $is_gsm0338 = true;

        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++)
        {
            if (!in_array(mb_substr($utf8_string, $i, 1), $gsm0338))
            {
                $is_gsm0338 = false;
                break;
            }
        }

        return ($is_gsm0338 ? ceil($len / 160) : ceil($len / 70));
    }

    /**
     * Get the model for the Controller.
     */
    protected function get_model(): \descartes\Model
    {
        $this->model = $this->model ?? new \models\Quota($this->bdd);

        return $this->model;
    }
}
