<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    class PhoneReliabilityHistory extends StandardModel
    {
        /**
         * Find all unreliable phones for a user, based on sended sms status, rate limit, etc.
         * 
         * @param int $id_user : User id
         * @param string $sms_status : Status of SMS to use to calculate rate
         * @param float $rate_limit : Percentage of SMS matching status after which we consider the phone unreliable         
         * @param int $min_volume : Minimum number of sms we need to have to consider the statistic relevent
         * @param int $period : The time span in minutes from which SMS counting should begin.
         * @param int $grace_period : How long in minutes should we wait before including a SMS in counting
         * 
         * @return array : A list of unreliable phone for the user, with phone id, total number of sms, and rate of failed sms
         */
        public function find_unreliable_phones (int $id_user, string $sms_status, float $rate_limit, int $min_volume, int $period, int $grace_period)
        {
            return $this->_run_query("
                WITH recent_messages AS (
                    SELECT 
                        sended.id_phone AS id_phone,
                        COUNT(sended.id) AS total,
                        SUM(sended.status = :sms_status) AS unreliable
                    FROM 
                        sended
                    JOIN
                        phone
                    ON
                        sended.id_phone = phone.id
                    LEFT JOIN 
                        (
                            SELECT 
                                id_phone,
                                MAX(created_at) AS last_alert_time
                            FROM 
                                phone_reliability_history
                            WHERE
                                type = :sms_status
                            GROUP BY 
                                id_phone
                        ) AS last_alerts 
                    ON
                        sended.id_phone = last_alerts.id_phone
                    WHERE 
                        sended.id_user = :id_user
                    AND 
                        phone.status != 'disabled'
                    AND 
                        sended.at > IFNULL(last_alerts.last_alert_time, '1970-01-01')
                    AND
                        sended.at BETWEEN NOW() - INTERVAL :period MINUTE AND NOW() - INTERVAL :grace_period MINUTE
                    GROUP BY 
                        id_phone
                )
                SELECT
                    id_phone,
                    total,
                    unreliable,
                    (unreliable / total) AS rate
                FROM
                    recent_messages
                WHERE
                    total >= :min_volume
                AND 
                    (unreliable / total) >= :rate_limit;
            ", [
                'id_user' => $id_user,
                'sms_status' => $sms_status,
                'period' => $period,
                'grace_period' => $grace_period,
                'min_volume' => $min_volume,
                'rate_limit' => $rate_limit,
            ]);
        }
        
        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'phone_reliability_history';
        }
    }
