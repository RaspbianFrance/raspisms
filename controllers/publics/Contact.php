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
            $contacts = $this->internal_contact->get_list(25, $page);
            $this->render('contact/list', ['contacts' => $contacts]);
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
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internal_contact->delete($id);
            }

            $this->redirect(\descartes\Router::url('Contact', 'list'));

            return true;
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
            global $db;
            $ids = $_GET['ids'] ?? [];

            $contacts = $this->internal_contact->get_by_ids($ids);

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
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            $name = $_POST['name'] ?? false;
            $number = $_POST['number'] ?? false;

            if (!$name || !$number)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Des champs sont manquants !');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            $number = \controllers\internals\Tool::parse_phone($number);
            if (!$number)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Numéro de téléphone incorrect.');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            if (!$this->internal_contact->create($number, $name))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de créer ce contact.');

                return $this->redirect(\descartes\Router::url('Contact', 'add'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le contact a bien été créé.');

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
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            $nb_contacts_update = 0;

            foreach ($_POST['contacts'] as $contact)
            {
                $nb_contacts_update += $this->internal_contact->update($contact['id'], $contact['number'], $contact['name']);
            }

            if ($nb_contacts_update !== \count($_POST['contacts']))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Certais contacts n\'ont pas pu êtres mis à jour.');

                return $this->redirect(\descartes\Router::url('Contact', 'list'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Tous les contacts ont été modifiés avec succès.');

            return $this->redirect(\descartes\Router::url('Contact', 'list'));
        }

        /**
         * Cette fonction retourne la liste des contacts sous forme JSON.
         */
        public function json_list()
        {
            header('Content-Type: application/json');
            echo json_encode($this->internal_contact->get_list());
        }
    }
