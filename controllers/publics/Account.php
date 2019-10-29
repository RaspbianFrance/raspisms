<?php
    namespace controllers\publics;

    class Account extends \descartes\Controller
    {
        public $internal_user;
        
        public function __construct()
        {
            $bdd = Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_user = new \controllers\internals\User($bdd);

            \controllers\internals\Tool::verify_connect();
        }

        /**
         * Show profile page
         */
        public function show()
        {
            $this->render('account/show');
        }

        /**
         * Update connected user password
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['password'] : The new password
         * @return void;
         */
        public function update_password($csrf)
        {
            $password = $_POST['password'] ?? false;
            
            if (!$this->verifyCSRF($csrf)) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            if (!$password) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez renseigner un mot de passe.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }


            $update_password_result = $this->internal_user->update_password($_SESSION['user']['id'], $password);
            if (!$update_password_result) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de mettre à jour le mot de passe.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            \DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le mot de passe a bien été mis à jour.');
            return header('Location: ' . \descartes\Router::url('Account', 'show'));
        }

        /**
         * Update user mail transfer property
         * @param $csrf : CSRF token
         * @param string $_POST['transfer'] : New transfer property value
         */
        public function update_transfer($csrf)
        {
            $transfer = $_POST['transfer'] ?? false;
            
            if (!$this->verifyCSRF($csrf)) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            if ($transfer === false) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez choisir une option parmis celles de la liste déroulante.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            $transfer_update_result = $this->internal_user->update_transfer($_SESSION['user']['id'], $transfer);
            if (!$transfer_update_result) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de mettre à jour.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            $_SESSION['user']['transfer'] = $transfer;
            
            \DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le transfert a bien été ' . ($transfer ? 'activé' : 'désactivé') . '.');
            return header('Location: ' . \descartes\Router::url('Account', 'show'));
        }

        /**
         * Update user email
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['email'] : User new email
         * @param string $_POST['verif_email'] : Verif email
         */
        public function update_email($csrf)
        {
            if (!$this->verifyCSRF($csrf)) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            $email = $_POST['email'] ?? false;
            
            if (!$email) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez fournir une adresse e-mail !');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'L\'adresse e-mail n\'est pas une adresse valide.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            $update_email_result = $this->internal_user->update_email($_SESSION['user']['id'], $email);
            if (!$update_email_result) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de mettre à jour.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }
            
            $_SESSION['user']['email'] = $email;

            \DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'L\'email a bien été mis à jour.');
            return header('Location: ' . \descartes\Router::url('Account', 'show'));
        }

        /**
         * Delete a user
         * @param string $_POST['delete_account'] : Boolean to see if we want to delete
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verifyCSRF($csrf)) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            $delete_account = $_POST['delete_account'] ?? false;

            if (!$delete_account) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Pour supprimer le compte, vous devez cocher la case correspondante.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }
            
            $delete_account_result = $this->internal_user->delete($_SESSION['user']['id']);
            if (!$delete_account_result) {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de supprimer le compte.');
                return header('Location: ' . \descartes\Router::url('Account', 'show'));
            }

            return $this->logout();
        }

        /**
         * Logout a user and redirect to login page
         * @return void
         */
        public function logout()
        {
            session_unset();
            session_destroy();
            return header('Location: ' . \descartes\Router::url('Connect', 'login'));
        }
    }
