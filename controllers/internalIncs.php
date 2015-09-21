<?php
	class internalIncs extends Controller
	{
		/**
		 * Cette fonction retourne le template du head html
		 * @param string $title : Optionnel. Il s'agit du titre à utiliser. Si non fourni, on utilisera simplement 'RaspiSMS'
		 * @return void
		 */
		public function head($title = '')
		{
			$title = (!empty($title)) ? $title . ' - ' . WEBSITE_TITLE : WEBSITE_TITLE;	
			$author = WEBSITE_AUTHOR;

			$error_message = false;
			$success_message = false;
			if (isset($_SESSION['errormessage']))
			{
				$error_message = $_SESSION['errormessage'];
				unset($_SESSION['errormessage']);
			}

			if (isset($_SESSION['successmessage']))
			{
				$success_message = $_SESSION['successmessage'];
				unset($_SESSION['successmessage']);
			}

			$this->render('internalIncs/head', array(
				'title' => $title,
				'author' => $author,
				'error_message' => $error_message,
				'success_message' => $success_message,
			));
		}
		
		/**
		 * Cette fonction retourne le template du menu
		 * @param string $page : Optionnel. Le nom de la page courante. Utilisé pour mettre en surbrillance la partie adaptée du menu
		 * @return void
		 */
		public function nav($page = '')
		{
			$email = isset($_SESSION['email']) ? $_SESSION['email'] : 'Mon compte';
			$this->render('internalIncs/nav', array(
				'email' => $email,
				'admin' => $_SESSION['admin'],
				'page' => $page,
			));
		}
		
		public function footer()
		{			
			$this->render('internalIncs/footer');
		}

		/**
		 * Cette fonction retourne une page js avec des constantes php sous forme js
		 */
		public function phptojs()
		{
			$this->render('internalIncs/phptojs');
		}
	}
