<?php
    namespace models;

    /**
     * Cette classe gère les accès bdd pour les smsstopes
     */
    class SmsStop extends \descartes\Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id($id)
        {
            $smsstops = $this->_select('smsstop', ['id' => $id]);
            return isset($smsstops[0]) ? $smsstops[0] : false;
        }
        
        /**
         * Retourne une entrée par son numéro de tel
         * @param string $number : Le numéro de tél
         * @return array : L'entrée
         */
        public function get_by_number($number)
        {
            $smsstops = $this->_select('smsstop', ['number' => $number]);
            return isset($smsstops[0]) ? $smsstops[0] : false;
        }

        /**
         * Retourne une liste de smsstopes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function get_list($limit, $offset)
        {
            $smsstops = $this->_select('smsstop', [], '', false, $limit, $offset);

            return $smsstops;
        }
        
        /**
         * Retourne une liste de smsstopes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
         */
        public function get_by_ids($ids)
        {
            $query = " 
                SELECT * FROM smsstop
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de smsstopes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @return int : Le nombre de lignes supprimées
         */
        public function delete_by_id($id)
        {
            $query = " 
                DELETE FROM smsstop
                WHERE id = :id";
     
            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une smsstope
         * @param array $smsstop : La smsstope à insérer avec les champs name, script, admin & admin
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($smsstop)
        {
            $result = $this->_insert('smsstop', $smsstop);

            if (!$result) {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une smsstope par son id
         * @param int $id : L'id de la smsstop à modifier
         * @param array $smsstop : Les données à mettre à jour pour la smsstope
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $smsstop)
        {
            return $this->_update('smsstop', $smsstop, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table smsstop
         * @return int : Le nombre de smsstop
         */
        public function count()
        {
            return $this->_count('smsstop');
        }
    }
