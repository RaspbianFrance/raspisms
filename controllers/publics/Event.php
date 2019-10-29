<?php
namespace controllers\publics;
	/**
	 * Page des events
	 */
	class Event extends \Controller
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

            $this->internalEvent = new \controllers\internals\Event($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les events, sous forme d'un tableau permettant l'administration de ces events
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $events = $this->internalEvent->get_list($limit, $page);
            $this->render('event/list', ['events' => $events, 'limit' => $limit, 'page' => $page, 'nb_results' => count($events)]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de events
         * @param array int $_GET['ids'] : Les id des eventes à supprimer
         * @return boolean;
         */
        public function delete ($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Event', 'list'));
            }
            
            if (!\controllers\internals\Tool::is_admin())
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être admin pour pouvoir supprimer des events.');
                return header('Location: ' . \Router::url('Event', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalEvent->delete($id);
            }

            return header('Location: ' . \Router::url('Event', 'list'));
        }
	}
