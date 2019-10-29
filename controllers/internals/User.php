<?php
    namespace controllers\internals;

    class User extends \descartes\InternalController
    {
        private $model_user;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_user = new \models\User($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * Return list of users as an array
         * @param mixed(int|bool) $nb_entry : Number of entry to return
         * @param mixed(int|bool) $page : Numero of page
         * @return array|bool : List of user or false
         */
        public function list(?int $nb_entry = null, ?int $page = null)
        {
            return $this->model_user->list($nb_entry, $page);
        }
        
        /**
         * Cette fonction va supprimer une liste de users
         * @param array $ids : Les id des useres à supprimer
         * @return int : Le nombre de useres supprimées;
         */
        public function delete($id)
        {
            return $this->model_user->remove($id);
        }

        /**
         * Cette fonction vérifie s'il existe un utilisateur qui corresponde à ce couple login/password
         * @param string $email : L'eamil de l'utilisateur
         * @param string $password : Le mot de passe de l'utilisateur
         * @return mixed false | array : False si pas de user, le user correspondant sous forme d'array sinon
         */
        public function check_credentials($email, $password)
        {
            $user = $this->model_user->get_by_email($email);
            if (!$user) {
                return false;
            }

            if (!password_verify($password, $user['password'])) {
                return false;
            }

            return $user;
        }

        /**
         * Update a user password
         * @param string $id : User id
         * @param string $password : New password
         * @return bool;
         */
        public function update_password(int $id, string $password) : bool
        {
            $password = password_hash($password, PASSWORD_DEFAULT);
            return (bool) $this->model_user->update_password($id, $password);
        }
        
        /**
         * Update a user transfer property value
         * @param string $id : User id
         * @param string $transfer : New value of property transfer
         * @return boolean;
         */
        public function update_transfer(int $id, int $transfer) : bool
        {
            return (bool) $this->model_user->update_transfer($id, $transfer);
        }
        
        /**
         * Update user email
         * @param string $id : user id
         * @param string $email : new mail
         * @return boolean;
         */
        public function update_email($id, $email)
        {
            return (bool) $this->model_user->update_email($id, $email);
        }

        /**
         * Cette fonction retourne un utilisateur pour un mail donné
         * @param string $email : L'email de l'utilisateur
         * @return mixed boolean | array : false si pas de user pour le mail, le user sinon
         */
        public function get_by_email($email)
        {
            return $this->model_user->get_by_email($email);
        }

        /**
         * Cette fonction met à jour une série de users
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $email, $password, $admin, $transfer)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'transfer' => $transfer,
            ];

            return $this->model_user->update($id, $user);
        }
        
        /**
         * Cette fonction insert une nouvelle usere
         * @param array $user : Un tableau représentant la usere à insérer
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle usere insérée
         */
        public function create($email, $password, $admin, $transfer = false)
        {
            $user = [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'admin' => $admin,
                'transfer' => $transfer,
            ];

            $result = $this->model_user->insert($user);

            if (!$result) {
                return false;
            }

            $this->internal_event->create('CONTACT_ADD', 'Ajout de l\'utilisateur : ' . $email . '.');
            
            return $result;
        }
    }
