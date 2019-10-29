<?php
namespace controllers\internals;
	/**
	 * Classe des commandes
	 */
	class Command extends \descartes\InternalController
	{

		public function populate_database ($nb_entry = false, $page = false)
        {
            global $bdd;
            $internalContact = new \controllers\internals\Contact($bdd);
            $internalGroupe = new \controllers\internals\Groupe($bdd);

            #On insert des contacts & regroupe aléatoirement les ids en groupes
            $groupes_contacts_ids = [];
            for ($i = 0; $i < 100; $i ++)
            {
                $contact = [
                    'name' => 'Contact N°' . $i,
                    'number' => '06' . rand(10,99) . rand(10,99) . rand(10,99) . rand(10,99),
                ];

                if (!$id_contact = $internalContact->create($contact))
                {
                    continue;
                }

                $nb_groupe = rand(0,14);

                if (!isset($groupes[$nb_groupe]))
                {
                    $groupes_contacts_ids[$nb_groupe] = [];
                }

                $groupes_contacts_ids[$nb_groupe][] = $id_contact;
            }

            #On insert les groupes
            foreach ($groupes_contacts_ids as $key => $groupe_contacts_ids)
            {
                $internalGroupe->create(['name' => 'Groupe N°' . $key], $groupe_contacts_ids);
            }


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
		 * Cette fonction va supprimer une liste de commands
		 * @param array $ids : Les id des commandes à supprimer
		 * @return int : Le nombre de commandes supprimées;
		 */
		public function delete ($ids)
        {
            $modelCommand = new \models\Command($this->bdd);
            return $modelCommand->delete_by_ids($ids);
		}

		/**
         * Cette fonction insert une nouvelle commande
         * @param array $command : La commande à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle commande insérée
		 */
        public function create ($command)
		{
            $modelCommand = new \models\Command($this->bdd);
            return $modelCommand->insert($command);
		}

		/**
         * Cette fonction met à jour une série de commandes
         * @return int : le nombre de ligne modifiées
		 */
		public function update ($commands)
        {
            $modelCommand = new \models\Command($this->bdd);
            
            $nb_update = 0;
            foreach ($commands as $command)
            {
                $result = $modelCommand->update($command['id'], $command);

                if ($result)
                {
                    $nb_update ++;
                }
            }
        
            return $nb_update;
        }
	}
