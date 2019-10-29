<?php
namespace controllers\publics;
	/**
	 * Page des sendeds
	 */
	class Sended extends \Controller
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

            $this->internalSended = new \controllers\internals\Sended($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les sendeds, sous forme d'un tableau permettant l'administration de ces sendeds
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $sendeds = $this->internalSended->get_list($limit, $page);
            $this->render('sended/list', ['sendeds' => $sendeds, 'page' => $page, 'limit' => $limit, 'nb_results' => count($sendeds)]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de sendeds
         * @param array int $_GET['ids'] : Les id des sendedes à supprimer
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Sended', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalSended->delete($id);
            }

            return header('Location: ' . \Router::url('Sended', 'list'));
        }
	}
