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

    /**
     * Classe des contactes.
     */
    class Contact extends \descartes\InternalController
    {
        private $model_contact;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_contact = new \models\Contact($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * List contacts for a user
         * @param int $id_user : user id
         * @param mixed(int|bool) $nb_entry : Number of entry to return
         * @param mixed(int|bool) $page     : Pagination, will offset $nb_entry * $page results
         * @return array
         */
        public function list($id_user, $nb_entry = null, $page = null)
        {
            return $this->model_contact->list_for_user($id_user, $nb_entry, $nb_entry * $page);
        }
        
        /**
         * Return a contact
         * @param $id : contact id
         * @return array
         */
        public function get($id)
        {
            return $this->model_contact->get($id);
        }

        /**
         * Cette fonction retourne une liste des contactes sous forme d'un tableau.
         * @param int $id_user : user id
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des contactes
         */
        public function gets_for_user($id_user, $ids)
        {
            //Recupération des contactes
            return $this->model_contact->gets_for_user($id_user, $ids);
        }

        /**
         * Cette fonction retourne un contact par son numéro de tel.
         *
         * @param string $number : Le numéro du contact
         *
         * @return array : Le contact
         */
        public function get_by_number($number)
        {
            //Recupération des contactes
            return $this->model_contact->get_by_number($number);
        }
        
        
        /**
         * Return a contact for a user by a number
         * @param int $id_user : user id
         * @param string $number : Contact number
         * @return array
         */
        public function get_by_number_and_user($number, $id_user)
        {
            //Recupération des contactes
            return $this->model_contact->get_by_number_and_user($number, $id_user);
        }

        /**
         * Cette fonction retourne un contact par son name.
         *
         * @param string $name : Le name du contact
         *
         * @return array : Le contact
         */
        public function get_by_name($name)
        {
            //Recupération des contactes
            return $this->model_contact->get_by_name($name);
        }

        /**
         * Cette fonction permet de compter le nombre de contacts.
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_contact->count();
        }

        /**
         * Cette fonction va supprimer un contact.
         *
         * @param array $id : L'id du contact à supprimer
         *
         * @return int : Le nombre de contact supprimées;
         */
        public function delete($id)
        {
            return $this->model_contact->delete($id);
        }

        /**
         * Cette fonction insert une nouvelle contacte.
         *
         * @param int $id_user : user id
         * @param mixed $number
         * @param mixed $name
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle contacte insérée
         */
        public function create($id_user, $number, $name)
        {
            $contact = [
                'id_user' => $id_user,
                'number' => $number,
                'name' => $name,
            ];

            $result = $this->model_contact->insert($contact);
            if (!$result)
            {
                return $result;
            }

            $this->internal_event->create($id_user, 'CONTACT_ADD', 'Ajout contact : '.$name.' ('.\controllers\internals\Tool::phone_format($number).')');

            return $result;
        }

        /**
         * Cette fonction met à jour une série de contactes.
         *
         * @param mixed $id
         * @param int $id_user : user id
         * @param mixed $number
         * @param mixed $name
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $id_user, $number, $name)
        {
            $contact = [
                'id_user' => $id_user,
                'number' => $number,
                'name' => $name,
            ];

            return $this->model_contact->update($id, $contact);
        }
    }
