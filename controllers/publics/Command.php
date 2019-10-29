<?php
namespace controllers\publics;
	/**
	 * Page des commandes
	 */
	class Command extends \Controller
    {
        public $bdd;


        public function __construct ()
        {
            $bdd = Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_command = new \Controller\internals\Command($bdd);
            $this->internal_event = new \Controller\internals\Event($bdd);
            
            \controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les users, sous forme d'un tableau permettant l'administration de ces users
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $commands = $this->internal_command->list(25, $page);
            $this->render('command/list', ['commands' => $commands]);
        }    

		/**
		 * Cette fonction va supprimer une liste de commands
		 * @param array int $_GET['ids'] : Les id des commandes à supprimer
		 * @return boolean;
		 */
		public function delete($csrf)
		{
			if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
				header('Location: ' . \Router::url('Command', 'list'));
				return false;
			}

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalCommand->delete($id);
            }
            
			header('Location: ' . \Router::url('Command', 'list'));		
			return true;
		}

		/**
		 * Cette fonction retourne la page d'ajout d'une commande
		 */
		public function add()
		{
			$this->render('command/add');
		}

		/**
		 * Cette fonction retourne la page d'édition des commandes
		 * @param array int $_GET['ids'] : Les id des commandes à editer
		 */
		public function edit()
		{
			global $db;
            $ids = $_GET['ids'] ?? [];
            
            $commands = $this->internalCommand->get_by_ids($ids);

			$this->render('command/edit', array(
				'commands' => $commands,
			));
		}

		/**
		 * Cette fonction insert une nouvelle commande
		 * @param $csrf : Le jeton CSRF
		 * @param string $_POST['name'] : Le nom de la commande
		 * @param string $_POST['script'] : Le script a appeler
		 * @param boolean $_POST['admin'] : Si la commande necessite les droits d'admin (par défaut non)
		 * @return boolean;
		 */
		public function create($csrf)
		{
			if (!$this->verifyCSRF($csrf))
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
				header('Location: ' . \Router::url('Command', 'list'));
				return false;
			}

			$name = $_POST['name'] ?? false;
			$script = $_POST['script'] ?? false;
			$admin = (isset($_POST['admin']) ? $_POST['admin'] : false);

            if (!$name || !$script)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Renseignez au moins un nom et un script.');
				return header('Location: ' . \Router::url('Command', 'list'));
            }

    
            if (!$this->internalCommand->create($name, $script, $admin))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible créer cette commande.');
				return header('Location: ' . \Router::url('commands', 'add'));
            }
			
            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'La commande a bien été crée.');
			return header('Location: ' . \Router::url('Command', 'list'));
		}

		/**
		 * Cette fonction met à jour une commande
		 * @param $csrf : Le jeton CSRF
		 * @param array $_POST['commands'] : Un tableau des commandes avec leur nouvelle valeurs
		 * @return boolean;
		 */
		public function update($csrf)
		{
			if (!$this->verifyCSRF($csrf))
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
				header('Location: ' . \Router::url('Command', 'list'));
				return false;
            }

            $nb_commands_update = 0;
            foreach ($_POST['commands'] as $command)
            {
                $update_command = $this->internalCommand->update($command['id'], $command['name'], $command['script'], $command['admin']);
                $nb_commands_update += (int) $update_command;
            }

            if ($nb_commands_update != count($_POST['commands']))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Certaines commandes n\'ont pas pu êtres mises à jour.');
				header('Location: ' . \Router::url('Command', 'list'));
				return false;
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Toutes les commandes ont été modifiées avec succès.');
            header('Location: ' . \Router::url('Command', 'list'));
            return true;
		}
	}
