<?php
	/**
	 * Page des groups
	 */
	class groups extends Controller
	{
		/**
		 * Cette fonction est appelée avant toute les autres : 
		 * Elle vérifie que l'utilisateur est bien connecté
		 * @return void;
		 */
		public function before()
		{
			internalTools::verifyConnect();
		}

		/**
		 * Cette fonction est alias de showAll()
		 */	
		public function byDefault()
		{
			$this->showAll();
		}
		
		/**
		 * Cette fonction retourne tous les groupes, sous forme d'un tableau permettant l'administration de ces groupes
		 * @return void;
		 */
		public function showAll()
		{
			//Creation de l'object de base de données
			global $db;

			$groups = $db->getGroupsWithContactsNb();
			$this->render('groups', array(
				'groups' => $groups,
			));
		}

		/**
		 * Cette fonction supprime une liste de groupes
		 * @param int... $ids : Les id des groups à supprimer
		 * @return void;
		 */
		public function delete(...$ids)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				$_SESSION['errormessage'] => 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('groups', 'showAll'));
				return false;
			}

			//Create de l'object de base de données
			global $db;
			
			$db->deleteGroupsIn($ids);
			header('Location: ' . $this->generateUrl('groups'));
			return true;
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un group
		 */
		public function add()
		{
			$this->render('addGroup');
		}

		/**
		 * Cette fonction retourne la page d'édition des groupes
		 * @param int... $ids : Les id des groups à modifier
		 */
		public function edit(...$ids)
		{
			global $db;
			
			$groups = $db->getGroupsIn($ids);
			$blocks = array(); //On défini la variable qui correspondra à un bloc groupe et contacts

			//Pour chaque groupe, on récupère les contacts liés
			foreach ($groups as $key => $group)
			{
				$groups[$key]['contacts'] = $db->getContactsForGroup($group['id']);	
			}

			$this->render('editGroups', array(
				'groups' => $groups,
			));
		}

		/**
		 * Cette fonction insert un nouveau contact
		 * @param string $_POST['name'] : Le nom du groupe
		 * @param array $_POST['contacts'] : Les id des contacts à mettre dans le du groupe
		 */
		public function create()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('groups', 'showAll'));
				return false;
			}

			global $db;
			
			$nom = $_POST['name'];
			if (!$db->insertIntoTable('groups' ['name' => $nom]))
			{
				$_SESSION['errormessage'] = 'Impossible de créer ce groupe.';
				header('Location: ' . $this->generateUrl('groups', 'showAll'));
				return false;
			}

			$id_group = $db->lastId();
			$db->insertIntoTable('events', ['type' => 'GROUP_ADD', 'text' => 'Ajout du groupe : ' . $nom]);

			foreach ($_POST['contacts'] as $id_contact)
			{
				$db->insertIntoTable('groups_contacts', ['id_group' => $id_group, 'id_contact' => $id_contact]);
			}

			$_SESSION['successmessage'] = 'Le groupe a bien été créé.';
			header('Location: ' . $this->generateUrl('groups', 'showAll'));
			return true;
		}

		/**
		 * Cette fonction met à jour une liste de groupes
		 * @param array $_POST['groups'] : Un tableau des groups avec leur nouvelle valeurs
		 * @return boolean;
		 */
		public function update()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('groups', 'showAll'));
				return false;
			}

			global $db;

			//Pour chaque groupe reçu, on boucle en récupérant son id (la clef), et le contact le tableau du groupe (nom et liste des contacts)
			foreach ($_POST['groups'] as $id_group => $group)
			{
				$db->updateTableWhere('groups', $group, ['id' => $id_group]); //On met à jour le nom du groupe
				$db->deleteFromTableWhere('groups_contacts', ['id_group' => $id_group]); //On supprime tous les contacts de ce groupe
				foreach ($group['contacts'] as $id_contact) //Pour chaque contact on l'ajoute au groupe
				{
					$db->insertIntoTable('groups_contacts', ['id_group' => $id_group, 'id_contact' => $id_contact]);
				}
			}

			$_SESSION['successmessage'] = 'Tous les groupes ont été modifiés avec succès.';
			header('Location: ' . $this->generateUrl('groups', 'showAll'));
		}

		/**
		 * Cette fonction retourne la liste des groupes sous forme JSON
		 */
		public function jsonGetGroups()
		{
			global $db;
			
			echo json_encode($db->getAll('groups'));
		}
	}
