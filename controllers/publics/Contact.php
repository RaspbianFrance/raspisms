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
     * Page des contacts.
     */
    class Contact extends \descartes\Controller
    {
        private $internal_contact;
        private $internal_event;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les contacts, sous forme d'un tableau permettant l'administration de ces contacts.
         */
        public function list()
        {
            $contacts = $this->internal_contact->list_for_user($_SESSION['user']['id']);

            return $this->render('contact/list', ['contacts' => $contacts]);
        }

        /**
         * Cette fonction va supprimer une liste de contacts.
         *
         * @param array int $_GET['ids'] : Les id des contactes à supprimer
         * @param mixed     $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_contact->delete_for_user($_SESSION['user']['id'], $id);
            }

            return $this->redirect(\descartes\Router::url('Contact', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un contact.
         */
        public function add()
        {
            $this->render('contact/add');
        }

        /**
         * Cette fonction retourne la page d'édition des contacts.
         *
         * @param int... $ids : Les id des contactes à supprimer
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];
            $id_user = $_SESSION['user']['id'];

            $contacts = $this->internal_contact->gets_in_for_user($id_user, $ids);

            if (!$contacts)
            {
                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            foreach ($contacts as &$contact)
            {
                if ($contact['datas'])
                {
                    $contact['datas'] = json_decode($contact['datas']);
                }
            }

            $this->render('contact/edit', [
                'contacts' => $contacts,
            ]);
        }

        /**
         * Cette fonction insert un nouveau contact.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name']  : Le nom du contact
         * @param string $_POST['phone'] : Le numero de téléphone du contact
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            $name = $_POST['name'] ?? false;
            $number = $_POST['number'] ?? false;
            $id_user = $_SESSION['user']['id'];
            $datas = $_POST['datas'] ?? [];

            if (!$name || !$number)
            {
                \FlashMessage\FlashMessage::push('danger', 'Des champs sont manquants !');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            $number = \controllers\internals\Tool::parse_phone($number);
            if (!$number)
            {
                \FlashMessage\FlashMessage::push('danger', 'Numéro de téléphone incorrect.');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            $clean_datas = [];
            foreach ($datas as $key => $value)
            {
                if ('' === $value)
                {
                    continue;
                }

                $key = mb_ereg_replace('[\W]', '', $key);
                $clean_datas[$key] = (string) $value;
            }

            $clean_datas = json_encode($clean_datas);

            if (!$this->internal_contact->create($id_user, $number, $name, $clean_datas))
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce contact.');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le contact a bien été créé.');

            return $this->redirect(\descartes\Router::url('Contact', 'list'));
        }

        /**
         * Cette fonction met à jour une contacte.
         *
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['contacts'] : Un tableau des contactes avec leur nouvelle valeurs
         *
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            if (![$_POST['contacts']])
            {
                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $nb_contacts_update = 0;
            foreach ($_POST['contacts'] as $id_contact => $contact)
            {
                $name = $contact['name'] ?? false;
                $number = $contact['number'] ?? false;
                $id_user = $_SESSION['user']['id'];
                $datas = $contact['datas'] ?? [];

                if (!$name || !$number)
                {
                    continue;
                }

                $number = \controllers\internals\Tool::parse_phone($number);
                if (!$number)
                {
                    continue;
                }

                $clean_datas = [];
                foreach ($datas as $key => $value)
                {
                    if ('' === $value)
                    {
                        continue;
                    }

                    $key = mb_ereg_replace('[\W]', '', $key);
                    $clean_datas[$key] = (string) $value;
                }
                $clean_datas = json_encode($clean_datas);

                $nb_contacts_update += (int) $this->internal_contact->update_for_user($id_user, $id_contact, $number, $name, $clean_datas);
            }

            if ($nb_contacts_update !== \count($_POST['contacts']))
            {
                \FlashMessage\FlashMessage::push('danger', 'Certains contacts n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            \FlashMessage\FlashMessage::push('success', 'Tous les contacts ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('Contact', 'list'));
        }

        /**
         * Allow to import a contacts list.
         *
         * @param string $csrf : Csrf token
         * @param $_FILES['contacts_list_file'] : A csv file of the contacts to import
         */
        public function import(string $csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $id_user = $_SESSION['user']['id'];

            $upload_array = $_FILES['contacts_list_file'] ?? false;
            if (!$upload_array)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez fournir un fichier de contacts à importer.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $read_file = \controllers\internals\Tool::read_uploaded_file($upload_array);
            if (!$read_file['success'])
            {
                \FlashMessage\FlashMessage::push('danger', $read_file['content']);

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            //Try to import file
            $invalid_type = false;
            switch ($read_file['mime_type'])
            {
                case 'text/csv':
                    $result = $this->internal_contact->import_csv($id_user, $read_file['content']);

                    break;
                case 'application/json':
                    $result = $this->internal_contact->import_json($id_user, $read_file['content']);

                    break;
                default:
                    $invalid_type = true;
            }

            if ($invalid_type)
            {
                \FlashMessage\FlashMessage::push('danger', 'Le type de fichier n\'est pas valide.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            if (false === $result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Le fichier contient des erreurs. Impossible d\'importer les contacts.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $msg = (1 === $result ? '1' : 'Pas de') . ' nouveau contact inséré.';
            if ($result > 1)
            {
                $msg = $result . ' nouveaux contacts ont été insérés.';
            }

            \FlashMessage\FlashMessage::push('success', $msg);

            return $this->redirect(\descartes\Router::url('Contact', 'list'));
        }

        /**
         * Allow to export a contacts list.
         *
         * @param $format : Format to export contacts to
         */
        public function export(string $format)
        {
            $id_user = $_SESSION['user']['id'];

            //Try to export contacts
            $invalid_type = false;
            switch ($format)
            {
                case 'csv':
                    $result = $this->internal_contact->export_csv($id_user);

                    break;
                case 'json':
                    $result = $this->internal_contact->export_json($id_user);

                    break;
                default:
                    $invalid_type = true;
            }

            if ($invalid_type)
            {
                \FlashMessage\FlashMessage::push('danger', 'Le format demandé n\'est pas supporté.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            if (false === $result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Nous ne sommes par parveu à exporté les contacts.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $result['headers'] = $result['headers'] ?? [];
            foreach ($result['headers'] as $header)
            {
                header($header);
            }

            echo $result['content'];
        }

        /**
         * Cette fonction retourne la liste des contacts sous forme JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_contact->list_for_user($_SESSION['user']['id']));
        }
    }
