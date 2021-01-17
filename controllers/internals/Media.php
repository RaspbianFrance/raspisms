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

    class Media extends StandardController
    {
        protected $model;

        /**
         * Create a media.
         *
         * @param int   $id_user      : Id of the user
         * @param int   $id_scheduled : Id of the scheduled
         * @param array $media        : $_FILES media array
         *
         * @return bool : false on error, new media id else
         */
        public function create(int $id_user, int $id_scheduled, array $media): bool
        {
            $internal_scheduled = new Scheduled($this->bdd);
            $scheduled = $internal_scheduled->get_for_user($id_user, $id_scheduled);
            if (!$scheduled)
            {
                return false;
            }

            $result_upload_media = \controllers\internals\Tool::upload_file($media);
            if (false === $result_upload_media['success'])
            {
                return false;
            }

            $data = [
                'id_scheduled' => $id_scheduled,
                'path' => $result_upload_media['content'],
            ];

            return (bool) $this->get_model()->insert($data);
        }

        /**
         * Update a media for a user.
         *
         * @param int    $id_user      : user id
         * @param int    $id_media     : Media id
         * @param int    $id_scheduled : Id of the scheduled
         * @param string $path         : Path of the file
         *
         * @return bool : false on error, true on success
         */
        public function update_for_user(int $id_user, int $id_media, int $id_scheduled, string $path): bool
        {
            $media = [
                'id_scheduled' => $id_scheduled,
                'path' => $path,
            ];

            $internal_scheduled = new Scheduled($this->bdd);
            $scheduled = $this->get_for_user($id_user, $id_scheduled);
            if (!$scheduled)
            {
                return false;
            }

            return (bool) $this->get_model()->update_for_user($id_user, $id_media, $media);
        }

        /**
         * Delete a media for a user.
         *
         * @param int $id_user : User id
         * @param int $id      : Entry id
         *
         * @return int : Number of removed rows
         */
        public function delete_for_user(int $id_user, int $id_media): bool
        {
            $media = $this->get_model()->get_for_user($id_user, $id_media);
            if (!$media)
            {
                return false;
            }

            unlink($media['path']);

            return $this->get_model()->delete_for_user($id_user, $id_media);
        }

        /**
         * Delete a media for a scheduled and a user.
         *
         * @param int $id_user      : User id
         * @param int $id_scheduled : Scheduled id to delete medias for
         *
         * @return int : Number of removed rows
         */
        public function delete_for_scheduled_and_user(int $id_user, int $id_scheduled): bool
        {
            $media = $this->get_model()->get_for_scheduled_and_user($id_user, $id_scheduled);
            if ($media)
            {
                unlink($media['path']);
            }

            return $this->get_model()->delete_for_scheduled_and_user($id_user, $id_scheduled);
        }

        /**
         * Find medias for a scheduled and a user.
         *
         * @param int $id_user      : User id
         * @param int $id_scheduled : Scheduled id to delete medias for
         *
         * @return mixed : Medias || false
         */
        public function get_for_scheduled_and_user(int $id_user, int $id_scheduled)
        {
            return $this->get_model()->get_for_scheduled_and_user($id_user, $id_scheduled);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Media($this->bdd);

            return $this->model;
        }
    }
