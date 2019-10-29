<?php
namespace controllers\internals;

    /**
     * Classe des groups
     */
    class Group extends \descartes\InternalController
    {
        private $model_group;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_group = new \models\Group($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * Cette fonction retourne une liste des groups sous forme d'un tableau
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page : Le numéro de page en cours
         * @return array : La liste des groups
         */
        public function get_list($nb_entry = false, $page = false)
        {
            //Recupération des groups
            return $this->model_group->get_list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des groups sous forme d'un tableau
         * @param array int $ids : Les ids des entrées à retourner
         * @return array : La liste des groups
         */
        public function get_by_ids($ids)
        {
            //Recupération des groups
            return $this->model_group->get_by_ids($ids);
        }
        
        /**
         * Cette fonction retourne un group par son name
         * @param string $name : Le name du group
         * @return array : Le group
         */
        public function get_by_name($name)
        {
            //Recupération des groups
            return $this->model_group->get_by_name($name);
        }

        /**
         * Cette fonction permet de compter le nombre de group
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_group->count();
        }

        /**
         * Cette fonction va supprimer une liste de group
         * @param array $ids : Les id des groups à supprimer
         * @return int : Le nombre de groups supprimées;
         */
        public function delete($ids)
        {
            return $this->model_group->delete_by_ids($ids);
        }

        /**
         * Cette fonction insert une nouvelle group
         * @param array $name : le nom du group
         * @param array $contacts_ids : Un tableau des ids des contact du group
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle group insérée
         */
        public function create($name, $contacts_ids)
        {
            $group = [
                'name' => $name,
            ];

            $id_group = $this->model_group->insert($group);
            if (!$id_group) {
                return false;
            }

            foreach ($contacts_ids as $contact_id) {
                $this->model_group->insert_group_contact($id_group, $contact_id);
            }

            $this->internal_event->create('GROUP_ADD', 'Ajout group : ' . $name);

            return $id_group;
        }

        /**
         * Cette fonction met à jour un group
         * @param int $id : L'id du group à update
         * @param string $name : Le nom du group à update
         * @param string $contacts_ids : Les ids des contact du group
         * @return bool : True if all update ok, false else
         */
        public function update($id, $name, $contacts_ids)
        {
            $group = [
                'name' => $name,
            ];

            $result = $this->model_group->update($id, $group);

            $this->model_group->delete_group_contact($id);

            $nb_contact_insert = 0;
            foreach ($contacts_ids as $contact_id) {
                if ($this->model_group->insert_group_contact($id, $contact_id)) {
                    $nb_contact_insert ++;
                }
            }

            if (!$result && $nb_contact_insert != count($contacts_ids)) {
                return false;
            }

            return true;
        }
        
        /**
         * Cette fonction retourne les contact pour un group
         * @param string $id : L'id du group
         * @return array : Un tableau avec les contact
         */
        public function get_contact($id)
        {
            //Recupération des groups
            return $this->model_group->get_contact($id);
        }
    }
