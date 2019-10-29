<?php
	namespace models;
	/**
     * Cette classe gère les accès bdd pour les commandes
	 */
	class Command extends \Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id ($id)
        {
            $commands = $this->select('command', ['id' => $id]);
            return isset($commands[0]) ? $commands[0] : false;
        }

		/**
		 * Retourne une liste de commandes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
		 */
		public function list ($limit, $offset)
        {
            $commands = $this->select('command', [], '', false, $limit, $offset);

	    	return $commands;
		}
        
        /**
		 * Retourne une liste de commandes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
		 */
        public function get_by_ids ($ids)
        {
			$query = " 
                SELECT * FROM command
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id 
            $generated_in = $this->generateInFromArray($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->runQuery($query, $params);
        }
        /**
         * Supprime une commande
         * @param array $id : l'id de l'entrée à supprimer
         * @return int : Le nombre de lignes supprimées
		 */
        public function delete_by_id ($id)
        {
			$query = " 
                DELETE FROM command
                WHERE id = :id";
     
            $params = ['id' => $id];

            return $this->runQuery($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une commande
         * @param array $command : La commande à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert ($command)
        {
            $result = $this->insertIntoTable('command', $command);

            if (!$result)
            {
                return false;
            }

            return $this->lastId();
        }

        /**
         * Met à jour une commande par son id
         * @param int $id : L'id de la command à modifier
         * @param array $command : Les données à mettre à jour pour la commande
         * @return int : le nombre de ligne modifiées
         */
        public function update ($id, $command)
        {
            return $this->updateTableWhere('command', $command, ['id' => $id]);
        }
        
        /**
         * Compte le nombre d'entrées dans la table
         * @return int : Le nombre d'entrées
         */
        public function count ()
        {
            return $this->countTable('command');
        }
    }
