<?php
namespace controllers\internals;
	/**
	 * Classe des commandes
	 */
	class Command extends \InternalController
	{
        private $model_command;

        public function __construct (\PDO $bdd)
        {
            $this->model_command = new \models\Command($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }


		/**
         * Return the list of commands as an array
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des commandes
         */	
		public function list ($nb_entry = false, $page = false)
		{
			//Recupération des commandes
            $model_command = new \models\Command($this->bdd);
            return $model_command->list($nb_entry, $nb_entry * $page);
		}

		/**
         * Cette fonction retourne une liste des commandes sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des commandes
         */	
		public function get_by_ids ($ids)
		{
			//Recupération des commandes
            $modelCommand = new \models\Command($this->bdd);
            return $modelCommand->get_by_ids($ids);
        }

        /**
         * Cette fonction permet de compter le nombre de scheduleds
         * @return int : Le nombre d'entrées dans la table
         */
        public function count ()
        {
            $modelCommand = new \models\Command($this->bdd);
            return $modelCommand->count();
        }

		/**
		 * Cette fonction va supprimer une commande
		 * @param array $id : L'id de la commande à supprimer
		 * @return int : Le nombre de commandes supprimées;
		 */
		public function delete ($id)
        {
            $modelCommand = new \models\Command($this->bdd);
            return $modelCommand->delete_by_id($id);
		}

		/**
         * Cette fonction insert une nouvelle commande
         * @param array $command : La commande à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle commande insérée
		 */
        public function create ($name, $script, $admin)
        {
            $command = [
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            $modelCommand = new \models\Command($this->bdd);
            $result = $modelCommand->insert($command);

            if (!$result)
            {
                return false;
            }

            $internalEvent = new \controllers\internals\Event($this->bdd); 
            $internalEvent->create('COMMAND_ADD', 'Ajout commande : ' . $name . ' => ' . $script);
            return $result;
		}

		/**
         * Cette fonction met à jour un commande
         * @return int : le nombre de ligne modifiées
		 */
		public function update ($id, $name, $script, $admin)
        {
            $modelCommand = new \models\Command($this->bdd);

            $command = [
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            $result = $modelCommand->update($id, $command);
        
            return $result;
        }
	}
