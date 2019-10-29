<?php
namespace controllers\publics;
	/**
	 * Page des receiveds
	 */
	class Received extends \Controller
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

            $this->internalReceived = new \controllers\internals\Received($this->bdd);
            $this->internalContact = new \controllers\internals\Contact($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les receiveds, sous forme d'un tableau permettant l'administration de ces receiveds
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $receiveds = $this->internalReceived->get_list($limit, $page);

            foreach ($receiveds as $key => $received)
            {
                if (!$contact = $this->internalContact->get_by_number($received['origin']))
                {
                    continue;
                }

                $receiveds[$key]['send_by'] = $contact['name'] . ' (' . $received['origin'] . ')';
            }

            $this->render('received/list', ['receiveds' => $receiveds, 'page' => $page, 'limit' => $limit, 'nb_results' => count($receiveds)]);
        }    

        /**
         * Cette fonction retourne tous les SMS reçus aujourd'hui pour la popup
         * @return json : Un tableau des SMS reçus
         */
        public function popup ()
        {
            $now = new \DateTime();
            $receiveds = $this->internalReceived->get_since_by_date($now->format('Y-m-d'));
        
            foreach ($receiveds as $key => $received)
            {
                if (!$contact = $this->internalContact->get_by_number($received['origin']))
                {
                    continue;
                }

                $receiveds[$key]['origin'] = $contact['name'] . ' (' . $received['origin'] . ')';
            }
        
            $nb_received = count($receiveds);

            if (!isset($_SESSION['popup_nb_receiveds']) || $_SESSION['popup_nb_receiveds'] > $nb_receiveds)
            {
                $_SESSION['popup_nb_receiveds'] = $nb_received;
            }

            $newly_receiveds = array_slice($receiveds, $_SESSION['popup_nb_receiveds']);
            
            $_SESSION['popup_nb_receiveds'] = $nb_receiveds;

            echo json_encode($newly_receiveds);
            return true;
        }

		/**
         * Cette fonction va supprimer une liste de receiveds
         * @param array int $_GET['ids'] : Les id des receivedes à supprimer
         * @return boolean;
         */
        public function delete ($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Received', 'list'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être administrateur pour effectuer cette action.');
                return header('Location: ' . \Router::url('Received', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalReceived->delete($id);
            }

            return header('Location: ' . \Router::url('Received', 'list'));
        }
	}
