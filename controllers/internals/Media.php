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
         * @param string $path        : path of the media in data dir
         *
         * @return mixed bool|int : false on error, new media id else
         */
        public function create(int $id_user, string $path): bool
        {
            $data = [
                'path' => $path,
                'id_user' => $id_user,
            ];

            return $this->get_model()->insert($data);
        }

        /**
         * Link a media to a scheduled, a received or a sended message
         * @param int $id_media : Id of the media
         * @param string $resource_type : Type of resource to link the media to ('scheduled', 'received' or 'sended')
         * @param int $resource_id : Id of the resource to link the media to
         *
         * @return mixed bool|int : false on error, the new link id else
         */
        public function link_to(int $id_media, int $resource_type, int $resource_id)
        {
            switch ($resource_type)
            {
                case 'scheduled':
                    return $this->get_model()->insert_media_scheduled($id_media, $resource_id);
                    break;
                
                case 'received':
                    return $this->get_model()->insert_media_received($id_media, $resource_id);
                    break;
                
                case 'sended':
                    return $this->get_model()->insert_media_sended($id_media, $resource_id);
                    break;

                default:
                    return false;
            }
        }
        
        
        /**
         * Unlink a media of a scheduled, a received or a sended message
         * @param int $id_media : Id of the media
         * @param string $resource_type : Type of resource to unlink the media of ('scheduled', 'received' or 'sended')
         * @param int $resource_id : Id of the resource to unlink the media of
         *
         * @return mixed bool : false on error, true on success
         */
        public function unlink_of(int $id_media, int $resource_type, int $resource_id)
        {
            switch ($resource_type)
            {
                case 'scheduled':
                    return $this->get_model()->delete_media_scheduled($id_media, $resource_id);
                    break;
                
                case 'received':
                    return $this->get_model()->delete_media_received($id_media, $resource_id);
                    break;
                
                case 'sended':
                    return $this->get_model()->delete_media_sended($id_media, $resource_id);
                    break;

                default:
                    return false;
            }
        }
        
        /**
         * Unlink all medias of a scheduled, a received or a sended message
         * @param string $resource_type : Type of resource to unlink the media of ('scheduled', 'received' or 'sended')
         * @param int $resource_id : Id of the resource to unlink the media of
         *
         * @return mixed bool : false on error, true on success
         */
        public function unlink_all_of(int $resource_type, int $resource_id)
        {
            switch ($resource_type)
            {
                case 'scheduled':
                    return $this->get_model()->delete_all_for_scheduled($resource_id);
                    break;
                
                case 'received':
                    return $this->get_model()->delete_all_for_received($resource_id);
                    break;
                
                case 'sended':
                    return $this->get_model()->delete_all_for_sended($resource_id);
                    break;

                default:
                    return false;
            }
        }

        /**
         * Update a media for a user.
         *
         * @param int    $id_user      : user id
         * @param int    $id_media     : Media id
         * @param string $path         : Path of the file
         *
         * @return bool : false on error, true on success
         */
        public function update_for_user(int $id_user, int $id_media, string $path): bool
        {
            $media = [
                'path' => $path,
            ];

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
         * Find medias for a scheduled and a user.
         *
         * @param int $id_user      : User id
         * @param int $id_scheduled : Scheduled id to fin medias for
         *
         * @return mixed : Medias || false
         */
        public function gets_for_scheduled_and_user(int $id_user, int $id_scheduled)
        {
            return $this->get_model()->gets_for_scheduled_and_user($id_user, $id_scheduled);
        }
        
        /**
         * Find medias for a sended and a user.
         *
         * @param int $id_user      : User id
         * @param int $id_sended : Scheduled id to fin medias for
         *
         * @return mixed : Medias || false
         */
        public function gets_for_sended_and_user(int $id_user, int $id_sended)
        {
            return $this->get_model()->gets_for_sended_and_user($id_user, $id_sended);
        }
        
        /**
         * Find medias for a received and a user.
         *
         * @param int $id_user      : User id
         * @param int $id_received : Scheduled id to fin medias for
         *
         * @return mixed : Medias || false
         */
        public function gets_for_received_and_user(int $id_user, int $id_received)
        {
            return $this->get_model()->gets_for_received_and_user($id_user, $id_received);
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
