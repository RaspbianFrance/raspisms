<?php
    namespace descartes;
	/**
	 * Cette classe sert de mère à tous les controlleurs destiné à la création d'API. Elle hérite de toutes les infos d'un controller standard, mais elle embarque en plus des mécanismes propres aux api REST, etc.
	 */
	class ApiController extends Controller
	{
		/**
		 * Cette fonction construit la classe, elle prend en paramètre obligatoire le type de méthode (PUT, GET, POST, etc) avec laquel on a appelée la page
		 */
		public function __construct()
		{
			//On va choisir le type à employer
			$method = $_SERVER['REQUEST_METHOD'];
			switch (mb_convert_case($method, MB_CASE_LOWER))
			{
				case 'delete' :
					$this->method = 'DELETE';
					break;
				case 'patch' :
					$this->method = 'PATCH';
					break;
				case 'post' :
					$this->method = 'POST';
					break;
				case 'put' :
					$this->method = 'PUT';
					break;
				default :
					$this->method = 'GET';
			}
		}

		/**
		 * Cette fonction permet d'effectuer n'importe quelle opération sur le header en retournant le controller en cours
		 * @param string $key : La clef du header
		 * @param string $value : La valeur à donner
		 * @return ApiController : On retourne l'API controlleur lui meme pour pouvoir chainer
		 */
		public function set_header ($key, $value)
		{
			header($key . ': ' . $value);
			return $this;	
		}

		/**
		 * Cette fonction permet de définir un code de retour HTTP
		 * @param int $code : Le numéro du code de retour HTTP
		 * @return ApiController : On retourne l'API controlleur lui meme pour pouvoir chainer
		 */
		public function set_http_code ($code)
		{
			http_response_code($code);
			return $this;
		}

		/**
		 * Cette fonction permet de définir un code de retour automatiquement selon le contexte du controller (c'est à dire si on l'appel en POST, en GET, etc.)
		 * @param boolean $success : Si la requete est un succes ou non (par défaut à true)
		 * @return ApiController : On retourne l'API controlleur lui meme pour pouvoir chainer
		 */
		public function auto_http_code ($success = true)
		{
			$response_codes = array(
				'GET' => array(
					'success' => 200,
					'fail' => 404,
				),
				'DELETE' => array(
					'success' => 204,
					'fail' => 400,
				),
				'PATCH' => array(
					'success' => 204,
					'fail' => 400,
				),
				'POST' => array(
					'success' => 201,
					'fail' => 400,
				),
				'PUT' => array(
					'success' => 204,
					'fail' => 400,
				),				
			);

			$key = $success ? 'success' : 'fail';

			return $this->set_http_code($response_codes[$this->method][$key]);
		}
	
		/**
		 * Cette fonction permet de faire un retour sous forme de json
		 * @param array $data : Les données à retourner sous forme de json
		 * @param boolean $secure : Défini si l'affichage doit être sécurisé contre les XSS, par défaut true
		 * @return ApiController : On retourne l'API controlleur lui meme pour pouvoir chainer
		 */
		public function json ($data, $secure = true)
		{
			header('Content-Type: application/json');
			
			if ($secure)
			{
				echo htmlspecialchars(json_encode($data), ENT_NOQUOTES);
			}
			else
			{
				echo json_encode($data);
			}

			return $this;
		}

		/**
		 * Cette fonction permet de fixer la location renvoyée en HTTP
		 * @param string $location : La location à renvoyer
		 * @return ApiController : On retourne l'API controlleur lui meme pour pouvoir chainer
		 */
		public function set_location ($location)
		{
			header('Location: ' . $location);
			return $this;
		}
	} 
