<?php
	/**
	 * Page de l'API pour créer un SMS
	 */
	class smsAPI extends Controller
	{
		//On défini les constantes qui servent pour les retours d'API
		const API_ERROR_NO = 0;
		const API_ERROR_BAD_ID = 1;
		const API_ERROR_CREATION_FAILED = 2;
		const API_ERROR_MISSING_FIELD = 3;
		

		/**
		 * Cette fonction permet d'envoyer un SMS, en passant simplement des arguments à l'URL (ou pas $_GET)
		 * @param string text = Le contenu du SMS
		 * @param mixed numbers = Les numéros auxquels envoyer les SMS. Soit un seul numéro, et il s'agit d'un string. Soit plusieurs numéros, et il s'agit d'un tableau
		 * @param mixed contacts = Les noms des contacts auxquels envoyer les SMS. Soit un seul et il s'agit d'un string. Soit plusieurs, et il s'agit d'un tableau
		 * @param mixed groups = Les noms des groupes auxquels envoyer les SMS. Soit un seul et il s'agit d'un string. Soit plusieurs, et il s'agit d'un tableau
		 * @param optionnal string date = La date à laquelle doit être envoyé le SMS. Au format 'Y-m-d H:i'. Si non fourni, le SMS sera envoyé dans 2 minutes
		 */
		public function byDefault()
		{
			global $db;

			//On récupère l'email et le password
			$email = isset($_GET['email']) ? $_GET['email'] : NULL;
			$email = isset($_POST['email']) ? $_POST['email'] : $email;
			$password = isset($_GET['password']) ? $_GET['password'] : NULL;
			$password = isset($_POST['password']) ? $_POST['password'] : $password;

			//Si les identifiants sont incorrect on retourne une erreur
			$user = $db->getUserFromEmail($email);

			if (!$user || sha1($password) != $user['password'])
			{
				echo json_encode(array(
					'error' => self::API_ERROR_BAD_ID,
				));
				return true;
			}

			//On map les variables $_GET
			$get_numbers = isset($_GET['numbers']) ? $_GET['numbers'] : array();
			$get_contacts = isset($_GET['contacts']) ? $_GET['contacts'] : array();
			$get_groups = isset($_GET['groups']) ? $_GET['groups'] : array();
			
			//On map les variables POST
			$post_numbers = isset($_POST['numbers']) ? $_POST['numbers'] : array();
			$post_contacts = isset($_POST['contacts']) ? $_POST['contacts'] : array();
			$post_groups = isset($_POST['groups']) ? $_POST['groups'] : array();

			//On map le texte et la date à part car c'est les seuls arguments qui ne sera jamais un tableau
			$text = isset($_GET['text']) ? $_GET['text'] : NULL;
			$text = isset($_POST['text']) ? $_POST['text'] : $text;
			$date = isset($_GET['date']) ? $_GET['date'] : NULL;
			$date = isset($_POST['date']) ? $_POST['date'] : $date;

			//On passe tous les paramètres GET en tableau
			$get_numbers = is_array($get_numbers) ? $get_numbers : ($get_numbers ? array($get_numbers) : array());
			$get_contacts = is_array($get_contacts) ? $get_contacts : array($get_contacts);
			$get_groups = is_array($get_groups) ? $get_groups : array($get_groups);

			//On passe tous les paramètres POST en tableau
			$post_numbers = is_array($post_numbers) ? $post_numbers : array($post_numbers);
			$post_contacts = is_array($post_contacts) ? $post_contacts : array($post_contacts);
			$post_groups = is_array($post_groups) ? $post_groups : array($post_groups);

			//On merge les données reçus en GET, et celles en POST
			$numbers = array_merge($get_numbers, $post_numbers);
			$contacts = array_merge($get_contacts, $post_contacts);
			$groups = array_merge($get_groups, $post_groups);

			//Pour chaque contact, on récupère l'id du contact
			foreach ($contacts as $key => $name)
			{
				if (!$contact = $db->getFromTableWhere('contacts', ['name' => $name]))
				{
					unset($contacts[$key]);
					continue;
				}

				$contacts[$key] = $contact['id'];
			}

			//Pour chaque groupe, on récupère l'id du groupe
			foreach ($groups as $key => $name)
			{
				if ($group = $db->getFromTableWhere('groups', ['name' => $name]))
				{
					unset($groups[$key]);
					continue;
				}

				$groups[$key] = $group['id'];
			}

			//Si la date n'est pas définie, on la met à la date du jour
			if (!$date)
			{
				$now = new DateTime();
				$date = $now->format('Y-m-d H:i');
			}

			//Si il manque des champs essentiels, on leve une erreur
			if (!$text || (!$numbers && !$contacts && !$groups))
			{
				echo json_encode(array(
					'error' => self::API_ERROR_MISSING_FIELD,
				));
				return false;
			}

			//On assigne les variable POST (après avoir vidé $_POST) en prévision de la création du SMS
			$_POST = array();
			$_POST['content'] = $text;
			$_POST['date'] = $date;
			$_POST['numbers'] = $numbers;
			$_POST['contacts'] = $contacts;
			$_POST['groups'] = $groups;
		
			$scheduleds = new scheduleds();
			$success = $scheduleds->create('', true);
		
			if (!$success)
			{
				echo json_encode(array(
					'error' => self::API_ERROR_CREATION_FAILED,
				));

				return false;
			}
			
			echo json_encode(array(
				'error' => self::API_ERROR_NO,
			));
			return true;
		}
	}	
