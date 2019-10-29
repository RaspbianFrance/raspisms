<?php
    namespace controllers\internals;

    /**
     * Classe des Event
	 */
	class Event extends \InternalController
	{

		/**
         * Cette fonction retourne une liste des events sous forme d'un tableau
         * @param PDO $bdd :  instance PDO de la base de donnée
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des events
         */	
		public function get_list ($nb_entry = false, $page = false)
		{
			//Recupération des events
            $modelEvent = new \models\Event($this->bdd);
            return $modelEvent->get_list($nb_entry, $nb_entry * $page);
		}

        /**
         * Cette fonction retourne les X dernières entrées triées par date
         * @param mixed false|int $nb_entry : Nombre d'entrée à retourner ou faux pour tout
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date ($nb_entry = false)
        {
            $modelEvent = new \models\Event($this->bdd);
            return $modelEvent->get_lasts_by_date($nb_entry);
        }

		/**
		 * Cette fonction va supprimer une liste de contacts
		 * @param array $ids : Les id des contactes à supprimer
		 * @return int : Le nombre de contactes supprimées;
		 */
		public function delete ($id)
        {
            $modelEvent = new \models\Event($this->bdd);
            return $modelEvent->delete_by_id($id);
		}

		/**
         * Cette fonction insert un nouvel event
         * @param array $event : Un tableau représentant l'event à insérer
         * @return mixed bool|int : false si echec, sinon l'id du nouvel event inséré
		 */
        public function create ($type, $text)
        {
            $modelEvent = new \models\Event($this->bdd);

            $event = [
                'type' => $type,
                'text' => $text,
            ];

            return $modelEvent->insert($event);
		}

	}
