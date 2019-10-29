<?php
    namespace models;

    /**
     * Cette classe gère les accès bdd pour les groupes
     */
    class Groupe extends \descartes\Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id($id)
        {
            $groupes = $this->_select('groupe', ['id' => $id]);
            return isset($groupes[0]) ? $groupes[0] : false;
        }
        
        /**
         * Retourne une entrée par son numéro de tel
         * @param string $name : Le numéro de tél
         * @return array : L'entrée
         */
        public function get_by_name($name)
        {
            $groupes = $this->_select('groupe', ['name' => $name]);
            return isset($groupes[0]) ? $groupes[0] : false;
        }

        /**
         * Retourne une liste de groupes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function get_list($limit, $offset)
        {
            $groupes = $this->_select('groupe', [], '', false, $limit, $offset);

            return $groupes;
        }
        
        /**
         * Retourne une liste de groupes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
         */
        public function get_by_ids($ids)
        {
            $query = " 
                SELECT * FROM groupe
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de groupes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @return int : Le nombre de lignes supprimées
         */
        public function delete_by_ids($ids)
        {
            $query = " 
                DELETE FROM groupe
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }
        
        /**
         * Supprime les liens groupe/contact pour un groupe précis
         * @param int $id_groupe : L'id du groupe pour lequel supprimer
         * @return int : Le nmbre d'entrées modifiées
         */
        public function delete_groupe_contact($id_groupe)
        {
            return $this->_delete('groupe_contact', ['id_groupe' => $id_groupe]);
        }

        /**
         * Insert une groupe
         * @param array $groupe : La groupe à insérer avec les champs name, script, admin & admin
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($groupe)
        {
            $result = $this->_insert('groupe', $groupe);

            if (!$result) {
                return false;
            }

            return $this->_last_id();
        }
        
        /**
         * Insert un lien groupe/contact
         * @param int $id_groupe : L'id du groupe à liéer
         * @param int $id_contact : L'id du contact à liéer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert_groupe_contact($id_groupe, $id_contact)
        {
            $result = $this->_insert('groupe_contact', ['id_groupe' => $id_groupe, 'id_contact' => $id_contact]);

            if (!$result) {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une groupe par son id
         * @param int $id : L'id de la groupe à modifier
         * @param array $groupe : Les données à mettre à jour pour la groupe
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $groupe)
        {
            return $this->_update('groupe', $groupe, ['id' => $id]);
        }
        
        /**
         * Compte le nombre d'entrées dans la table
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('groupe');
        }
        
        /**
         * Cette fonction retourne les contact pour un groupe
         * @param string $id : L'id du groupe
         * @return array : Un tableau avec les contact
         */
        public function get_contact($id)
        {
            $query = "
                SELECT * 
                FROM contact
                WHERE id IN (SELECT id_contact FROM groupe_contact WHERE id_groupe = :id)
            ";

            $params = array(
                'id' => $id,
            );

            return $this->_run_query($query, $params);
        }
    }
