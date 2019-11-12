<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    /**
     * Cette classe gère les accès bdd pour les commandes.
     */
    class Command extends \descartes\Model
    {
        /**
         * Return a command by his id
         * @param int $id : command id
         * @return array
         */
        public function get($id)
        {
            return $this->_select_one('command', ['id' => $id]);
        }


        /**
         * Return a list of commands for a user
         * @param int $id_user : user id 
         * @param int $limit  : Number of command to return
         * @param int $offset : Number of command to ignore
         * @return array
         */
        public function list_for_user (int $id_user, $limit, $offset)
        {
            return $this->_select('command', ['id_user' => $id_user], null, false, $limit, $offset);
        }

        /**
         * Return a list of commands in a group of ids and for a user
         * @param int $id_user : user id
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets_in_for_user($id_user, $ids)
        {
            $query = ' 
                SELECT * FROM command
                WHERE id_user = :id_user
                AND id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];
            $params['id_user'] = $id_user;

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime une commande.
         *
         * @param array $id : l'id de l'entrée à supprimer
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM command
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une commande.
         *
         * @param array $command : La commande à insérer
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($command)
        {
            $result = $this->_insert('command', $command);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une commande par son id.
         *
         * @param int   $id      : L'id de la command à modifier
         * @param array $command : Les données à mettre à jour pour la commande
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $command)
        {
            return $this->_update('command', $command, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('command');
        }
    }
