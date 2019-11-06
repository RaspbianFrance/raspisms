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
     * Cette classe gère les accès bdd pour les groups.
     */
    class Group extends \descartes\Model
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
            $groups = $this->_select('group', ['id' => $id]);

            return isset($groups[0]) ? $groups[0] : false;
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
            $groups = $this->_select('group', ['name' => $name]);

            return isset($groups[0]) ? $groups[0] : false;
        }

        /**
         * Retourne une liste de groups sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('group', [], '', false, $limit, $offset);
        }

        /**
         * Retourne une liste de groups sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM group
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de groups sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function deletes($ids)
        {
            $query = ' 
                DELETE FROM group
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Supprime les liens group/contact pour un group précis.
         *
         * @param int $id_group : L'id du group pour lequel supprimer
         *
         * @return int : Le nmbre d'entrées modifiées
         */
        public function delete_group_contacts($id_group)
        {
            return $this->_delete('group_contact', ['id_group' => $id_group]);
        }

        /**
         * Insert une group.
         *
         * @param array $group : La group à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($group)
        {
            $result = $this->_insert('group', $group);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Insert un lien group/contact.
         *
         * @param int $id_group   : L'id du group à liéer
         * @param int $id_contact : L'id du contact à liéer
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert_group_contact($id_group, $id_contact)
        {
            $result = $this->_insert('group_contact', ['id_group' => $id_group, 'id_contact' => $id_contact]);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une group par son id.
         *
         * @param int   $id    : L'id de la group à modifier
         * @param array $group : Les données à mettre à jour pour la group
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $group)
        {
            return $this->_update('group', $group, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('group');
        }

        /**
         * Cette fonction retourne les contact pour un group.
         *
         * @param string $id : L'id du group
         *
         * @return array : Un tableau avec les contact
         */
        public function get_contacts($id)
        {
            $query = '
                SELECT * 
                FROM contact
                WHERE id IN (SELECT id_contact FROM group_contact WHERE id_group = :id)
            ';

            $params = [
                'id' => $id,
            ];

            return $this->_run_query($query, $params);
        }
    }
