<?php
	/**
	 * Page des contacts
	 */
	class contacts extends Controller
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
		 * Cette fonction retourne tous les contacts, sous forme d'un tableau permettant l'administration de ces contacts
		 */
		public function byDefault()
		{
			//Creation de l'object de base de données
			global $db;

			//Recupération des nombres des 4 panneaux d'accueil
			if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS)
			{
				$contacts = $db->getFromTableWhere('contacts', array(), '', false, false, false, array(array(
					'table' => 'contacts_infos',
					'type' => 'LEFT',
					'on' => 'contacts_infos.id_contact = contacts.id'
				)));
			} else {
				$contacts = $db->getFromTableWhere('contacts');
			}

			$this->render('contacts/default', array(
				'contacts' => $contacts,
			));
		}

		/**
		 * Cette fonction supprimer une liste de contacts
		 * @param $csrf : Le jeton CSRF
		 * @param int... $ids : Les id des commandes à supprimer
		 * @return Boolean;
		 */
		public function delete($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('contacts'));
				return false;
			}

			//On récupère les ids comme étant tous les arguments de la fonction et on supprime le premier (csrf)
			$ids = func_get_args();
			unset($ids[0]);

			//Create de l'object de base de données
			global $db;

			$db->deleteContactsIn($ids);
			header('Location: ' . $this->generateUrl('contacts'));
			return true;
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un contact
		 */
		public function add()
		{
			$this->render('contacts/add');
		}

		/**
		 * Cette fonction retourne la page d'édition des contacts
		 * @param int... $ids : Les id des commandes à supprimer
		 */
		public function edit()
		{
			global $db;

			//On récupère les ids comme étant tous les arguments de la fonction
			$ids = func_get_args();

			$contacts = $db->getContactsIn($ids);
			$this->render('contacts/edit', array(
				'contacts' => $contacts,
			));
		}

		/**
		 * Cette fonction insert un nouveau contact
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['name'] : Le nom du contact
		 * @param string $_POST['phone'] : Le numero de téléphone du contact
		 */
		public function create($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('contacts'));
				return false;
			}

			global $db;

			if (empty($_POST['name']) || empty($_POST['phone']) || (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS && empty($_POST['civility'])))
			{
				$_SESSION['errormessage'] = 'Des champs sont manquants.';
				header('Location: ' . $this->generateUrl('contacts', 'add'));
				return false;
			}

			$nom = $_POST['name'];
			$phone = $_POST['phone'];
			// on enregistre les infos si elles ont été saisies par l'utilisateur
			$civility = isset($_POST['civility']) ? $_POST['civility'] : null;
			$prenom = isset($_POST['firstName']) ? $_POST['firstName'] : null;
			$birthday = isset($_POST['birthday']) ? $_POST['birthday'] : null;
			$loveSituation = isset($_POST['loveSituation']) ? $_POST['loveSituation'] : null;
			$nomComplet = $prenom ? $prenom.' '.$nom : $nom;

			if (!$phone = internalTools::parsePhone($phone))
			{
				$_SESSION['errormessage'] = 'Numéro de téléphone incorrect.';
				header('Location: ' . $this->generateUrl('contacts', 'add'));
				return false;
			}

			if (!$db->insertIntoTable('contacts', ['name' => $nomComplet, 'number' => $phone]))
			{
				$_SESSION['errormessage'] = 'Impossible créer ce contact.';
				header('Location: ' . $this->generateUrl('contacts', 'add'));
				return false;
			}

			$db->insertIntoTable('events', ['type' => 'CONTACT_ADD', 'text' => 'Ajout contact : ' . $nomComplet . ' (' . internalTools::phoneAddSpace($phone) . ')']);

			if (!RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS)
			{
				$_SESSION['successmessage'] = 'Le contact a bien été créé.';
				header('Location: ' . $this->generateUrl('contacts'));
				return true;
			}

			$id_contact = $db->lastId();

			if (!$db->insertIntoTable('contacts_infos', ['id_contact' => $id_contact, 'civility' => $civility, 'first_name' => $prenom, 'last_name' => $nom, 'birthday' => $birthday, 'love_situation' => $loveSituation]))
			{
				$_SESSION['errormessage'] = "Le contact a bien été créé, mais certaines informations n'ont pas pu être enregistrées.";
				header('Location: ' . $this->generateUrl('contacts', 'add'));
				return false;
			}

			$db->insertIntoTable('events', ['type' => 'CONTACT_EXTENTED_ADD', 'text' => 'Ajout infos pour le contact : ' . $nomComplet . ' (' . internalTools::phoneAddSpace($phone) . ')']);

			$_SESSION['successmessage'] = 'Le contact a bien été créé.';
			header('Location: ' . $this->generateUrl('contacts'));
			return true;
		}

		/**
		 * Cette fonction met à jour une liste de contacts
		 * @param $csrf : Le jeton CSRF
		 * @param array $_POST['contacts'] : Un tableau des contacts avec leur nouvelle valeurs
		 * @return boolean;
		 */
		public function update($csrf)
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF($csrf))
			{
				$_SESSION['errormessage'] = 'Jeton CSRF invalide !';
				header('Location: ' . $this->generateUrl('contacts'));
			}

			global $db;

			$errors = array(); //On initialise le tableau qui contiendra les erreurs rencontrés
			//Pour chaque contact reçu, on boucle en récupérant son id (la clef), et le contact lui-même (la value)

			foreach ($_POST['contacts'] as $id => $contact)
			{
				if (!isset($contact['name']) || !isset($contact['phone']) || (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS && !isset($contact['civility'])))
				{
					$errors[] = $id;
					continue;
				}

				$nom = $contact['name'];
				$phone = $contact['phone'];
				// on enregistre les infos si elles ont été saisies par l'utilisateur
				$civility = isset($contact['civility']) ? $contact['civility'] : null;
				$prenom = isset($contact['first_name']) ? $contact['first_name'] : null;
				$birthday = isset($contact['birthday']) ? $contact['birthday'] : null;
				$loveSituation = isset($contact['love_situation']) ? $contact['love_situation'] : null;
				$nomComplet = $prenom ? $prenom.' '.$nom : $nom;

				if (!$number = internalTools::parsePhone($contact['phone']))
				{
					$errors[] = $id;
					continue;
				}

				$db->updateTableWhere('contacts', ['name' => $nomComplet, 'number' => $number], ['id' => $id]);

				if (!isset($contact['contacts_infos_id'])) {
					if (!$db->insertIntoTable('contacts_infos', ['id_contact' => $id, 'civility' => $civility, 'first_name' => $prenom, 'last_name' => $nom, 'birthday' => $birthday, 'love_situation' => $loveSituation]))
					{
						$errors[] = $id;
						continue;
					}
				}

				if (!$db->updateTableWhere('contacts_infos', ['id_contact' => $id, 'civility' => $civility, 'first_name' => $prenom, 'last_name' => $nom, 'birthday' => $birthday, 'love_situation' => $loveSituation], ['id' => $contact['contacts_infos_id']]))
				{
					$errors[] = $id;
					continue;
				}
			}

			//Si on a eu des erreurs
			if (count($errors))
			{
				$_SESSION['errormessage'] = 'Certains contacts n\'ont pas pu êtres mis à jour. Voici leurs identifiants : ' . implode(', ', $errors);
				return header('Location: ' . $this->generateUrl('contacts'));
			}

			$_SESSION['successmessage'] = 'Tous les contacts ont été modifiés avec succès.';
			return header('Location: ' . $this->generateUrl('contacts'));
		}

		/**
		 * Cette fonction retourne la liste des contacts sous forme JSON
		 */
		public function jsonGetContacts()
		{
			global $db;

			if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS)
			{
				$contacts = $db->getFromTableWhere('contacts', array(), '', false, false, false, array(array(
					'table' => 'contacts_infos',
					'type' => 'LEFT',
					'on' => 'contacts_infos.id_contact = contacts.id'
				)));
			} else {
				$contacts = $db->getFromTableWhere('contacts');
			}

			echo json_encode($contacts);
		}
	}
