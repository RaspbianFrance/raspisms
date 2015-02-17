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
			$title = (!empty($title)) ? $title . ' - RaspiSMS' : 'RaspiSMS';	
			$author = 'Ajani';

			$error_message = (isset($_GET['errormessage'])) ? $_GET['errormessage'] : '';
			$success_message = (isset($_GET['successmessage'])) ? $_GET['successmessage'] : '';

			$this->render('head', array(
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
			$this->render('nav', array(
				'email' => $email,
				'page' => $page,
			));
		}
		
		public function footer()
		{			
			$this->render('footer');
		}
	}
