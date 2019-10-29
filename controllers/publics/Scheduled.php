<?php
namespace controllers\publics;
	/**
	 * Page des scheduleds
	 */
	class Scheduled extends \Controller
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

            $this->internalScheduled = new \controllers\internals\Scheduled($this->bdd);
            $this->internalEvent = new \controllers\internals\Event($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les scheduleds, sous forme d'un tableau permettant l'administration de ces scheduleds
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $scheduleds = $this->internalScheduled->get_list(25, $page);
            $this->render('scheduled/list', ['scheduleds' => $scheduleds]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de scheduleds
         * @param array int $_GET['ids'] : Les id des scheduledes à supprimer
         * @return boolean;
         */
        public function delete ($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Scheduled', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalScheduled->delete($id);
            }

            return header('Location: ' . \Router::url('Scheduled', 'list'));
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

            $scheduleds = $this->internalScheduled->get_by_ids($ids);

            //Pour chaque message on ajoute les numéros, les contacts & les groupes
            foreach ($scheduleds as $key => $scheduled)
            {
                $scheduleds[$key]['numbers'] = [];
                $scheduleds[$key]['contacts'] = [];
                $scheduleds[$key]['groupes'] = [];


                $numbers = $this->internalScheduled->get_numbers($scheduled['id']);
                foreach ($numbers as $number)
                {
                    $scheduleds[$key]['numbers'][] = $number['number'];
                }
                
                $contacts = $this->internalScheduled->get_contacts($scheduled['id']);
                foreach ($contacts as $contact)
                {
                    $scheduleds[$key]['contacts'][] = (int) $contact['id'];
                }
                
                $groupes = $this->internalScheduled->get_groupes($scheduled['id']);
                foreach ($groupes as $groupe)
                {
                    $scheduleds[$key]['groupes'][] = (int) $groupe['id'];
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
		 * @param string $_POST['groupes'] : Les groupes du scheduled
		 */
		public function create($csrf)
		{
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Scheduled', 'add'));
            }
			
			$date = $_POST['date'] ?? false;
            $content = $_POST['content'] ?? false;
            $numbers = $_POST['numbers'] ?? [];
            $contacts = $_POST['contacts'] ?? [];
            $groupes = $_POST['groupes'] ?? [];

            if (!$content)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous ne pouvez pas créer un SMS sans message.');
                return header('Location: ' . \Router::url('Scheduled', 'add'));
            }

            if (!\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i'))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez fournir une date valide.');
                return header('Location: ' . \Router::url('Scheduled', 'add'));
            }
            
            foreach ($numbers as $key => $number)
            {
                $number = \controllers\internals\Tool::parse_phone($number);

                if (!$number)
                {
                    unset($numbers[$key]);
                    continue;
                }

                $numbers[$key] = $number;   
            }

            if (!$numbers && !$contacts && !$groupes)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez renseigner au moins un destinataire pour le SMS.');
                return header('Location: ' . \Router::url('Scheduled', 'add'));
            }

            $scheduled = [
                'at' => $date,
                'content' => $content,
                'flash' => false,
                'progress' => false,
            ];

            if (!$scheduled_id = $this->internalScheduled->create($scheduled, $numbers, $contacts, $groupes))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de créer le SMS.');
                return header('Location: ' . \Router::url('Scheduled', 'add'));
            }

			\modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le SMS a bien été créé pour le ' . $date . '.');
			return header('Location: ' . \Router::url('Scheduled', 'list'));
		}

		/**
         * Cette fonction met à jour une schedulede
         * @param $csrf : Le jeton CSRF
         * @param array $_POST['scheduleds'] : Un tableau des scheduledes avec leur nouvelle valeurs + les numbers, contacts et groupes liées
         * @return boolean;
         */
        public function update($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Scheduled', 'list'));
            }
            
            $scheduleds = $_POST['scheduleds'] ?? [];

            $all_update_ok = true;

            foreach ($scheduleds as $id_scheduled => $scheduled)
            {

                $date = $scheduled['date'] ?? false;
                $content = $scheduled['content'] ?? false;
                $numbers = $scheduled['numbers'] ?? [];
                $contacts = $scheduled['contacts'] ?? [];
                $groupes = $scheduled['groupes'] ?? [];

                if (!$content)
                {
                    $all_update_ok = false;
                    continue;
                }

                if (!\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i:s') && !\controllers\internals\Tool::validate_date($date, 'Y-m-d H:i'))
                {
                    $all_update_ok = false;
                    continue;
                }
                
                foreach ($numbers as $key => $number)
                {
                    $number = \controllers\internals\Tool::parse_phone($number);

                    if (!$number)
                    {
                        unset($numbers[$key]);
                        continue;
                    }

                    $numbers[$key] = $number;   
                }

                if (!$numbers && !$contacts && !$groupes)
                {
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
                    'groupes_ids' => $groupes,
                ];

                if (!$this->internalScheduled->update([$scheduled]))
                {
                    $all_update_ok = false;
                    continue;
                }
            }

            if (!$all_update_ok)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Certains SMS n\'ont pas pu êtres mis à jour.');
                return header('Location: ' . \Router::url('Scheduled', 'list'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Tous les SMS ont été mis à jour.');
            return header('Location: ' . \Router::url('Scheduled', 'list'));
        }
	}
