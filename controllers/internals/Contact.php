<?php
namespace controllers\internals;
	/**
	 * Classe des contactes
	 */
	class Contact extends \InternalController
	{

		/**
         * Cette fonction retourne une liste des contactes sous forme d'un tableau
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des contactes
         */	
		public function get_list ($nb_entry = false, $page = false)
		{
			//Recupération des contactes
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->get_list($nb_entry, $nb_entry * $page);
		}

		/**
         * Cette fonction retourne une liste des contactes sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des contactes
         */	
		public function get_by_ids ($ids)
		{
			//Recupération des contactes
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->get_by_ids($ids);
        }
        
        /**
         * Cette fonction retourne un contact par son numéro de tel
         * @param string $number : Le numéro du contact
         * @return array : Le contact
         */	
		public function get_by_number ($number)
		{
			//Recupération des contactes
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->get_by_number($number);
        }
        
        /**
         * Cette fonction retourne un contact par son name
         * @param string $name : Le name du contact
         * @return array : Le contact
         */	
        public function get_by_name ($name)
		{
			//Recupération des contactes
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->get_by_name($name);
        }
        

        /**
         * Cette fonction permet de compter le nombre de contacts
         * @return int : Le nombre d'entrées dans la table
         */
        public function count ()
        {
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->count();
        }

		/**
		 * Cette fonction va supprimer un contact
		 * @param array $id : L'id du contact à supprimer
		 * @return int : Le nombre de contact supprimées;
		 */
		public function delete ($id)
        {
            $modelContact = new \models\Contact($this->bdd);
            return $modelContact->delete_by_id($id);
		}

		/**
         * Cette fonction insert une nouvelle contacte
         * @param array $contact : Un tableau représentant la contacte à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle contacte insérée
		 */
        public function create ($number, $name)
        {
            $contact = [
                'number' => $number,
                'name' => $name,
            ];

            $modelContact = new \models\Contact($this->bdd);
            
            $result = $modelContact->insert($contact);
            if (!$result)
            {
                return $result;
            }

            $internalEvent = new \controllers\internals\Event($this->bdd);
            $internalEvent->create('CONTACT_ADD', 'Ajout contact : ' . $name . ' (' . \controllers\internals\Tool::phone_add_space($number) . ')');

            return $result;
		}

		/**
         * Cette fonction met à jour une série de contactes
         * @return int : le nombre de ligne modifiées
		 */
		public function update ($id, $number, $name)
        {
            $modelContact = new \models\Contact($this->bdd);

            $contact = [
                'number' => $number,
                'name' => $name,
            ];
        
            return $modelContact->update($id, $contact);
        }
	}
