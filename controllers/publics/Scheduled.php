<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    /**
     * Page des scheduleds.
     */
    class Scheduled extends \descartes\Controller
    {
        private $internal_scheduled;
        private $internal_phone;
        private $internal_contact;
        private $internal_group;
        private $internal_conditional_group;
        private $internal_media;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_scheduled = new \controllers\internals\Scheduled($bdd);
            $this->internal_phone = new \controllers\internals\Phone($bdd);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_group = new \controllers\internals\Group($bdd);
            $this->internal_conditional_group = new \controllers\internals\ConditionalGroup($bdd);
            $this->internal_media = new \controllers\internals\Media($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les scheduleds, sous forme d'un tableau permettant l'administration de ces scheduleds.
         */
        public function list()
        {
            $this->render('scheduled/list');
        }

        /**
         * Return scheduleds as json.
         */
        public function list_json()
        {
            $entities = $this->internal_scheduled->list_for_user($_SESSION['user']['id']);
            foreach ($entities as &$entity)
            {
                if ($entity['mms'])
                {
                    $entity['medias'] = $this->internal_media->gets_for_scheduled($entity['id']);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $entities]);
        }

        /**
         * Cette fonction va supprimer une liste de scheduleds.
         *
         * @param array int $_GET['ids'] : Les id des scheduledes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $scheduled = $this->internal_scheduled->get($id);
                if (!$scheduled || $scheduled['id_user'] !== $_SESSION['user']['id'])
                {
                    continue;
                }

                $this->internal_scheduled->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un scheduled.
         *
         * @param array? int $contacts_ids           : Ids of contacts to prefilled
         * @param array? int $groups_ids             : Ids of groups to prefilled
         * @param array? int $conditional_groups_ids : Ids of conditional groups to prefilled
         * @param $prefilled : If we have prefilled some fields (possible values : 'contacts', 'groups', 'conditional_groups', false)
         */
        public function add()
        {
            $now = new \DateTime();
            $less_one_minute = new \DateInterval('PT1M');
            $now->sub($less_one_minute);

            $id_user = $_SESSION['user']['id'];

            $contacts = $this->internal_contact->gets_for_user($id_user);
            $phones = $this->internal_phone->gets_for_user($id_user);

            $contact_ids = (isset($_GET['contact_ids']) && \is_array($_GET['contact_ids'])) ? $_GET['contact_ids'] : [];
            $group_ids = (isset($_GET['group_ids']) && \is_array($_GET['group_ids'])) ? $_GET['group_ids'] : [];
            $conditional_group_ids = (isset($_GET['conditional_group_ids']) && \is_array($_GET['conditional_group_ids'])) ? $_GET['conditional_group_ids'] : [];

            $prefilled_contacts = [];
            $prefilled_groups = [];
            $prefilled_conditional_groups = [];

            if ($contact_ids)
            {
                foreach ($this->internal_contact->gets_in_for_user($id_user, $contact_ids) as $contact)
                {
                    $prefilled_contacts[] = $contact['id'];
                }
            }
            elseif ($group_ids)
            {
                foreach ($this->internal_group->gets_in_for_user($id_user, $group_ids) as $group)
                {
                    $prefilled_groups[] = $group['id'];
                }
            }
            elseif ($conditional_group_ids)
            {
                foreach ($this->internal_conditional_group->gets_in_for_user($id_user, $conditional_group_ids) as $conditional_group)
                {
                    $prefilled_conditional_groups[] = $conditional_group['id'];
                }
            }

            $this->render('scheduled/add', [
                'now' => $now->format('Y-m-d H:i'),
                'contacts' => $contacts,
                'phones' => $phones,
                'prefilled_contacts' => $prefilled_contacts,
                'prefilled_groups' => $prefilled_groups,
                'prefilled_conditional_groups' => $prefilled_conditional_groups,
            ]);
        }

        /**
         * Cette fonction retourne la page d'édition des scheduleds.
         *
         * @param int... $ids : Les id des scheduledes à supprimer
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            if (!$ids)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez choisir des messages à mettre à jour !');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            $id_user = $_SESSION['user']['id'];

            $all_contacts = $this->internal_contact->gets_for_user($_SESSION['user']['id']);
            $phones = $this->internal_phone->gets_for_user($_SESSION['user']['id']);
            $scheduleds = $this->internal_scheduled->gets_in_for_user($_SESSION['user']['id'], $ids);

            //Pour chaque message on ajoute les numéros, les contacts & les groups
            foreach ($scheduleds as $key => $scheduled)
            {
                if (!$scheduled || $scheduled['id_user'] !== $_SESSION['user']['id'])
                {
                    continue;
                }

                $scheduleds[$key]['numbers'] = [];
                $scheduleds[$key]['contacts'] = [];
                $scheduleds[$key]['groups'] = [];
                $scheduleds[$key]['conditional_groups'] = [];

                $numbers = $this->internal_scheduled->get_numbers($scheduled['id']);
                foreach ($numbers as $number)
                {
                    $number['data'] = json_decode($number['data'] ?? '[]');
                    $scheduleds[$key]['numbers'][] = $number;
                }

                $contacts = $this->internal_scheduled->get_contacts($scheduled['id']);
                foreach ($contacts as $contact)
                {
                    $scheduleds[$key]['contacts'][] = (int) $contact['id'];
                }

                $groups = $this->internal_scheduled->get_groups($scheduled['id']);
                foreach ($groups as $group)
                {
                    $scheduleds[$key]['groups'][] = (int) $group['id'];
                }

                $medias = $this->internal_media->gets_for_scheduled($scheduled['id']);
                $scheduleds[$key]['medias'] = $medias;

                $conditional_groups = $this->internal_scheduled->get_conditional_groups($scheduled['id']);
                foreach ($conditional_groups as $conditional_group)
                {
                    $scheduleds[$key]['conditional_groups'][] = (int) $conditional_group['id'];
                }
            }

            $this->render('scheduled/edit', [
                'scheduleds' => $scheduleds,
                'phones' => $phones,
                'contacts' => $all_contacts,
            ]);
        }

        /**
         * Create a new scheduled message
         * (you must provide at least one entry in any of numbers, contacts, groups or conditional_groups).
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['at']                 : Date to send message for
         * @param string $_POST['text']               : Text of the message
         * @param ?bool  $_POST['flash']              : Is the message a flash message (by default false)
         * @param ?int   $_POST['id_phone']           : Id of the phone to send message from, if null use random phone
         * @param ?array $_POST['numbers']            : Numbers to send the message to
         * @param ?array $_POST['contacts']           : Numbers to send the message to
         * @param ?array $_POST['groups']             : Numbers to send the message to
         * @param ?array $_POST['conditional_groups'] : Numbers to send the message to
         * @param ?array $_FILES['medias']            : The media to link to a scheduled
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            $id_user = $_SESSION['user']['id'];
            $at = $_POST['at'] ?? false;
            $text = $_POST['text'] ?? false;
            $flash = (bool) ($_POST['flash'] ?? false);
            $id_phone = empty($_POST['id_phone']) ? null : $_POST['id_phone'];
            $numbers = $_POST['numbers'] ?? [];
            $numbers = is_array($numbers) ? $numbers : [$numbers];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];
            $conditional_groups = $_POST['conditional_groups'] ?? [];
            $files = $_FILES['medias'] ?? false;
            $csv_file = $_FILES['csv'] ?? false;

            //Iterate over files to re-create individual $_FILES array
            $files_arrays = [];
            if ($files && is_array($files['name']))
            {
                foreach ($files as $property_name => $files_values)
                {
                    foreach ($files_values as $file_key => $property_value)
                    {
                        if (!isset($files_arrays[$file_key]))
                        {
                            $files_arrays[$file_key] = [];
                        }

                        $files_arrays[$file_key][$property_name] = $property_value;
                    }
                }
            }

            //Remove empty files input
            foreach ($files_arrays as $key => $file)
            {
                if (UPLOAD_ERR_NO_FILE === $file['error'])
                {
                    unset($files_arrays[$key]);
                }
            }

            //Remove empty csv file input
            if ($csv_file && UPLOAD_ERR_NO_FILE === $csv_file['error'])
            {
                $csv_file = false;
            }

            if (empty($text))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous ne pouvez pas créer un Sms sans message.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (empty($at))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous ne pouvez pas créer un Sms sans date.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (!is_string($at))
            {
                \FlashMessage\FlashMessage::push('danger', 'La date doit être valide.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (!is_string($text))
            {
                \FlashMessage\FlashMessage::push('danger', 'Votre message doit être un texte.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (mb_strlen($text) > \models\Scheduled::SMS_LENGTH_LIMIT)
            {
                \FlashMessage\FlashMessage::push('danger', 'Votre message doit faire moins de ' . \models\Scheduled::SMS_LENGTH_LIMIT . ' caractères.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i'))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez fournir une date valide.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if ($csv_file)
            {
                $uploaded_file = \controllers\internals\Tool::read_uploaded_file($csv_file);
                if (!$uploaded_file['success'])
                {
                    \FlashMessage\FlashMessage::push('danger', 'Impossible de traiter ce fichier CSV : ' . $uploaded_file['content']);

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }

                try
                {
                    $csv_numbers = $this->internal_scheduled->parse_csv_numbers_file($uploaded_file['content']);
                    if (!$csv_numbers)
                    {
                        \FlashMessage\FlashMessage::push('danger', 'Aucun destinataire valide dans le fichier CSV, assurez-vous de fournir un fichier CSV valide.');

                        return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                    }

                    $numbers = array_merge($csv_numbers, $numbers);
                }
                catch (\Exception $e)
                {
                    \FlashMessage\FlashMessage::push('danger', 'Impossible de traiter ce fichier CSV : ' . $e->getMessage());

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }
            }

            foreach ($numbers as $key => $number)
            {
                // If number is not an array turn it into an array
                $number = is_array($number) ? $number : ['number' => $number, 'data' => []];
                $number['data'] = $number['data'] ?? [];
                $number['number'] = \controllers\internals\Tool::parse_phone($number['number'] ?? '');

                if (!$number['number'])
                {
                    unset($numbers[$key]);

                    continue;
                }

                $clean_data = [];
                foreach ($number['data'] as $data_key => $value)
                {
                    if ('' === $value)
                    {
                        continue;
                    }

                    $data_key = mb_ereg_replace('[\W]', '', $data_key);
                    $clean_data[$data_key] = (string) $value;
                }
                $clean_data = json_encode($clean_data);
                $number['data'] = $clean_data;

                $numbers[$key] = $number;
            }

            if (!$numbers && !$contacts && !$groups && !$conditional_groups)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez renseigner au moins un destinataire pour le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            //If mms is enable and we have medias uploaded
            $media_ids = [];
            if ($_SESSION['user']['settings']['mms'] && $files_arrays)
            {
                foreach ($files_arrays as $file)
                {
                    try
                    {
                        $new_media_id = $this->internal_media->create_from_uploaded_file_for_user($_SESSION['user']['id'], $file);
                    }
                    catch (\Exception $e)
                    {
                        \FlashMessage\FlashMessage::push('danger', 'Impossible d\'upload et d\'enregistrer le fichier ' . $file['name'] . ':' . $e->getMessage());

                        return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                    }

                    $media_ids[] = $new_media_id;
                }
            }

            $mms = (bool) count($media_ids);

            $scheduled_id = $this->internal_scheduled->create($id_user, $at, $text, $id_phone, $flash, $mms, $numbers, $contacts, $groups, $conditional_groups, $media_ids);
            if (!$scheduled_id)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le Sms a bien été créé pour le ' . $at . '.');

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction met à jour un scheduled.
         *
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['scheduleds'] : Un tableau des scheduledes avec leur nouvelle valeurs + les numbers, contacts et groups liées
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            $scheduleds = $_POST['scheduleds'] ?? [];

            $nb_update = 0;
            foreach ($scheduleds as $id_scheduled => $scheduled)
            {
                $id_user = $_SESSION['user']['id'];
                $at = $scheduled['at'] ?? false;
                $text = $scheduled['text'] ?? false;
                $id_phone = empty($scheduled['id_phone']) ? null : $scheduled['id_phone'];
                $flash = (bool) ($scheduled['flash'] ?? false);
                $numbers = $scheduled['numbers'] ?? [];
                $contacts = $scheduled['contacts'] ?? [];
                $groups = $scheduled['groups'] ?? [];
                $conditional_groups = $scheduled['conditional_groups'] ?? [];
                $files = $_FILES['scheduleds_' . $id_scheduled . '_medias'] ?? false;
                $csv_file = $_FILES['scheduleds_' . $id_scheduled . '_csv'] ?? false;
                $media_ids = $scheduled['media_ids'] ?? [];

                //Check scheduled exists and belong to user
                $scheduled = $this->internal_scheduled->get($id_scheduled);
                if (!$scheduled || $scheduled['id_user'] !== $id_user)
                {
                    continue;
                }

                //Iterate over files to re-create individual $_FILES array
                $files_arrays = [];
                if ($files && is_array($files['name']))
                {
                    foreach ($files as $property_name => $files_values)
                    {
                        foreach ($files_values as $file_key => $property_value)
                        {
                            if (!isset($files_arrays[$file_key]))
                            {
                                $files_arrays[$file_key] = [];
                            }

                            $files_arrays[$file_key][$property_name] = $property_value;
                        }
                    }
                }

                //Remove empty files input
                foreach ($files_arrays as $key => $file)
                {
                    if (UPLOAD_ERR_NO_FILE === $file['error'])
                    {
                        unset($files_arrays[$key]);
                    }
                }

                //Remove empty csv file input
                if ($csv_file && UPLOAD_ERR_NO_FILE === $csv_file['error'])
                {
                    $csv_file = false;
                }

                if (empty($text))
                {
                    continue;
                }

                if (empty($at))
                {
                    \FlashMessage\FlashMessage::push('danger', 'Vous ne pouvez pas créer un Sms sans date.');

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }

                if (!is_string($at))
                {
                    \FlashMessage\FlashMessage::push('danger', 'La date doit être valide.');

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }

                if (!is_string($text))
                {
                    \FlashMessage\FlashMessage::push('danger', 'Votre message doit être un texte.');

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }

                if (mb_strlen($text) > \models\Scheduled::SMS_LENGTH_LIMIT)
                {
                    \FlashMessage\FlashMessage::push('danger', 'Votre message doit faire moins de ' . \models\Scheduled::SMS_LENGTH_LIMIT . ' caractères.');

                    return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
                }

                if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i'))
                {
                    continue;
                }

                if ($csv_file)
                {
                    $uploaded_file = \controllers\internals\Tool::read_uploaded_file($csv_file);
                    if (!$uploaded_file['success'])
                    {
                        continue;
                    }

                    try
                    {
                        $csv_numbers = $this->internal_scheduled->parse_csv_numbers_file($uploaded_file['content']);
                        if (!$csv_numbers)
                        {
                            continue;
                        }

                        $numbers = array_merge($csv_numbers, $numbers);
                    }
                    catch (\Exception $e)
                    {
                        continue;
                    }
                }

                $numbers = is_array($numbers) ? $numbers : [$numbers];
                foreach ($numbers as $key => $number)
                {
                    // If number is not an array turn it into an array
                    $number = is_array($number) ? $number : ['number' => $number, 'data' => []];
                    $number['data'] = $number['data'] ?? [];
                    $number['number'] = \controllers\internals\Tool::parse_phone($number['number'] ?? '');

                    if (!$number['number'])
                    {
                        unset($numbers[$key]);

                        continue;
                    }

                    $clean_data = [];
                    foreach ($number['data'] as $data_key => $value)
                    {
                        if ('' === $value)
                        {
                            continue;
                        }

                        $data_key = mb_ereg_replace('[\W]', '', $data_key);
                        $clean_data[$data_key] = (string) $value;
                    }
                    $clean_data = json_encode($clean_data);
                    $number['data'] = $clean_data;

                    $numbers[$key] = $number;
                }

                if (!$numbers && !$contacts && !$groups && !$conditional_groups)
                {
                    continue;
                }

                //If mms is enable and we have medias uploaded
                if ($_SESSION['user']['settings']['mms'] && $files_arrays)
                {
                    foreach ($files_arrays as $file)
                    {
                        try
                        {
                            $new_media_id = $this->internal_media->create_from_uploaded_file_for_user($_SESSION['user']['id'], $file);
                        }
                        catch (\Exception $e)
                        {
                            continue 2;
                        }

                        $media_ids[] = $new_media_id;
                    }
                }

                //Ensure media_ids point to medias belongings to the current user
                foreach ($media_ids as $key => $media_id)
                {
                    $media = $this->internal_media->get($media_id);
                    if (!$media || $media['id_user'] !== $_SESSION['user']['id'])
                    {
                        unset($media_ids[$key]);
                    }
                }

                $mms = (bool) count($media_ids);

                $this->internal_scheduled->update_for_user($id_user, $id_scheduled, $at, $text, $id_phone, $flash, $mms, $numbers, $contacts, $groups, $conditional_groups, $media_ids);
                ++$nb_update;
            }

            if ($nb_update !== \count($scheduleds))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains SMS n\'ont pas été mis à jour.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les SMS ont été mis à jour.');

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }
    }
