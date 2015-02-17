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
		 * Cette fonction est alias de showAll()
		 */	
		public function byDefault()
		{
			$this->showAll();
		}
		
		/**
		 * Cette fonction retourne tous les contacts, sous forme d'un tableau permettant l'administration de ces contacts
		 * @return void;
		 */
		public function showAll()
		{
			//Creation de l'object de base de données
			global $db;
			
			//Recupération des nombres des 4 panneaux d'accueil
			$contacts = $db->getAll('contacts');

			$this->render('contacts', array(
				'contacts' => $contacts,
			));
			
		}

		/**
		 * Cette fonction supprimer une liste de contacts
		 * @return void;
		 */
		public function delete()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
			}

			//Create de l'object de base de données
			global $db;
			
			$contacts_ids = $_GET;
			$db->deleteContactsIn($contacts_ids);
			header('Location: ' . $this->generateUrl('contacts'));		
		}

		/**
		 * Cette fonction retourne la page d'ajout d'un contact
		 */
		public function add()
		{
			$this->render('addContact');
		}

		/**
		 * Cette fonction retourne la page d'édition des contacts
		 */
		public function edit()
		{
			global $db;
			

			$contacts = $db->getContactsIn($_GET);
			$this->render('editContacts', array(
				'contacts' => $contacts,
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
				header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
			}

			global $db;
			

			$nom = $_POST['name'];
			$phone = $_POST['phone'];
			if ($phone = internalTools::parsePhone($phone))
			{
				if ($db->createContact($nom, $phone))
				{
					$db->createEvent('CONTACT_ADD', 'Ajout contact : ' . $nom . ' (' . internalTools::phoneAddSpace($phone) . ')');
					header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
						'successmessage' => 'Le contact a bien été créé.'
					)));

					return true;
				}

				header('Location: ' . $this->generateUrl('contacts', 'add', array(
					'errormessage' => 'Impossible créer ce contact.'
				)));
				return true;
			}

			header('Location: ' . $this->generateUrl('contacts', 'add', array(
				'errormessage' => 'Numéro de téléphone incorrect.'
			)));
		}

		/**
		 * Cette fonction met à jour une liste de contacts
		 */
		public function update()
		{
			//On vérifie que le jeton csrf est bon
			if (!internalTools::verifyCSRF())
			{
				header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
					'errormessage' => 'Jeton CSRF invalide !'
				)));
			}

			global $db;
			

			$errors = array(); //On initialise le tableau qui contiendra les erreurs rencontrés
			//Pour chaque contact reçu, on boucle en récupérant son id (la clef), et le contact lui-même (la value)

			foreach ($_POST['contacts'] as $id => $contact)
			{
				if ($number = internalTools::parsePhone($contact['phone']))
				{
					$db->updateContact($id, $contact['name'], $number);
				}
				else
				{
					$errors[] = $contact['id'];
				}
			}

			//Si on a eu des erreurs
			if (count($errors))
			{
				$message = 'Certains contacts n\'ont pas pu êtres mis à jour. Voici leurs identifiants : ' . implode(', ', $errors);
				header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
					'errormessage' => $message,
				)));
			}
			else
			{
				$message = 'Tous les contacts ont été modifiés avec succès.';
				header('Location: ' . $this->generateUrl('contacts', 'showAll', array(
					'successmessage' => $message,
				)));
			}
		}

		/**
		 * Cette fonction retourne la liste des contacts sous forme JSON
		 */
		public function jsonGetContacts()
		{
			global $db;
			
			echo json_encode($db->getAll('contacts'));
		}
	}
