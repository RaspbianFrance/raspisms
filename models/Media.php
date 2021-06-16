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
         * Return all medias for a scheduled.
         *
         * @param int $id_scheduled : scheduled id
         *
         * @return array
         */
        public function gets_for_scheduled(int $id_scheduled)
        {
            $query = '
                SELECT m.id as id, m.id_user as id_user, m.path as path
                FROM `' . $this->get_table_name() . '` as m
                INNER JOIN media_scheduled as ms
                ON m.id = ms.id_media
                WHERE ms.id_scheduled = :id_scheduled
            ';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return all medias for a sended.
         *
         * @param int $id_sended : sended id
         *
         * @return array
         */
        public function gets_for_sended(int $id_sended)
        {
            $query = '
                SELECT m.id as id, m.id_user as id_user, m.path as path
                FROM `' . $this->get_table_name() . '` as m
                INNER JOIN media_sended as ms
                ON m.id = ms.id_media
                WHERE ms.id_sended = :id_sended
            ';

            $params = [
                'id_sended' => $id_sended,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Return all medias for a received.
         *
         * @param int $id_received : received id
         *
         * @return array
         */
        public function gets_for_received(int $id_received)
        {
            $query = '
                SELECT m.id as id, m.id_user as id_user, m.path as path
                FROM `' . $this->get_table_name() . '` as m
                INNER JOIN media_received as mr
                ON m.id = mr.id_media
                WHERE mr.id_received = :id_received
            ';

            $params = [
                'id_received' => $id_received,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Link a media to a scheduled.
         *
         * @param int $id_media     : Media id
         * @param int $id_scheduled : Scheduled id
         *
         * @return bool | int
         */
        public function insert_media_scheduled(int $id_media, int $id_scheduled)
        {
            $entry = [
                'id_media' => $id_media,
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_insert('media_scheduled', $entry) ? $this->_last_id() : false;
        }

        /**
         * Link a media to a received.
         *
         * @param int $id_media    : Media id
         * @param int $id_received : Scheduled id
         *
         * @return bool | int
         */
        public function insert_media_received(int $id_media, int $id_received)
        {
            $entry = [
                'id_media' => $id_media,
                'id_received' => $id_received,
            ];

            return $this->_insert('media_received', $entry) ? $this->_last_id() : false;
        }

        /**
         * Link a media to a sended.
         *
         * @param int $id_media  : Media id
         * @param int $id_sended : Scheduled id
         *
         * @return bool | int
         */
        public function insert_media_sended(int $id_media, int $id_sended)
        {
            $entry = [
                'id_media' => $id_media,
                'id_sended' => $id_sended,
            ];

            return $this->_insert('media_sended', $entry) ? $this->_last_id() : false;
        }

        /**
         * Unlink a media of a scheduled.
         *
         * @param int $id_media     : Media id
         * @param int $id_scheduled : Scheduled id
         *
         * @return bool | int
         */
        public function delete_media_scheduled(int $id_media, int $id_scheduled)
        {
            $where = [
                'id_media' => $id_media,
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_delete('media_scheduled', $where);
        }

        /**
         * Unlink a media of a received.
         *
         * @param int $id_media    : Media id
         * @param int $id_received : Scheduled id
         *
         * @return bool | int
         */
        public function delete_media_received(int $id_media, int $id_received)
        {
            $where = [
                'id_media' => $id_media,
                'id_received' => $id_received,
            ];

            return $this->_delete('media_received', $where);
        }

        /**
         * Unlink a media of a sended.
         *
         * @param int $id_media  : Media id
         * @param int $id_sended : Scheduled id
         *
         * @return bool | int
         */
        public function delete_media_sended(int $id_media, int $id_sended)
        {
            $where = [
                'id_media' => $id_media,
                'id_sended' => $id_sended,
            ];

            return $this->_delete('media_sended', $where);
        }

        /**
         * Unlink all medias of a scheduled.
         *
         * @param int $id_scheduled : Scheduled id
         *
         * @return bool | int
         */
        public function delete_all_for_scheduled(int $id_scheduled)
        {
            $where = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_delete('media_scheduled', $where);
        }

        /**
         * Unlink all medias of a received.
         *
         * @param int $id_received : Scheduled id
         *
         * @return bool | int
         */
        public function delete_all_for_received(int $id_received)
        {
            $where = [
                'id_received' => $id_received,
            ];

            return $this->_delete('media_received', $where);
        }

        /**
         * Unlink all medias of a sended.
         *
         * @param int $id_sended : Scheduled id
         *
         * @return bool | int
         */
        public function delete_all_for_sended(int $id_sended)
        {
            $where = [
                'id_sended' => $id_sended,
            ];

            return $this->_delete('media_sended', $where);
        }

        /**
         * Find all unused medias.
         *
         * @return array
         */
        public function gets_unused()
        {
            $query = '
                SELECT `media`.*
                FROM   `media`
                       LEFT JOIN `media_sended`
                               ON `media`.id = `media_sended`.id_media
                       LEFT JOIN `media_received`
                               ON `media`.id = `media_received`.id_media
                       LEFT JOIN `media_scheduled`
                               ON `media`.id = `media_scheduled`.id_media
                WHERE  `media_sended`.id IS NULL
                        AND `media_received`.id IS NULL
                        AND `media_scheduled`.id IS NULL 
            ';

            return $this->_run_query($query);
        }

        /**
         * Return table name.
         */
        protected function get_table_name(): string
        {
            return 'media';
        }
    }
