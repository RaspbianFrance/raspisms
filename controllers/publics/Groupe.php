<?php
namespace controllers\publics;
	/**
	 * Page des groupes
	 */
	class Groupe extends \Controller
	{
		/**
		 * Cette fonction est appelée avant toute les autres : 
		 * Elle vérifie que l'utilisateur est bien connecté
		 * @return void;
		 */
		public function _before()
        {
            global $bdd;
            $this->bdd = $bdd;

            $this->internalGroupe = new \controllers\internals\Groupe($this->bdd);
            $this->internalContact = new \controllers\internals\Contact($this->bdd);
            $this->internalEvent = new \controllers\internals\Event($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les groupes, sous forme d'un tableau permettant l'administration de ces groupes
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $groupes = $this->internalGroupe->get_list(25, $page);
    
            foreach ($groupes as $key => $groupe)
            {
                $contacts = $this->internalGroupe->get_contact($groupe['id']);
                $groupes[$key]['nb_contacts'] = count($contacts);
            }

            $this->render('groupe/list', ['groupes' => $groupes]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de groupes
         * @param array int $_GET['ids'] : Les id des groupes à supprimer
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                header('Location: ' . \Router::url('Groupe', 'list'));
                return false;
            }

            $ids = $_GET['ids'] ?? [];
            $this->internalGroupe->delete($ids);

            header('Location: ' . \Router::url('Groupe', 'list'));
            return true;
        }

		/**
		 * Cette fonction retourne la page d'ajout d'un groupe
		 */
		public function add()
		{
			$this->render('groupe/add');
		}

		/**
		 * Cette fonction retourne la page d'édition des groupes
		 * @param int... $ids : Les id des groupes à supprimer
		 */
		public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            $groupes = $this->internalGroupe->get_by_ids
($ids);

            foreach ($groupes as $key => $groupe)
            {
                $groupes[$key]['contacts'] = $this->internalGroupe->get_contact($groupe['id']);
            }

            $this->render('groupe/edit', array(
                'groupes' => $groupes,
            ));
        }

		/**
		 * Cette fonction insert un nouveau groupe
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['name'] : Le nom du groupe
         * @param array $_POST['contacts'] : Les ids des contacts à mettre dans le groupe
		 */
		public function create ($csrf)
		{
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Groupe', 'add'));
            }
			
			$name = $_POST['name'] ?? false;
			$contacts_ids = $_POST['contacts'] ?? false;

			if (!$name || !$contacts_ids)
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Des champs sont manquants !');
                return header('Location: ' . \Router::url('Groupe', 'add'));
			}

			$id_groupe = $this->internalGroupe->create($name, $contacts_ids);
            if (!$id_groupe)
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de créer ce groupe.');
				return header('Location: ' . \Router::url('Groupe', 'add'));
			}

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le groupe a bien été créé.');
			return header('Location: ' . \Router::url('Groupe', 'list'));
		}

		/**
         * Cette fonction met à jour une groupe
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['groupes'] : Un tableau des groupes avec leur nouvelle valeurs & une entrée 'contacts_id' avec les ids des contacts pour chaque groupe
         * @return boolean;
         */
        public function update ($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                header('Location: ' . \Router::url('Groupe', 'list'));
                return false;
            }

            $groupes = $_POST['groupes'] ?? [];

            $nb_groupes_update = 0;
            foreach ($groupes as $id => $groupe)
            {
                $nb_groupes_update += (int) $this->internalGroupe->update($id, $groupe['name'], $groupe['contacts_ids']);
            }

            if ($nb_groupes_update != count($groupes))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Certains groupes n\'ont pas pu êtres mis à jour.');
                return header('Location: ' . \Router::url('Groupe', 'list'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Tous les groupes ont été modifiés avec succès.');
            return header('Location: ' . \Router::url('Groupe', 'list'));
        }

		/**
		 * Cette fonction retourne la liste des groupes sous forme JSON
		 */
		public function json_list()
		{
            header('Content-Type: application/json');
            echo json_encode($this->internalGroupe->get_list());
		}
	}
