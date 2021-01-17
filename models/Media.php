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

    /**
     * Cette classe gère les accès bdd pour les mediaes.
     */
    class Media extends StandardModel
    {
        /**
         * Return an entry by his id for a user.
         *
         * @param int $id_user : user id
         * @param int $id      : entry id
         *
         * @return array
         */
        public function get_for_user(int $id_user, int $id)
        {
            $query = '
                SELECT * FROM `' . $this->get_table_name() . '`
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
                AND id = :id
            ';

            $params = [
                'id' => $id,
                'id_user' => $id_user,
            ];

            $receiveds = $this->_run_query($query, $params);

            return $receiveds[0] ?? [];
        }

        /**
         * Return all entries for a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            $query = '
                SELECT * FROM `' . $this->get_table_name() . '`
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
            ';

            $params = [
                'id_user' => $id_user,
            ];

            $receiveds = $this->_run_query($query, $params);
        }

        /**
         * Return a media for a user and a scheduled.
         *
         * @param int $id_user      : user id
         * @param int $id_scheduled : scheduled id
         *
         * @return array
         */
        public function get_for_scheduled_and_user(int $id_user, int $id_scheduled)
        {
            $query = '
                SELECT * FROM `' . $this->get_table_name() . '`
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
                AND id_scheduled = :id_scheduled
            ';

            $params = [
                'id_user' => $id_user,
                'id_scheduled' => $id_scheduled,
            ];

            $receiveds = $this->_run_query($query, $params);
            if (!$receiveds)
            {
                return false;
            }

            return $receiveds[0];
        }

        /**
         * Return a list of media for a user.
         *
         * @param int $id_user : User id
         * @param int $limit   : Max results to return
         * @param int $offset  : Number of results to ignore
         */
        public function list_for_user($id_user, $limit, $offset)
        {
            $limit = (int) $limit;
            $offset = (int) $offset;

            $query = '
                SELECT * FROM media
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
                LIMIT ' . $limit . ' OFFSET ' . $offset;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return a list of medias in a group of ids and for a user.
         *
         * @param int   $id_user : user id
         * @param array $ids     : ids of medias to find
         *
         * @return array
         */
        public function gets_in_for_user(int $id_user, $ids)
        {
            $query = '
                SELECT * FROM media
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
                AND id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];
            $params['id_user'] = $id_user;

            return $this->_run_query($query, $params);
        }

        /**
         * Delete a entry by his id for a user.
         *
         * @param int $id_user : User id
         * @param int $id      : Entry id
         *
         * @return int : Number of removed rows
         */
        public function delete_for_user(int $id_user, int $id)
        {
            $query = '
                DELETE FROM media
                WHERE id = :id
                AND id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
            ';

            $params = ['id_user' => $id_user, 'id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Delete a entry by his id for a user.
         *
         * @param int $id_user      : User id
         * @param int $id_scheduled : Scheduled id
         *
         * @return int : Number of removed rows
         */
        public function delete_for_scheduled_and_user(int $id_user, int $id_scheduled)
        {
            $query = '
                DELETE FROM media
                WHERE id_scheduled = :id_scheduled
                AND id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
            ';

            $params = ['id_user' => $id_user, 'id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Update a media sms for a user.
         *
         * @param int   $id_user : User id
         * @param int   $id      : Entry id
         * @param array $data   : data to update
         *
         * @return int : number of modified rows
         */
        public function update_for_user(int $id_user, int $id, array $data)
        {
            $params = [];
            $sets = [];

            foreach ($data as $label => $value)
            {
                $label = preg_replace('#[^a-zA-Z0-9_]#', '', $label);
                $params['set_' . $label] = $value;
                $sets[] = '`' . $label . '` = :set_' . $label . ' ';
            }

            $query = '
                UPDATE `media`
                SET ' . implode(', ', $sets) . '
                WHERE id = :id
                AND id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
            ';

            $params['id'] = $id;
            $params['id_user'] = $id_user;

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Count number of media sms for user.
         *
         * @param int $id_user : user id
         *
         * @return int : Number of media SMS for user
         */
        public function count_for_user($id_user)
        {
            $query = '
                SELECT COUNT(id) as nb
                FROM media
                WHERE id_scheduled IN (SELECT id FROM scheduled WHERE id_user = :id_user)
            ';

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params)[0]['nb'] ?? 0;
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'media';
        }
    }
