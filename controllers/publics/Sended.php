<?php
namespace controllers\publics;

    /**
     * Page des sendeds
     */
    class Sended extends \descartes\Controller
    {
        private $internal_sended;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_sended = new \controllers\internals\Sended($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les sendeds, sous forme d'un tableau permettant l'administration de ces sendeds
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $limit = 25;
            $sendeds = $this->internal_sended->get_list($limit, $page);
            $this->render('sended/list', ['sendeds' => $sendeds, 'page' => $page, 'limit' => $limit, 'nb_results' => count($sendeds)]);
        }
        
        /**
         * Cette fonction va supprimer une liste de sendeds
         * @param array int $_GET['ids'] : Les id des sendedes à supprimer
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf)) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return $this->redirect(\descartes\Router::url('Sended', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id) {
                $this->internal_sended->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Sended', 'list'));
        }
    }
