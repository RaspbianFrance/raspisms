<?php
namespace controllers\publics;

    /**
     * Page des scheduleds
     */
    class Scheduled extends \descartes\Controller
    {
        private $internal_scheduled;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_scheduled = new \controllers\internals\Scheduled($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction retourne tous les scheduleds, sous forme d'un tableau permettant l'administration de ces scheduleds
         */
        public function list($page = 0)
        {
            $page = (int) $page;
            $scheduleds = $this->internal_scheduled->get_list(25, $page);
            $this->render('scheduled/list', ['scheduleds' => $scheduleds]);
        }
        
        /**
         * Cette fonction va supprimer une liste de scheduleds
         * @param array int $_GET['ids'] : Les id des scheduledes à supprimer
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf)) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id) {
                $this->internal_scheduled->delete($id);
            }

            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction retourne la page d'ajout d'un scheduled
         */
        public function add()
        {
            $now = new \DateTime();
            $less_one_minute = new \DateInterval('PT1M');
            $now->sub($less_one_minute);

            $this->render('scheduled/add', [
                'now' => $now->format('Y-m-d H:i'),
            ]);
        }

        /**
         * Cette fonction retourne la page d'édition des scheduleds
         * @param int... $ids : Les id des scheduledes à supprimer
         */
        public function edit()
        {
            $ids = $_GET['ids'] ?? [];

            $scheduleds = $this->internal_scheduled->get_by_ids($ids);

            //Pour chaque message on ajoute les numéros, les contacts & les groups
            foreach ($scheduleds as $key => $scheduled) {
                $scheduleds[$key]['numbers'] = [];
                $scheduleds[$key]['contacts'] = [];
                $scheduleds[$key]['groups'] = [];


                $numbers = $this->internal_scheduled->get_numbers($scheduled['id']);
                foreach ($numbers as $number) {
                    $scheduleds[$key]['numbers'][] = $number['number'];
                }
                
                $contacts = $this->internal_scheduled->get_contacts($scheduled['id']);
                foreach ($contacts as $contact) {
                    $scheduleds[$key]['contacts'][] = (int) $contact['id'];
                }
                
                $groups = $this->internal_scheduled->get_groups($scheduled['id']);
                foreach ($groups as $group) {
                    $scheduleds[$key]['groups'][] = (int) $group['id'];
                }
            }


            $this->render('scheduled/edit', array(
                'scheduleds' => $scheduleds,
            ));
        }

        /**
         * Cette fonction insert un nouveau scheduled
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['name'] : Le nom du scheduled
         * @param string $_POST['date'] : La date d'envoie du scheduled
         * @param string $_POST['numbers'] : Les numeros de téléphone du scheduled
         * @param string $_POST['contacts'] : Les contacts du scheduled
         * @param string $_POST['groups'] : Les groups du scheduled
         */
        public function create($csrf)
        {
            if (!$this->verify_csrf($csrf)) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }
            
            $date = $_POST['date'] ?? false;
            $content = $_POST['content'] ?? false;
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groups = $_POST['groups'] ?? [];

            if (!$content) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous ne pouvez pas créer un Sms sans message.');
                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            if (!\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i')) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez fournir une date valide.');
                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }
            
            foreach ($numbers as $key => $number) {
                $number = \controllers\internals\Tool::parse_phone($number);

                if (!$number) {
                    unset($numbers[$key]);
                    continue;
                }

                $numbers[$key] = $number;
            }

            if (!$numbers && !$contacts && !$groups) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez renseigner au moins un destinataire pour le Sms.');
                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            $scheduled = [
                'at' => $date,
                'content' => $content,
                'flash' => false,
                'progress' => false,
            ];

            if (!$scheduled_id = $this->internal_scheduled->create($scheduled, $numbers, $contacts, $groups)) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de créer le Sms.');
                return $this->redirect(\descartes\Router::url('Scheduled', 'add'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le Sms a bien été créé pour le ' . $date . '.');
            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }

        /**
         * Cette fonction met à jour une schedulede
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['scheduleds'] : Un tableau des scheduledes avec leur nouvelle valeurs + les numbers, contacts et groups liées
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verify_csrf($csrf)) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }
            
            $scheduleds = $_POST['scheduleds'] ?? [];

            $all_update_ok = true;

            foreach ($scheduleds as $id_scheduled => $scheduled) {
                $date = $scheduled['date'] ?? false;
                $content = $scheduled['content'] ?? false;
                $numbers = $scheduled['numbers'] ?? [];
                $contacts = $scheduled['contacts'] ?? [];
                $groups = $scheduled['groups'] ?? [];

                if (!$content) {
                    $all_update_ok = false;
                    continue;
                }

                if (!\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i')) {
                    $all_update_ok = false;
                    continue;
                }
                
                foreach ($numbers as $key => $number) {
                    $number = \controllers\internals\Tool::parse_phone($number);

                    if (!$number) {
                        unset($numbers[$key]);
                        continue;
                    }

                    $numbers[$key] = $number;
                }

                if (!$numbers && !$contacts && !$groups) {
                    $all_update_ok = false;
                    continue;
                }

                $scheduled = [
                    'scheduled' => [
                        'id' => $id_scheduled,
                        'at' => $date,
                        'content' => $content,
                        'flash' => false,
                        'progress' => false,
                    ],
                    'numbers' => $numbers,
                    'contacts_ids' => $contacts,
                    'groups_ids' => $groups,
                ];

                if (!$this->internal_scheduled->update([$scheduled])) {
                    $all_update_ok = false;
                    continue;
                }
            }

            if (!$all_update_ok) {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Certains Sms n\'ont pas pu êtres mis à jour.');
                return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Tous les Sms ont été mis à jour.');
            return $this->redirect(\descartes\Router::url('Scheduled', 'list'));
        }
    }
