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

namespace models;

    /**
     * Cette classe gère les accès bdd pour les contactes.
     */
    class Contact extends \descartes\Model
    {
        /**
         * Retourne une entrée par son id.
         *
         * @param int $id : L'id de l'entrée
         *
         * @return array : L'entrée
         */
        public function get($id)
        {
            $contacts = $this->_select('contact', ['id' => $id]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }

        /**
         * Retourne une entrée par son numéro de tel.
         *
         * @param string $number : Le numéro de tél
         *
         * @return array : L'entrée
         */
        public function get_by_number($number)
        {
            $contacts = $this->_select('contact', ['number' => $number]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }

        /**
         * Retourne une entrée par son numéro de tel.
         *
         * @param string $name : Le numéro de tél
         *
         * @return array : L'entrée
         */
        public function get_by_name($name)
        {
            $contacts = $this->_select('contact', ['name' => $name]);

            return isset($contacts[0]) ? $contacts[0] : false;
        }
        
        
        /**
         * Get contacts of a particular group
         *
         * @param int $id_group : Id of the group we want contacts for
         * @return array
         */
        public function get_by_group($id_group)
        {
            $contacts = $this->_select('contact', ['id_group' => $id_group]);
            return $contacts;
        }

        /**
         * Retourne une liste de contactes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('contact', [], '', false, $limit, $offset);
        }

        /**
         * Retourne une liste de contactes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM contact
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprimer un contact par son id.
         *
         * @param array $id : un ou plusieurs id d'entrées à supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM contact
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une contacte.
         *
         * @param array $contact : La contacte à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($contact)
        {
            $result = $this->_insert('contact', $contact);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une contacte par son id.
         *
         * @param int   $id      : L'id de la contact à modifier
         * @param array $contact : Les données à mettre à jour pour la contacte
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $contact)
        {
            return $this->_update('contact', $contact, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table contact.
         *
         * @return int : Le nombre de contact
         */
        public function count()
        {
            return $this->_count('contact');
        }
    }
