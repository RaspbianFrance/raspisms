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
     * Cette classe gère les accès bdd pour les transferes.
     */
    class Transfer extends \descartes\Model
    {
        /**
         * Get all transfers.
         *
         * @return array
         */
        public function get_all()
        {
            return $this->_select('transfer');
        }

        /**
         * Get transfers not in progress.
         *
         * @return array
         */
        public function get_not_in_progress()
        {
            return $this->_select('transfer', ['progress' => false]);
        }

        /**
         * Retourne une entrée par son id.
         *
         * @param int $id : L'id de l'entrée
         *
         * @return array : L'entrée
         */
        public function get($id)
        {
            $transfers = $this->_select('transfer', ['id' => $id]);

            return isset($transfers[0]) ? $transfers[0] : false;
        }

        /**
         * Retourne une liste de transferes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('transfer', [], null, false, $limit, $offset);
        }

        /**
         * Retourne une liste de transferes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM transfer
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime un transfer.
         *
         * @param array $id : l'id de l'entrée à supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM transfer
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert un transfer.
         *
         * @param array $transfer : La transfere à insérer
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($transfer)
        {
            $result = $this->_insert('transfer', $transfer);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour un transfer par son id.
         *
         * @param int   $id       : L'id de la transfer à modifier
         * @param array $transfer : Les données à mettre à jour pour la transfere
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $transfer)
        {
            return $this->_update('transfer', $transfer, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('transfer');
        }
    }
