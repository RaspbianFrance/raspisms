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

    class Quota extends StandardModel
    {
        /**
         * Get remaining credit for a date
         * if no quota for this user return max int
         * @param int $id_user : User id
         * @param \DateTime $at : date to get credit at
         * @return int : number of remaining credits
         */
        public function get_remaining_credit (int $id_user, \DateTime $at): int
        {
            $query = '
                SELECT (credit + additional - consumed) AS remaining_credit
                FROM quota
                WHERE id_user = :id_user
                AND start_date <= :at
                AND end_date > :at';

            $params = [
                'id_user' => $id_user,
                'at' => $at->format('Y-m-d H:i:s'),
            ];

            $result = $this->_run_query($query, $params);

            return ($result[0]['remaining_credit'] ?? PHP_INT_MAX);
        }
        
        /**
         * Get credit usage percent for a date
         * if no quota for this user return 0
         * @param int $id_user : User id
         * @param \DateTime $at : date to get usage percent at
         * @return float : percent of used credits
         */
        public function get_usage_percentage (int $id_user, \DateTime $at): int
        {
            $query = '
                SELECT (consumed / (credit + additional)) AS usage_percentage
                FROM quota
                WHERE id_user = :id_user
                AND start_date <= :at
                AND end_date > :at';

            $params = [
                'id_user' => $id_user,
                'at' => $at->format('Y-m-d H:i:s'),
            ];

            $result = $this->_run_query($query, $params);

            return ($result[0]['usage_percentage'] ?? 0);
        }
        
        /**
         * Consume some credit for a user
         * @param int $id_user : User id
         * @param int $quantity : Number of credits to consume
         * @return bool
         */
        public function consume_credit (int $id_user, int $quantity): int
        {
            $query = '
                UPDATE quota
                SET consumed = consumed + :quantity
                WHERE id_user = :id_user';

            $params = [
                'id_user' => $id_user,
                'quantity' => $quantity,
            ];

            return (bool) $this->_run_query($query, $params, \descartes\Model::ROWCOUNT);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'quota';
        }
    }
