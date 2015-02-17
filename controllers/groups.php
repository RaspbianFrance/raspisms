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
		 * @return void;
		 */
		public function delete()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('groups', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			//Create de l'object de base de données
			global $db;
			
			$groups_ids = $_GET;
			$db->deleteGroupsIn($groups_ids);
			header('Location: ' . $this->generateUrl('groups'));		
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
		 */
		public function edit()
		{
			global $db;
			
			$groups = $db->getGroupsIn($_GET);
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
		 */
		public function create()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('groups', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			

			$nom = $_POST['name'];
			if ($db->createGroup($nom))
			{
				$id_group = $db->lastId();
				$db->createEvent('GROUP_ADD', 'Ajout du groupe : ' . $nom);
				foreach ($_POST['contacts'] as $id_contact)
				{
					$db->createGroups_contacts($id_group, $id_contact);
				}

				header('Location: ' . $this->generateUrl('groups', 'showAll', array(
					'successmessage' => 'Le groupe a bien été créé.'
				)));
		
				return true;
			}

			header('Location: ' . $this->generateUrl('groups', 'showAll', array(
				'errormessage' => 'Impossible de créer ce groupe.'
			)));
		}

		/**
		 * Cette fonction met à jour une liste de groupes
		 */
		public function update()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('groups', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
				return true;
			}

			global $db;
			

			//Pour chaque groupe reçu, on boucle en récupérant son id (la clef), et le contact le tableau du groupe (nom et liste des contacts)
			foreach ($_POST['groups'] as $id_group => $group)
			{
				$db->updateGroup($id_group, $group['name']); //On met à jour le nom du groupe
				$db->deleteGroups_contactsForGroup($id_group); //On supprime tous les contacts de ce groupe
				foreach ($group['contacts'] as $id_contact) //Pour chaque contact on l'ajoute au groupe
				{
					$db->createGroups_contacts($id_group, $id_contact);
				}
			}

			$message = 'Tous les groupes ont été modifiés avec succès.';
			header('Location: ' . $this->generateUrl('groups', 'showAll', array(
				'successmessage' => $message,
			)));
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
