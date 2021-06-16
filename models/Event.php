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

    class Event extends StandardModel
    {
        /**
         * Gets lasts x events for a user order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of events to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->_select('event', ['id_user' => $id_user], 'at', true, $nb_entry);
        }

        /**
         * Gets events for a type, since a date and eventually until a date (both included).
         *
         * @param int        $id_user : User id
         * @param string     $type    : Event type we want
         * @param \DateTime  $since   : Date to get events since
         * @param ?\DateTime $until   (optional) : Date until wich we want events, if not specified no limit
         *
         * @return array
         */
        public function get_events_by_type_and_date_for_user(int $id_user, string $type, \DateTime $since, ?\DateTime $until = null)
        {
            $where = [
                'id_user' => $id_user,
                'type' => $type,
                '>=at' => $since->format('Y-m-d H:i:s'),
            ];

            if (null !== $until)
            {
                $where['<=at'] = $until->format('Y-m-d H:i:s');
            }

            return $this->_select('event', $where, 'at');
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'event';
        }
    }
