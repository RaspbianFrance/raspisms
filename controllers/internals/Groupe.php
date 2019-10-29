<?php
namespace controllers\internals;
	/**
	 * Classe des groupes
	 */
	class Groupe extends \InternalController
	{

		/**
         * Cette fonction retourne une liste des groupes sous forme d'un tableau
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des groupes
         */	
		public function get_list ($nb_entry = false, $page = false)
		{
			//Recupération des groupes
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->get_list($nb_entry, $nb_entry * $page);
		}

		/**
         * Cette fonction retourne une liste des groupes sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des groupes
         */	
		public function get_by_ids ($ids)
		{
			//Recupération des groupes
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->get_by_ids($ids);
        }
        
        /**
         * Cette fonction retourne un groupe par son name
         * @param string $name : Le name du groupe
         * @return array : Le groupe
         */	
        public function get_by_name ($name)
		{
			//Recupération des groupes
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->get_by_name($name);
        }

        /**
         * Cette fonction permet de compter le nombre de groupe
         * @return int : Le nombre d'entrées dans la table
         */
        public function count ()
        {
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->count();
        }

		/**
		 * Cette fonction va supprimer une liste de groupe
		 * @param array $ids : Les id des groupes à supprimer
		 * @return int : Le nombre de groupes supprimées;
		 */
		public function delete ($ids)
        {
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->delete_by_ids($ids);
		}

		/**
         * Cette fonction insert une nouvelle groupe
         * @param array $name : le nom du groupe
         * @param array $contacts_ids : Un tableau des ids des contact du groupe
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle groupe insérée
		 */
        public function create ($name, $contacts_ids)
		{
            $modelGroupe = new \models\Groupe($this->bdd);

            $groupe = [
                'name' => $name,
            ]; 

            $id_groupe = $modelGroupe->insert($groupe);
            if (!$id_groupe)
            {
                return false;
            }

            foreach ($contacts_ids as $contact_id)
            {
                $modelGroupe->insert_groupe_contact($id_groupe, $contact_id);
            }

            $internalEvent = new \controllers\internals\Event($this->bdd);
            $internalEvent->create('GROUP_ADD', 'Ajout groupe : ' . $name);

            return $id_groupe;
		}

		/**
         * Cette fonction met à jour un groupe
         * @param int $id : L'id du groupe à update
         * @param string $name : Le nom du groupe à update
         * @param string $contacts_ids : Les ids des contact du groupe
         * @return bool : True if all update ok, false else
		 */
		public function update ($id, $name, $contacts_ids)
        {
            $modelGroupe = new \models\Groupe($this->bdd);

            $groupe = [
                'name' => $name,
            ];

            $result = $modelGroupe->update($id, $groupe);

            $modelGroupe->delete_groupe_contact($id);

            $nb_contact_insert = 0;
            foreach ($contacts_ids as $contact_id)
            {
                if ($modelGroupe->insert_groupe_contact($id, $contact_id))
                {
                    $nb_contact_insert ++;
                }
            }

            if (!$result && $nb_contact_insert != count($contacts_ids))
            {
                return false;
            }

            return $true;
        }
        
        /**
         * Cette fonction retourne les contact pour un groupe
         * @param string $id : L'id du groupe
         * @return array : Un tableau avec les contact
         */	
        public function get_contact ($id)
		{
			//Recupération des groupes
            $modelGroupe = new \models\Groupe($this->bdd);
            return $modelGroupe->get_contact($id);
        }

	}
