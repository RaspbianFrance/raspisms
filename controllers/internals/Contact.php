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
         * Cette fonction retourne une liste des contactes sous forme d'un tableau.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des contactes
         */
        public function list($nb_entry = null, $page = null)
        {
            //Recupération des contactes
            return $this->model_contact->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des contactes sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des contactes
         */
        public function gets($ids)
        {
            //Recupération des contactes
            return $this->model_contact->gets($ids);
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
         * @param array $contact : Un tableau représentant la contacte à insérer
         * @param mixed $number
         * @param mixed $name
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle contacte insérée
         */
        public function create($number, $name)
        {
            $contact = [
                'number' => $number,
                'name' => $name,
            ];

            $result = $this->model_contact->insert($contact);
            if (!$result)
            {
                return $result;
            }

            $this->internal_event->create('CONTACT_ADD', 'Ajout contact : '.$name.' ('.\controllers\internals\Tool::phone_add_space($number).')');

            return $result;
        }

        /**
         * Cette fonction met à jour une série de contactes.
         *
         * @param mixed $id
         * @param mixed $number
         * @param mixed $name
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $number, $name)
        {
            $contact = [
                'number' => $number,
                'name' => $name,
            ];

            return $this->model_contact->update($id, $contact);
        }
    }
