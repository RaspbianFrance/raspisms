<?php
	namespace models;
	/**
     * Cette classe gère les accès bdd pour les sendedes
	 */
	class Sended extends \Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id ($id)
        {
            $sendeds = $this->select('sended', ['id' => $id]);
            return isset($sendeds[0]) ? $sendeds[0] : false;
        }

		/**
		 * Retourne une liste de sendedes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
		 */
		public function get_list ($limit, $offset)
        {
            $sendeds = $this->select('sended', [], '', false, $limit, $offset);

	    	return $sendeds;
		}
        
        /**
		 * Retourne une liste de sendedes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
		 */
        public function get_by_ids ($ids)
        {
			$query = " 
                SELECT * FROM sended
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id 
            $generated_in = $this->generateInFromArray($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->runQuery($query, $params);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date
         * @param int $nb_entry : Nombre d'entrée à retourner
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date ($nb_entry)
        {
            $sendeds = $this->select('sended', [], 'at', true, $nb_entry);
            return $sendeds;
        }
        
        /**
         * Cette fonction retourne une liste des sended sous forme d'un tableau
         * @param string $target : Le numéro auquel est envoyé le message
         * @return array : La liste des sended
         */	
		public function get_by_target ($target)
		{
            $sendeds = $this->select('sended', ['target' => $target]);
            return $sendeds;
        }


        /**
		 * Retourne une liste de sendedes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @return int : Le nombre de lignes supprimées
		 */
        public function delete_by_id ($id)
        {
			$query = " 
                DELETE FROM sended
                WHERE id = :id";
     
            $params = ['id' => $id];

            return $this->runQuery($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une sendede
         * @param array $sended : La sendede à insérer avec les champs name, script, admin & admin
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert ($sended)
        {
            $result = $this->insertIntoTable('sended', $sendeds);

            if (!$result)
            {
                return false;
            }

            return $this->lastId();
        }

        /**
         * Met à jour une sendede par son id
         * @param int $id : L'id de la sended à modifier
         * @param array $sended : Les données à mettre à jour pour la sendede
         * @return int : le nombre de ligne modifiées
         */
        public function update ($id, $sended)
        {
            return $this->updateTableWhere('sended', $sended, ['id' => $id]);
        }
        
        /**
         * Compte le nombre d'entrées dans la table
         * @return int : Le nombre d'entrées
         */
        public function count ()
        {
            return $this->countTable('sended');
        }

        /**
         * Récupère le nombre de SMS envoyés pour chaque jour depuis une date
         * @param \DateTime $date : La date depuis laquelle on veux les SMS
         * @return array : Tableau avec le nombre de SMS depuis la date
		 */
		public function count_by_day_since ($date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM sended
                WHERE at > :date
                GROUP BY at_ymd
            ";

            $params = array(
                'date' => $date,
            );

            return $this->runQuery($query, $params);
        }
        
    }
