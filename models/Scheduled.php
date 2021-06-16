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

    class Scheduled extends StandardModel
    {
        /**
         * Return numbers for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_numbers(int $id_scheduled)
        {
            return $this->_select('scheduled_number', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Return contacts for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_contacts(int $id_scheduled)
        {
            $query = 'SELECT * FROM contact WHERE id IN (SELECT id_contact FROM scheduled_contact WHERE id_scheduled = :id_scheduled)';
            $params = ['id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params);
        }

        /**
         * Return groups for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_groups(int $id_scheduled)
        {
            $query = 'SELECT * FROM `group` WHERE id IN (SELECT id_group FROM scheduled_group WHERE id_scheduled = :id_scheduled)';
            $params = ['id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params);
        }

        /**
         * Return conitional groups for a scheduled message.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return array
         */
        public function get_conditional_groups(int $id_scheduled)
        {
            $query = 'SELECT * FROM `conditional_group` WHERE id IN (SELECT id_conditional_group FROM scheduled_conditional_group WHERE id_scheduled = :id_scheduled)';
            $params = ['id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params);
        }

        /**
         * Insert a number for a scheduled.
         *
         * @param int    $id_scheduled : Scheduled id
         * @param string $number       : Number
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_scheduled_number(int $id_scheduled, string $number)
        {
            $success = $this->_insert('scheduled_number', ['id_scheduled' => $id_scheduled, 'number' => $number]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Insert a relation between a scheduled and a contact.
         *
         * @param int $id_scheduled : Scheduled id
         * @param int $id_contact   : Group id
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_scheduled_contact_relation(int $id_scheduled, int $id_contact)
        {
            $success = $this->_insert('scheduled_contact', ['id_scheduled' => $id_scheduled, 'id_contact' => $id_contact]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Insert a relation between a scheduled and a group.
         *
         * @param int $id_scheduled : Scheduled id
         * @param int $id_group     : Group id
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_scheduled_group_relation(int $id_scheduled, int $id_group)
        {
            $success = $this->_insert('scheduled_group', ['id_scheduled' => $id_scheduled, 'id_group' => $id_group]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Insert a relation between a scheduled and a conditional group.
         *
         * @param int $id_scheduled         : Scheduled id
         * @param int $id_conditional_group : Group id
         *
         * @return mixed (bool|int) : False on error, new row id else
         */
        public function insert_scheduled_conditional_group_relation(int $id_scheduled, int $id_conditional_group)
        {
            $success = $this->_insert('scheduled_conditional_group', ['id_scheduled' => $id_scheduled, 'id_conditional_group' => $id_conditional_group]);

            return $success ? $this->_last_id() : false;
        }

        /**
         * Delete numbers for a scheduled.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return mixed int : Number of deleted rows
         */
        public function delete_scheduled_numbers(int $id_scheduled)
        {
            return $this->_delete('scheduled_number', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Delete contact scheduled relations for a scheduled.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return mixed int : Number of deleted rows
         */
        public function delete_scheduled_contact_relations(int $id_scheduled)
        {
            return $this->_delete('scheduled_contact', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Delete group scheduled relations for a scheduled.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return mixed int : Number of deleted rows
         */
        public function delete_scheduled_group_relations(int $id_scheduled)
        {
            return $this->_delete('scheduled_group', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Delete conditional group scheduled relations for a scheduled.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return mixed int : Number of deleted rows
         */
        public function delete_scheduled_conditional_group_relations(int $id_scheduled)
        {
            return $this->_delete('scheduled_conditional_group', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Get messages scheduled before a date for a number and a user.
         *
         * @param int $id_user : User id
         * @param $date : Date before which we want messages
         * @param string $number : Number for which we want messages
         *
         * @return array
         */
        public function gets_before_date_for_number_and_user(int $id_user, $date, string $number)
        {
            $query = '
                SELECT *
                FROM scheduled
                WHERE at <= :date
                AND id_user = :id_user
                AND (
                    id IN (
                        SELECT id_scheduled
                        FROM scheduled_number
                        WHERE number = :number
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_contact
                        WHERE id_contact IN (
                            SELECT id
                            FROM contact
                            WHERE number = :number
                        )
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_group
                        WHERE id_group IN (
                            SELECT id_group
                            FROM `group_contact`
                            WHERE id_contact IN (
                                SELECT id
                                FROM contact
                                WHERE number = :number
                            )
                        )
                    )
                )
            ';

            $params = [
                'id_user' => $id_user,
                'date' => $date,
                'number' => $number,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Get messages scheduled after a date for a number and a user.
         *
         * @param int $id_user : User id
         * @param $date : Date after which we want messages
         * @param string $number : Number for which we want messages
         *
         * @return array
         */
        public function gets_after_date_for_number_and_user(int $id_user, $date, string $number)
        {
            $query = '
                SELECT *
                FROM scheduled
                WHERE at > :date
                AND id_user = :id_user
                AND (
                    id IN (
                        SELECT id_scheduled
                        FROM scheduled_number
                        WHERE number = :number
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_contact
                        WHERE id_contact IN (
                            SELECT id
                            FROM contact
                            WHERE number = :number
                        )
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_group
                        WHERE id_group IN (
                            SELECT id_group
                            FROM `group_contact`
                            WHERE id_contact IN (
                                SELECT id
                                FROM contact
                                WHERE number = :number
                            )
                        )
                    )
                )
            ';

            $params = [
                'id_user' => $id_user,
                'date' => $date,
                'number' => $number,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Get scheduleds before a date.
         *
         * @param string $date : Date to get scheduleds before
         *
         * @return array
         */
        public function gets_before_date(string $date)
        {
            return $this->_select($this->get_table_name(), ['<=at' => $date]);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'scheduled';
        }
    }
