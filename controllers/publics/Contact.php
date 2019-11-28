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
         *
         * @param mixed $page
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $contacts = $this->internal_contact->list_for_user($_SESSION['user']['id'], 25, $page);

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
                if ($value === "")
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

            if (!array($_POST['contacts']))
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
                    if ($value === "")
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
         * Cette fonction retourne la liste des contacts sous forme JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_contact->list_for_user($_SESSION['user']['id']));
        }
    }
