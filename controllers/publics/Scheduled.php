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
            $scheduleds = $this->internal_scheduled->list_for_user($_SESSION['user']['id']);
            $this->render('scheduled/list', ['scheduleds' => $scheduleds]);
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
         * @param $prefilled : If we have prefilled some fields (possible values : 'contacts', 'groups', 'conditional_groups', false)
         */
        public function add($prefilled = false)
        {
            $now = new \DateTime();
            $less_one_minute = new \DateInterval('PT1M');
            $now->sub($less_one_minute);

            $id_user = $_SESSION['user']['id'];

            $contacts = $this->internal_contact->gets_for_user($id_user);
            $phones = $this->internal_phone->gets_for_user($id_user);

            $prefilled_contacts = [];
            $prefilled_groups = [];
            $prefilled_conditional_groups = [];

            if ($prefilled)
            {
                $ids = $_GET['ids'] ?? [];
            }

            if ('contacts' === $prefilled)
            {
                foreach ($this->internal_contact->gets_in_for_user($id_user, $ids) as $contact)
                {
                    $prefilled_contacts[] = $contact['id'];
                }
            }
            elseif ('groups' === $prefilled)
            {
                foreach ($this->internal_group->gets_in_for_user($id_user, $ids) as $group)
                {
                    $prefilled_groups[] = $group['id'];
                }
            }
            elseif ('conditional_groups' === $prefilled)
            {
                foreach ($this->internal_conditional_group->gets_in_for_user($id_user, $ids) as $conditional_group)
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
                    $scheduleds[$key]['numbers'][] = $number['number'];
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

                $media = $this->internal_media->get_for_scheduled_and_user($id_user, $scheduled['id']);
                $scheduleds[$key]['media'] = $media;

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
         * @param ?array $_FILES['media']             : The media to link to a scheduled
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
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];
            $conditional_groups = $_POST['conditional_groups'] ?? [];
            $media = $_FILES['media'] ?? false;

            if (empty($text))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous ne pouvez pas créer un Sms sans message.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i'))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez fournir une date valide.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            foreach ($numbers as $key => $number)
            {
                $number = \controllers\internals\Tool::parse_phone($number);

                if (!$number)
                {
                    unset($numbers[$key]);

                    continue;
                }

                $numbers[$key] = $number;
            }

            if (!$numbers && !$contacts && !$groups && !$conditional_groups)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez renseigner au moins un destinataire pour le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            $scheduled_id = $this->internal_scheduled->create($id_user, $at, $text, $id_phone, $flash, $numbers, $contacts, $groups, $conditional_groups);
            if (!$scheduled_id)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            //If mms is enabled, try to process a media to link to the scheduled
            if (!($_SESSION['user']['settings']['mms'] ?? false) || !$media)
            {
                \FlashMessage\FlashMessage::push('success', 'Le Sms a bien été créé pour le ' . $at . '.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            $success = $this->internal_media->create($id_user, $scheduled_id, $media);
            if (!$success)
            {
                \FlashMessage\FlashMessage::push('success', 'Le SMS a bien été créé mais le média n\'as pas pu être enregistré.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
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

                $scheduled = $this->internal_scheduled->get($id_scheduled);
                if (!$scheduled || $scheduled['id_user'] !== $id_user)
                {
                    continue;
                }

                if (empty($text))
                {
                    continue;
                }

                if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i'))
                {
                    continue;
                }

                foreach ($numbers as $key => $number)
                {
                    $number = \controllers\internals\Tool::parse_phone($number);
                    if (!$number)
                    {
                        unset($numbers[$key]);

                        continue;
                    }

                    $numbers[$key] = $number;
                }

                if (!$numbers && !$contacts && !$groups && !$conditional_groups)
                {
                    continue;
                }

                $success = $this->internal_scheduled->update_for_user($id_user, $id_scheduled, $at, $text, $id_phone, $flash, $numbers, $contacts, $groups, $conditional_groups);

                //Check for media
                /*
                $current_media = $scheduled['current_media'] ?? false;
                if (!$current_media)
                {
                    $this->internal_media->delete_for_scheduled_and_user($id_user, $id_scheduled);
                }

                $media = $_FILES['media_' . $id_scheduled] ?? false;
                if (!$media)
                {
                    $nb_update += (int) $success;
                    continue;
                }

                $success = $this->internal_media->create($id_user, $id_scheduled, $media);
                if (!$success)
                {
                    continue;
                }
                 */

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
