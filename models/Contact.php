<?php
	namespace models;
	/**
     * Cette classe gère les accès bdd pour les contactes
	 */
	class Contact extends \Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id ($id)
        {
            $contacts = $this->select('contact', ['id' => $id]);
            return isset($contacts[0]) ? $contacts[0] : false;
        }
        
        /**
         * Retourne une entrée par son numéro de tel
         * @param string $number : Le numéro de tél
         * @return array : L'entrée
         */
        public function get_by_number ($number)
        {
            $contacts = $this->select('contact', ['number' => $number]);
            return isset($contacts[0]) ? $contacts[0] : false;
        }
        
        /**
         * Retourne une entrée par son numéro de tel
         * @param string $name : Le numéro de tél
         * @return array : L'entrée
         */
        public function get_by_name ($name)
        {
            $contacts = $this->select('contact', ['name' => $name]);
            return isset($contacts[0]) ? $contacts[0] : false;
        }

		/**
		 * Retourne une liste de contactes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
		 */
		public function get_list ($limit, $offset)
        {
            $contacts = $this->select('contact', [], '', false, $limit, $offset);

	    	return $contacts;
		}
        
        /**
		 * Retourne une liste de contactes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
		 */
        public function get_by_ids ($ids)
        {
			$query = " 
                SELECT * FROM contact
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id 
            $generated_in = $this->generateInFromArray($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->runQuery($query, $params);
        }

        /**
         * Supprimer un contact par son id
         * @param array $id : un ou plusieurs id d'entrées à supprimer
         * @return int : Le nombre de lignes supprimées
		 */
        public function delete_by_id ($id)
        {
			$query = " 
                DELETE FROM contact
                WHERE id = :id";
     
            $params = ['id' => $id];
            return $this->runQuery($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une contacte
         * @param array $contact : La contacte à insérer avec les champs name, script, admin & admin
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert ($contact)
        {
            $result = $this->insertIntoTable('contact', $contact);

            if (!$result)
            {
                return false;
            }

            return $this->lastId();
        }

        /**
         * Met à jour une contacte par son id
         * @param int $id : L'id de la contact à modifier
         * @param array $contact : Les données à mettre à jour pour la contacte
         * @return int : le nombre de ligne modifiées
         */
        public function update ($id, $contact)
        {
            return $this->updateTableWhere('contact', $contact, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table contact
         * @return int : Le nombre de contact
         */
        public function count ()
        {
            return $this->countTable('contact');
        }
    }
