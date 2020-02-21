<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    class Account extends \descartes\Controller
    {
        public $internal_user;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_user = new \controllers\internals\User($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Show profile page.
         */
        public function show()
        {
            $this->render('account/show');
        }

        /**
         * Update connected user password.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['password'] : The new password
         *
         * @return void;
         */
        public function update_password($csrf)
        {
            $password = $_POST['password'] ?? false;

            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            if (!$password)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez renseigner un mot de passe.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $update_password_result = $this->internal_user->update_password($_SESSION['user']['id'], $password);
            if (!$update_password_result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de mettre à jour le mot de passe.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            \FlashMessage\FlashMessage::push('success', 'Le mot de passe a bien été mis à jour.');

            return $this->redirect(\descartes\Router::url('Account', 'show'));
        }

        /**
         * Update user email.
         *
         * @param $csrf : Le jeton CSRF
         * @param string $_POST['email']       : User new email
         * @param string $_POST['verif_email'] : Verif email
         */
        public function update_email($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $email = $_POST['email'] ?? false;

            if (!$email)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez fournir une adresse e-mail !');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                \FlashMessage\FlashMessage::push('danger', 'L\'adresse e-mail n\'est pas une adresse valide.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $update_email_result = $this->internal_user->update_email($_SESSION['user']['id'], $email);
            if (!$update_email_result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de mettre à jour.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $_SESSION['user']['email'] = $email;

            \FlashMessage\FlashMessage::push('success', 'L\'email a bien été mis à jour.');

            return $this->redirect(\descartes\Router::url('Account', 'show'));
        }

        /**
         * Update user api key.
         *
         * @param $csrf : Le jeton CSRF
         */
        public function update_api_key($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $new_api_key = $this->internal_user->update_api_key($_SESSION['user']['id']);
            if (!$new_api_key)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de mettre à jour.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $_SESSION['user']['api_key'] = $new_api_key;

            \FlashMessage\FlashMessage::push('success', 'Votre ancienne clef API a été désactivée et une nouvelle clef générée.');

            return $this->redirect(\descartes\Router::url('Account', 'show'));
        }

        /**
         * Delete a user.
         *
         * @param string $_POST['delete_account'] : Boolean to see if we want to delete
         * @param mixed  $csrf
         *
         * @return boolean;
         */
        public function delete($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $delete_account = $_POST['delete_account'] ?? false;

            if (!$delete_account)
            {
                \FlashMessage\FlashMessage::push('danger', 'Pour supprimer le compte, vous devez cocher la case correspondante.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            $delete_account_result = $this->internal_user->delete($_SESSION['user']['id']);
            if (!$delete_account_result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de supprimer le compte.');

                return $this->redirect(\descartes\Router::url('Account', 'show'));
            }

            return $this->logout();
        }

        /**
         * Logout a user and redirect to login page.
         */
        public function logout()
        {
            session_unset();
            session_destroy();

            return $this->redirect(\descartes\Router::url('Connect', 'login'));
        }
    }
