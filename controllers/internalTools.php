<?php
	class internalTools extends Controller
	{
		/**
		 * Cette fonction parse un numéro pour le retourner sans espaces, etc.
		 * @param string $number : Le numéro de téléphone à parser
		 * @return mixed : Si le numéro est bien un numéro de téléphone, on retourne le numéro parsé. Sinon, on retourne faux
		 */
		public static function parsePhone($number)
		{
			$number = preg_replace('#[^-0-9+]#', '', $number);
			if (preg_match('#^(0|\+[1-9]{1,3}|\+1\-[0-9]{3})[1-9][0-9]{8}$#', $number))
			{
				return $number;
			}
			
			return false;
		}

		/**
		 * Cette fonction parse un numéro pour le retourner avec des espaces, etc.
		 * @param string $number : Le numéro de téléphone à parser
		 * @return mixed : Si le numéro est bien un numéro de téléphone, on retourne le numéro parsé. Sinon, on retourne faux
		 */
		public static function phoneAddSpace($number)
		{
			return preg_replace('#(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})#', '$1 $2 $3 $4 $5', $number);
		}

		/**
		 * Cette fonction fait la correspondance entre un type d'evenement et une icone font awesome.
		 * @param string $type : Le type de l'évenement à analyser
		 * @return string : Le nom de l'icone à afficher (ex : fa-user)
		 */
		public static function eventTypeToIcon($type)
		{
			switch ($type)
			{
				case 'USER_ADD' :
					$logo = 'fa-user';
					break;
				case 'CONTACT_ADD' :
					$logo = 'fa-user';
					break;
				case 'GROUP_ADD' :
					$logo = 'fa-group';
					break;
				case 'SCHEDULED_ADD' :
					$logo = 'fa-calendar';
					break;
				case 'COMMAND_ADD' :
					$logo = 'fa-terminal';
					break;
				default :
					$logo = 'fa-question';
			}

			return $logo;	
		}

		/**
		 * Cette fonction vérifie une date
		 * @param string $date : La date a valider
		 * @param string $format : Le format de la date
		 * @return boolean : Vrai si la date et valide, faux sinon
		 */
		public static function validateDate($date, $format)
		{
			$objectDate = DateTime::createFromFormat($format, $date);
			return ($objectDate && $objectDate->format($format) == $date);
		}

		/**
		 * Cette fonction parse un texte, pour en extraire des données contenu dans des drapeaux au format [FLAG:contenu du drapeau]
		 * @param string $texte : Le texte à parser
		 * @return mixed : Tableau de la forme 'FLAG' => 'contenu du drapeau'. si on trouve une forme correcte (Le contenu de FLAG sera mis en majuscule automatiquement). Sinon le tableau vide.
		 */
		public static function parseForFlag($texte)
		{
			$returns = array();
			$results = array();
			while(preg_match('#\[(.*)(?<!\\\):(.*)(?<!\\\)\]#Uui', $texte, $results)) //Tant qu'on recuèpre un flag
			{
				$returns[mb_strtoupper($results[1])] = $results[2];
				$texte = str_replace($results[0], '', $texte);
			}
			
			return $returns;
		}

		/**
		 * Cette fonction retourne un mot de passe généré aléatoirement
		 * @param int $length : Taille du mot de passe à générer
		 * @return string : Le mot de passe aléatoire
		 */
		public static function generatePassword($length)
		{
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-@()?.:!%*$&/';
			$password = '';
			$chars_length = mb_strlen($chars) - 1;
			$i = 0;
			while ($i < $length)
			{
				$i ++;
				$password .= $chars[rand(0, $chars_length)];
			}
			return $password;	
		}

		/**
		 * Cette fonction vérifie si un utilisateur et connecté, et si il ne l'est pas, redirige sur la page de connexion
		 * @return void
		 */
		public static function verifyConnect()
		{
			if (!isset($_SESSION['connect']) || !$_SESSION['connect'])
			{
				$controller = new Controller();
				header('Location: ' . $controller->generateUrl('connect'));
				die();
			}
		}

		/**
		 * Cette fonction vérifie si un argument csrf a été fourni et est valide
		 * @param string $csrf : argument optionel, qui est la chaine csrf à vérifier. Si non fournie, la fonction cherchera à utiliser $_GET['csrf'] ou $_POST['csrf'].
		 * @return boolean : True si le csrf est valide. False sinon.
		 */
		public static function verifyCSRF($csrf = '')
		{
			if (!$csrf)
			{
				$csrf = isset($_GET['csrf']) ? $_GET['csrf'] : $csrf;
				$csrf = isset($_POST['csrf']) ? $_POST['csrf'] : $csrf;
			}

			if ($csrf == $_SESSION['csrf'])
			{
				return true;
			}
			
			return false;
		}

	}
