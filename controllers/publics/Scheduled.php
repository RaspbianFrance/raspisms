<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    /**
     * Page des scheduleds.
     */
    class Scheduled extends \descartes\Controller
    {
        private $internal_scheduled;

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

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les scheduleds, sous forme d'un tableau permettant l'administration de ces scheduleds.
         *
         * @param mixed $page
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $scheduleds = $this->internal_scheduled->list(25, $page);
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
                $this->internal_scheduled->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un scheduled.
         */
        public function add()
        {
            $now = new \DateTime();
            $less_one_minute = new \DateInterval('PT1M');
            $now->sub($less_one_minute);

            $this->render('scheduled/add', [
                'now' => $now->format('Y-m-d H:i'),
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

            $scheduleds = $this->internal_scheduled->gets($ids);

            //Pour chaque message on ajoute les numéros, les contacts & les groups
            foreach ($scheduleds as $key => $scheduled)
            {
                $scheduleds[$key]['numbers'] = [];
                $scheduleds[$key]['contacts'] = [];
                $scheduleds[$key]['groups'] = [];

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
            }

            $this->render('scheduled/edit', [
                'scheduleds' => $scheduleds,
            ]);
        }

        /**
         * Cette fonction insert un nouveau scheduled.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name']     : Le nom du scheduled
         * @param string $_POST['date']     : La date d'envoie du scheduled
         * @param string $_POST['numbers']  : Les numeros de téléphone du scheduled
         * @param string $_POST['contacts'] : Les contacts du scheduled
         * @param string $_POST['groups']   : Les groups du scheduled
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            $at = $_POST['at'] ?? false;
            $text = $_POST['text'] ?? false;
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];

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

            if (!$numbers && !$contacts && !$groups)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez renseigner au moins un destinataire pour le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            $scheduled_id = $this->internal_scheduled->create($at, $text, false, false, $numbers, $contacts, $groups);
            if (!$scheduled_id)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer le Sms.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le Sms a bien été créé pour le '.$at.'.');

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction met à jour une schedulede.
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

            $all_update_ok = true;

            foreach ($scheduleds as $id_scheduled => $scheduled)
            {
                $at = $scheduled['at'] ?? false;
                $text = $scheduled['text'] ?? false;
                $numbers = $scheduled['numbers'] ?? [];
                $contacts = $scheduled['contacts'] ?? [];
                $groups = $scheduled['groups'] ?? [];

                if (empty($text))
                {
                    $all_update_ok = false;

                    continue;
                }

                if (!\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($at, 'Y-m-d H:i'))
                {
                    $all_update_ok = false;

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

                if (!$numbers && !$contacts && !$groups)
                {
                    $all_update_ok = false;

                    continue;
                }

                $success = $this->internal_scheduled->update($id_scheduled, $text, $at, $numbers, $contacts, $groups);
                if (!$success)
                {
                    $all_update_ok = false;

                    continue;
                }
            }

            if (!$all_update_ok)
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains SMS n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les SMS ont été mis à jour.');

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }
    }
