<?php
namespace controllers\internals;
	/**
	 * Classe des smsstopes
	 */
	class SMSStop extends \InternalController
	{
		/**
         * Cette fonction retourne une liste des smsstopes sous forme d'un tableau
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des smsstopes
         */	
		public function get_list ($nb_entry = false, $page = false)
		{
			//Recupération des smsstopes
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->get_list($nb_entry, $nb_entry * $page);
		}

		/**
         * Cette fonction retourne une liste des smsstopes sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des smsstopes
         */	
		public function get_by_ids ($ids)
		{
			//Recupération des smsstopes
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->get_by_ids($ids);
        }
        
        /**
         * Cette fonction retourne un smsstop par son numéro de tel
         * @param string $number : Le numéro du smsstop
         * @return array : Le smsstop
         */	
		public function get_by_number ($number)
		{
			//Recupération des smsstopes
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->get_by_number($number);
        }
        

        /**
         * Cette fonction permet de compter le nombre de smsstops
         * @return int : Le nombre d'entrées dans la table
         */
        public function count ()
        {
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->count();
        }

		/**
		 * Cette fonction va supprimer une liste de smsstops
		 * @param array $ids : Les id des smsstopes à supprimer
		 * @return int : Le nombre de smsstopes supprimées;
		 */
		public function delete ($id)
        {
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->delete_by_id($id);
		}

		/**
         * Cette fonction insert une nouvelle smsstope
         * @param array $smsstop : Un tableau représentant la smsstope à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle smsstope insérée
		 */
        public function create ($smsstop)
		{
            $modelSMSStop = new \models\SMSStop($this->bdd);
            return $modelSMSStop->insert($smsstop);
		}

		/**
         * Cette fonction met à jour une série de smsstopes
         * @return int : le nombre de ligne modifiées
		 */
		public function update ($smsstops)
        {
            $modelSMSStop = new \models\SMSStop($this->bdd);
            
            $nb_update = 0;
            foreach ($smsstops as $smsstop)
            {
                $result = $modelSMSStop->update($smsstop['id'], $smsstop);

                if ($result)
                {
                    $nb_update ++;
                }
            }
        
            return $nb_update;
        }
	}
