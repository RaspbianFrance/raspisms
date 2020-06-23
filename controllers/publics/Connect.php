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

    /**
     * Page de connexion.
     */
    class Connect extends \descartes\Controller
    {
        private $internal_user;
        private $internal_setting;

        /**
         * Cette fonction est appelée avant toute les autres :.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

            $this->internal_user = new \controllers\internals\User($bdd);
            $this->internal_setting = new \controllers\internals\Setting($bdd);
        }

        /**
         * Cette fonction retourne la fenetre de connexion.
         */
        public function login()
        {
            if (\controllers\internals\Tool::is_connected())
            {
                return $this->redirect(\descartes\Router::url('Dashboard', 'show'));
            }

            return $this->render('connect/login');
        }

        /**
         * Cette fonction connecte un utilisateur, et le redirige sur la page d'accueil.
         *
         * @param string $_POST['mail']     : L'email de l'utilisateur
         * @param string $_POST['password'] : Le mot de passe de l'utilisateur
         */
        public function connection()
        {
            $email = $_POST['mail'] ?? false;
            $password = $_POST['password'] ?? false;

            $user = $this->internal_user->check_credentials($email, $password);
            if (!$user)
            {
                \FlashMessage\FlashMessage::push('danger', 'Email ou mot de passe invalide.');

                return $this->redirect(\descartes\Router::url('Connect', 'login'));
            }

            if (\models\User::STATUS_ACTIVE !== $user['status'])
            {
                \FlashMessage\FlashMessage::push('danger', 'Votre compte est actuellement suspendu.');

                return $this->redirect(\descartes\Router::url('Connect', 'login'));
            }

            $settings = $this->internal_setting->gets_for_user($user['id']);
            $user['settings'] = $settings;

            $_SESSION['connect'] = true;
            $_SESSION['user'] = $user;

            return $this->redirect(\descartes\Router::url('Dashboard', 'show'));
        }

        /**
         * Cette fonction retourne la fenetre de changement de password.
         *
         * @return void;
         */
        public function forget_password()
        {
            $this->render('connect/forget-password');
        }

        /**
         * Cette fonction envoi un email contenant un lien pour re-générer un password oublié.
         *
         * @param string $csrf           : jeton csrf
         * @param string $_POST['email'] : L'email pour lequel on veut envoyer un nouveau password
         */
        public function send_reset_password($csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Connect', 'forget_password'));
            }

            $email = $_POST['email'] ?? false;
            $user = $this->internal_user->get_by_email($email);

            if (!$email || !$user)
            {
                \FlashMessage\FlashMessage::push('danger', 'Aucun utilisateur n\'existe pour cette adresse mail.');

                return $this->redirect(\descartes\Router::url('Connect', 'forget_password'));
            }

            $Tokenista = new \Ingenerator\Tokenista(APP_SECRET);
            $token = $Tokenista->generate(3600, ['id_user' => $user['id']]);

            $reset_link = \descartes\Router::url('Connect', 'reset_password', ['id_user' => $user['id'], 'token' => $token]);

            $mailer = new \controllers\internals\Mailer();
            $email_send = $mailer->enqueue($email, EMAIL_RESET_PASSWORD, ['reset_link' => $reset_link]);

            return $this->render('connect/send-reset-password');
        }

        /**
         * Cette fonction permet à un utilisateur de re-définir son mot de passe.
         *
         * @param int       $id_user           : L'id du user dont on veut modifier le password
         * @param string    $token             : Le token permetttant de vérifier que l'opération est légitime
         * @param optionnal $_POST['password'] : Le nouveau password à utiliser
         */
        public function reset_password($id_user, $token)
        {
            $password = $_POST['password'] ?? false;

            $Tokenista = new \Ingenerator\Tokenista(APP_SECRET);

            if (!$Tokenista->isValid($token, ['id_user' => $id_user]))
            {
                return $this->render('connect/reset-password-invalid');
            }

            if (!$password)
            {
                return $this->render('connect/reset-password');
            }

            $this->internal_user->update_password($id_user, $password);

            return $this->render('connect/reset-password-done');
        }

        /**
         * Cette fonction déconnecte un utilisateur et le renvoie sur la page d'accueil.
         */
        public function logout()
        {
            session_destroy();
            $_SESSION = [];

            return $this->redirect(\descartes\Router::url('Connect', 'login'));
        }
    }
