<?php
namespace controllers\publics;
	/**
	 * Page des smsstops
	 */
	class SMSStop extends \Controller
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

            $this->internalSMSStop = new \controllers\internals\SMSStop($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les smsstops, sous forme d'un tableau permettant l'administration de ces smsstops
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $smsstops = $this->internalSMSStop->get_list($limit, $page);
            $this->render('smsstop/list', ['page' => $page, 'smsstops' => $smsstops, 'limit' => $limit, 'nb_results' => count($smsstops)]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de smsstops
         * @param array int $_GET['ids'] : Les id des smsstopes à supprimer
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('SMSStop', 'list'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être administrateur pour pouvoir supprimer un "STOP SMS" !');
                return header('Location: ' . \Router::url('SMSStop', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalSMSStop->delete($id);
            }

            return header('Location: ' . \Router::url('SMSStop', 'list'));
        }

	}
