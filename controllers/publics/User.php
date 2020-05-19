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
 * Page des users.
 */
class User extends \descartes\Controller
{
    private $internal_user;

    /**
     * Cette fonction est appelée avant toute les autres :
     * Elle vérifie que l'utilisateur est bien connecté.
     *
     * @return void;
     */
    public function __construct()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $this->internal_user = new \controllers\internals\User($bdd);

        \controllers\internals\Tool::verifyconnect();

        if (!\controllers\internals\Tool::is_admin())
        {
            return $this->redirect(\descartes\Router::url('Dashboard', 'show'));
        }
    }

    /**
     * Cette fonction retourne tous les users, sous forme d'un tableau permettant l'administration de ces users.
     */
    public function list()
    {
        $users = $this->internal_user->list();
        $this->render('user/list', ['users' => $users]);
    }
    
    
    /**
     * Update status of users
     *
     * @param array int $_GET['ids'] : User ids
     * @param mixed     $csrf
     * @param int $status : 1 -> active, 0 -> suspended
     *
     * @return boolean;
     */
    public function update_status ($csrf, int $status)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        if ($status == 0)
        {
            $status = \models\User::STATUS_SUSPENDED;
        }
        else
        {
            $status = \models\User::STATUS_ACTIVE;
        }

        $ids = $_GET['ids'] ?? [];
        foreach ($ids as $id)
        {
            $this->internal_user->update_status($id, $status);
        }

        return $this->redirect(\descartes\Router::url('User', 'list'));
    }


    /**
     * Cette fonction va supprimer une liste de users.
     *
     * @param array int $_GET['ids'] : Les id des useres à supprimer
     * @param mixed     $csrf
     *
     * @return boolean;
     */
    public function delete($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        if (!\controllers\internals\Tool::is_admin())
        {
            \FlashMessage\FlashMessage::push('danger', 'Vous devez être administrateur pour supprimer un utilisateur !');

            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        $ids = $_GET['ids'] ?? [];
        foreach ($ids as $id)
        {
            $this->internal_user->delete($id);
        }

        return $this->redirect(\descartes\Router::url('User', 'list'));
    }

    /**
     * Cette fonction retourne la page d'ajout d'un user.
     */
    public function add()
    {
        return $this->render('user/add');
    }

    /**
     * Cette fonction insert un nouveau user.
     *
     * @param $csrf : Le jeton CSRF
     * @param string           $_POST['email']            : L'email de l'utilisateur
     * @param string           $_POST['email_confirm']    : Verif de l'email de l'utilisateur
     * @param optional string  $_POST['password']         : Le mot de passe de l'utilisateur (si vide, généré automatiquement)
     * @param optional string  $_POST['password_confirm'] : Confirmation du mot de passe de l'utilisateur
     * @param optional boolean $_POST['admin']            : Si vrai, l'utilisateur est admin, si vide non
     */
    public function create($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('User', 'add'));
        }

        $email = $_POST['email'] ?? false;
        $password = !empty($_POST['password']) ? $_POST['password'] : \controllers\internals\Tool::generate_password(rand(6, 12));
        $admin = $_POST['admin'] ?? false;
        $status = 'active';

        if (!$email)
        {
            \FlashMessage\FlashMessage::push('danger', 'Vous devez au moins fournir une adresse e-mail pour l\'utilisateur.');

            return $this->redirect(\descartes\Router::url('User', 'add'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            \FlashMessage\FlashMessage::push('danger', 'L\'adresse e-mail n\'est pas valide.');

            return $this->redirect(\descartes\Router::url('User', 'add'));
        }

        $id_user = $this->internal_user->create($email, $password, $admin);
        if (!$id_user)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce user.');

            return $this->redirect(\descartes\Router::url('User', 'add'));
        }

        $mailer = new \controllers\internals\Mailer();
        $email_send = $mailer->enqueue($email, EMAIL_CREATE_USER, ['email' => $email, 'password' => $password]);
        if (!$email_send)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible d\'envoyer l\'e-mail à l\'utilisateur.');
        }

        \FlashMessage\FlashMessage::push('success', 'L\'utilisateur a bien été créé.');

        return $this->redirect(\descartes\Router::url('User', 'list'));
    }
}
