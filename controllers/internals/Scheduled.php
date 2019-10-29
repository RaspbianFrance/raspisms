<?php
namespace controllers\internals;
	/**
	 * Classe des scheduledes
	 */
	class Scheduled extends \InternalController
	{
        
		/**
         * Cette fonction retourne une liste des scheduledes sous forme d'un tableau
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des scheduledes
         */	
		public function get_list ($nb_entry = false, $page = false)
		{
			//Recupération des scheduledes
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_list($nb_entry, $nb_entry * $page);
		}

		/**
         * Cette fonction retourne une liste des scheduledes sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des scheduledes
         */	
		public function get_by_ids ($ids)
		{
			//Recupération des scheduledes
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_by_ids($ids);
        }

        /**
         * Cette fonction retourne les messages programmés avant une date et pour un numéro
         * @param \DateTime $date : La date avant laquelle on veux le message
         * @param string $number : Le numéro
         * @return array : Les messages programmés avant la date
         */
        public function get_before_date_for_number ($date, $number)
        {
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_before_date_for_number($date, $number);
        }

        /**
         * Cette fonction permet de compter le nombre de scheduled
         * @return int : Le nombre d'entrées dans la table
         */
        public function count ()
        {
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->count();
        }

		/**
		 * Cette fonction va supprimer un scheduled
		 * @param int $id : L'id du scheduled à supprimer
		 * @return int : Le nombre de scheduledes supprimées;
		 */
		public function delete ($id)
        {
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->delete_by_id($id);
		}

		/**
         * Cette fonction insert un nouveau scheduled
         * @param array $scheduled : Le scheduled à créer avec at, content, flash, progress
         * @param array $numbers : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groupes_ids : Les ids des groupe auxquels envoyer le scheduled
         * @return mixed bool|int : false si echec, sinon l'id du nouveau scheduled inséré
		 */
        public function create ($scheduled, $numbers = [], $contacts_ids = [], $groupes_ids = [])
		{
            $modelScheduled = new \models\Scheduled($this->bdd);
            
            if (!$id_scheduled = $modelScheduled->insert($scheduled))
            {
                $internalEvent = new \controllers\internals\Event($this->bdd);
                $internalEvent->create('SCHEDULED_ADD', 'Ajout d\'un SMS pour le ' . $date . '.');

                return false;
            }

            foreach ($numbers as $number)
            {
                $modelScheduled->insert_scheduled_number($id_scheduled, $number);
            }

            foreach ($contacts_ids as $contact_id)
            {
                $modelScheduled->insert_scheduled_contact($id_scheduled, $contact_id);
            }
            
            foreach ($groupes_ids as $groupe_id)
            {
                $modelScheduled->insert_scheduled_groupe($id_scheduled, $groupe_id);
            }

            return $id_scheduled;
		}

		/**
         * Cette fonction met à jour une série de scheduledes
         * @param array $scheduleds : Un tableau de scheduled à modifier avec at, content, flash, progress + pour chaque scheduled number, contact_ids, groupe_ids
         * @param array $numbers : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groupes_ids : Les ids des groupe auxquels envoyer le scheduled
         * @return int : le nombre de ligne modifiées
		 */
        public function update ($scheduleds)
        {
            $modelScheduled = new \models\Scheduled($this->bdd);
            
            $nb_update = 0;
            foreach ($scheduleds as $scheduled)
            {
                $result = $modelScheduled->update($scheduled['scheduled']['id'], $scheduled['scheduled']);

                $modelScheduled->delete_scheduled_number($scheduled['scheduled']['id']);
                $modelScheduled->delete_scheduled_contact($scheduled['scheduled']['id']);
                $modelScheduled->delete_scheduled_groupe($scheduled['scheduled']['id']);

                foreach ($scheduled['number'] as $number)
                {
                    $modelScheduled->insert_scheduled_number($scheduled['scheduled']['id'], $number);
                }

                foreach ($scheduled['contact_ids'] as $contact_id)
                {
                    $modelScheduled->insert_scheduled_contact($scheduled['scheduled']['id'], $contact_id);
                }
                
                foreach ($scheduled['groupe_ids'] as $groupe_id)
                {
                    $modelScheduled->insert_scheduled_groupe($scheduled['scheduled']['id'], $groupe_id);
                }
                

                $nb_update ++;
            }
        
            return $nb_update;
        }
        
        /**
         * Cette fonction retourne une liste de numéro pour un scheduled
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         * @return array : La liste des scheduledes
         */	
        public function get_number ($id_scheduled)
		{
			//Recupération des scheduledes
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_number($id_scheduled);
		}

        /**
         * Cette fonction retourne une liste de contact pour un scheduled
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         * @return array : La liste des contact
         */	
        public function get_contact ($id_scheduled)
		{
			//Recupération des scheduledes
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_contact($id_scheduled);
		}
        
        /**
         * Cette fonction retourne une liste de groupe pour un scheduled
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         * @return array : La liste des groupe
         */	
        public function get_groupe ($id_scheduled)
		{
			//Recupération des scheduledes
            $modelScheduled = new \models\Scheduled($this->bdd);
            return $modelScheduled->get_groupe($id_scheduled);
		}
	}
