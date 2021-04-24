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
    const DEFAULT_CHMOD = 0660;

    protected $model;

    /**
     * Create a media.
     *
     * @param int   $id_user        : Id of the user
     * @param string $tmpfile_path  : Path of the temporary local copy of the media
     * @param ?string $extension    : Extension to use for the media
     *
     * @return int : Exception on error, new media id else
     */
    public function create(int $id_user, string $tmpfile_path, ?string $extension = null)
    {
        $user_path = \controllers\internals\Tool::create_user_public_path($id_user);
        if (!file_exists($tmpfile_path))
        {
            throw new \Exception('File ' . $tmpfile_path . ' does not exists.');
        }
        
        if (!is_readable($tmpfile_path))
        {
            throw new \Exception('File ' . $tmpfile_path . ' is not readable.');
        }

        $mimey = new \Mimey\MimeTypes;
        $extension = $extension ?? $mimey->getExtension(mime_content_type($tmpfile_path));

        $new_file_name = \controllers\internals\Tool::random_uuid() . '.' . $extension;
        $new_file_path = $user_path . '/' . $new_file_name;
        $new_file_relpath = $id_user . '/' . $new_file_name;

        if (!file_put_contents($new_file_path, 'a'))
        {
            throw new \Exception('pute de merde');
        }

        if (!rename($tmpfile_path, $new_file_path))
        {
            throw new \Exception('Cannot create file ' . $new_file_path);
        }

        if (!chown($new_file_path, fileowner($user_path)))
        {
            throw new \Exception('Cannot give file ' . $new_file_path . ' to user : ' . fileowner($user_path));
        }
        
        if (!chgrp($new_file_path, filegroup($user_path)))
        {
            throw new \Exception('Cannot give file ' . $new_file_path . ' to group : ' . filegroup($user_path));
        }

        if (!chmod($new_file_path, self::DEFAULT_CHMOD))
        {
            throw new \Exception('Cannot give file ' . $new_file_path . ' rights : ' . self::DEFAULT_CHMOD);
        }

        $data = [
            'path' => $new_file_relpath,
            'id_user' => $id_user,
        ];

        $new_media_id = $this->get_model()->insert($data);
        if (!$new_media_id)
        {
            throw new \Exception('Cannot insert media in database.');
        }

        return $new_media_id;
    }

    /**
     * Upload and create a media
     * 
     * @param int   $id_user      : Id of the user
     * @param array $file : array representing uploaded file, extracted from $_FILES['yourfile']
     * @return int : Raise exception on error or return new media id on success
     */
    public function create_from_uploaded_file_for_user(int $id_user, array $file)
    {
        $upload_result = \controllers\internals\Tool::read_uploaded_file($file);
        if ($upload_result['success'] !== true)
        {
            throw new \Exception($upload_result['content']);
        }

        //Move uploaded file to a tmp file
        if (!$tmp_file = tempnam('/tmp', 'raspisms-media-'))
        {
            throw new \Exception('Cannot create tmp file in /tmp to store the uploaded file.');
        }

        if (!move_uploaded_file($upload_result['tmp_name'], $tmp_file))
        {
            throw new \Exception('Cannot move uploaded file to : ' . $tmp_file);
        }
        
        return $this->create($id_user, $tmp_file, $upload_result['extension']);
    }

    /**
     * Link a media to a scheduled, a received or a sended message
     * @param int $id_media : Id of the media
     * @param string $resource_type : Type of resource to link the media to ('scheduled', 'received' or 'sended')
     * @param int $resource_id : Id of the resource to link the media to
     *
     * @return mixed bool|int : false on error, the new link id else
     */
    public function link_to(int $id_media, string $resource_type, int $resource_id)
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
    public function unlink_all_of(string $resource_type, int $resource_id)
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
     * @return mixed bool|int : False on error, else number of removed rows
     */
    public function delete_for_user(int $id_user, int $id_media): bool
    {
        $media = $this->get_model()->get_for_user($id_user, $id_media);
        if (!$media)
        {
            return false;
        }

        //Delete file
        try
        {
            $filepath = PWD_DATA_PUBLIC . '/' . $media['path'];
            if (file_exists($filepath))
            {
                unlink($filepath);
            }
        }
        catch (\Throwable $t)
        {
            return false;
        }

        return $this->get_model()->delete_for_user($id_user, $id_media);
    }

    /**
     * Find medias for a scheduled.
     *
     * @param int $id_scheduled : Scheduled id to fin medias for
     *
     * @return mixed : Medias || false
     */
    public function gets_for_scheduled(int $id_scheduled)
    {
        return $this->get_model()->gets_for_scheduled($id_scheduled);
    }

    /**
     * Find medias for a sended and a user.
     *
     * @param int $id_sended : Scheduled id to fin medias for
     *
     * @return mixed : Medias || false
     */
    public function gets_for_sended(int $id_sended)
    {
        return $this->get_model()->gets_for_sended($id_sended);
    }

    /**
     * Find medias for a received and a user.
     *
     * @param int $id_received : Scheduled id to fin medias for
     *
     * @return mixed : Medias || false
     */
    public function gets_for_received(int $id_received)
    {
        return $this->get_model()->gets_for_received($id_received);
    }

    /**
     * Find medias that are not used
     * @return array
     */
    public function gets_unused()
    {
        return $this->get_model()->gets_unused();
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
