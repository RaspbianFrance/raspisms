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
    private $internal_quota;

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
        $this->internal_quota = new \controllers\internals\Quota($bdd);

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
        $this->render('user/list');
    }

    /**
     * Return users as json.
     */
    public function list_json()
    {
        $entities = $this->internal_user->list();

        foreach ($entities as &$entity)
        {
            $quota_percentage = $this->internal_quota->get_usage_percentage($entity['id']);
            $entity['quota_percentage'] = $quota_percentage * 100;

            $quota = $this->internal_quota->get_user_quota($entity['id']);
            if (!$quota)
            {
                continue;
            }

            if (new \DateTime() > new \DateTime($quota['expiration_date']))
            {
                $entity['quota_expired_at'] = $quota['expiration_date'];
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $entities]);
    }

    /**
     * Update status of users.
     *
     * @param array int $_GET['user_ids'] : User ids
     * @param mixed     $csrf
     * @param int       $status           : 1 -> active, 0 -> suspended
     *
     * @return boolean;
     */
    public function update_status($csrf, int $status)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        if (0 === $status)
        {
            $status = \models\User::STATUS_SUSPENDED;
        }
        else
        {
            $status = \models\User::STATUS_ACTIVE;
        }

        $ids = $_GET['user_ids'] ?? [];
        foreach ($ids as $id)
        {
            $this->internal_user->update_status($id, $status);
        }

        return $this->redirect(\descartes\Router::url('User', 'list'));
    }

    /**
     * Cette fonction va supprimer une liste de users.
     *
     * @param array int $_GET['user_ids'] : Les id des useres à supprimer
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

        $ids = $_GET['user_ids'] ?? [];
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
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:00');

        return $this->render('user/add', ['now' => $now]);
    }

    /**
     * Cette fonction insert un nouveau user.
     *
     * @param $csrf : Le jeton CSRF
     * @param string           $_POST['email']                          : User email
     * @param optional string  $_POST['password']                       : User password, (if empty the password is randomly generated)
     * @param optional boolean $_POST['admin']                          : If true user is admin
     * @param optional boolean $_POST['quota_enable']                   : If true create a quota for the user
     * @param bool             $_POST['quota_enable']                   : If true create a quota for the user
     * @param optional int     $_POST['quota_credit']                   : credit for quota
     * @param optional int     $_POST['quota_additional']               : additional credit
     * @param optional string  $_POST['quota_start_date']               : quota beginning date
     * @param optional string  $_POST['quota_renewal_interval']         : period to use on renewal to calculate new expiration date. Also use to calculate first expiration date.
     * @param optional boolean $_POST['quota_auto_renew']               : Should the quota be automatically renewed on expiration
     * @param optional boolean $_POST['quota_report_unused']            : Should unused credit be reported next month
     * @param optional boolean $_POST['quota_report_unused_additional'] : Should unused additional credit be transfered next month
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
        $quota_enable = $_POST['quota_enable'] ?? false;
        $quota_credit = $_POST['quota_credit'] ?? false;
        $quota_additional = $_POST['quota_additional'] ?? false;
        $quota_start_date = $_POST['quota_start_date'] ?? false;
        $quota_renew_interval = $_POST['quota_renew_interval'] ?? false;
        $quota_auto_renew = $_POST['quota_auto_renew'] ?? false;
        $quota_report_unused = $_POST['quota_report_unused'] ?? false;
        $quota_report_unused_additional = $_POST['quota_report_unused_additional'] ?? false;

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

        //Forge quota for user if needed
        $quota = null;
        if ($quota_enable)
        {
            $quota = [];
            $quota['credit'] = (int) $quota_credit;
            $quota['additional'] = (int) $quota_additional;

            if (false === $quota_start_date || !\controllers\internals\Tool::validate_date($quota_start_date, 'Y-m-d H:i:s'))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez définir une date de début valide pour le quota.');

                return $this->redirect(\descartes\Router::url('User', 'add'));
            }
            $quota['start_date'] = new \DateTime($quota_start_date);

            if (false === $quota_renew_interval || !\controllers\internals\Tool::validate_period($quota_renew_interval))
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez définir une durée de quota parmis la liste proposée.');

                return $this->redirect(\descartes\Router::url('User', 'add'));
            }
            $quota['renew_interval'] = $quota_renew_interval;

            $quota['expiration_date'] = clone $quota['start_date'];
            $quota['expiration_date']->add(new \DateInterval($quota_renew_interval));

            $quota['auto_renew'] = (bool) $quota_auto_renew;
            $quota['report_unused'] = (bool) $quota_report_unused;
            $quota['report_unused_additional'] = (bool) $quota_report_unused_additional;
        }

        $id_user = $this->internal_user->create($email, $password, $admin, null, \models\User::STATUS_ACTIVE, true, $quota);
        if (!$id_user)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible de créer cet utilisateur.');

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

    /**
     * Return the edition page for the users.
     *
     * @param int... $ids : users ids
     */
    public function edit()
    {
        $ids = $_GET['user_ids'] ?? [];
        $id_user = $_SESSION['user']['id'];

        $users = $this->internal_user->gets_in_by_id($ids);

        if (!$users)
        {
            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        foreach ($users as &$user)
        {
            $user['quota'] = $this->internal_quota->get_user_quota($user['id']);
        }

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:00');

        $this->render('user/edit', [
            'users' => $users,
            'now' => $now,
        ]);
    }

    /**
     * Update a list of users.
     *
     * @param $csrf : Le jeton CSRF
     * @param array $_POST['users'] : Array of the users and new values, id as key. Quota may also be defined.
     */
    public function update($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('User', 'add'));
        }

        $nb_update = 0;
        $users = $_POST['users'] ?? [];
        foreach ($users as $id_user => $user)
        {
            $email = $user['email'] ?? false;
            $password = !empty($user['password']) ? $user['password'] : null;
            $admin = $user['admin'] ?? false;

            $quota_enable = $user['quota_enable'] ?? false;
            $quota_consumed = $user['quota_consumed'] ?? false;
            $quota_credit = $user['quota_credit'] ?? false;
            $quota_additional = $user['quota_additional'] ?? false;
            $quota_start_date = $user['quota_start_date'] ?? false;
            $quota_renew_interval = $user['quota_renew_interval'] ?? false;
            $quota_auto_renew = $user['quota_auto_renew'] ?? false;
            $quota_report_unused = $user['quota_report_unused'] ?? false;
            $quota_report_unused_additional = $user['quota_report_unused_additional'] ?? false;

            if (!$email)
            {
                \FlashMessage\FlashMessage::push('danger', 'L\'utilisateur #' . (int) $id_user . ' n\'as pas pu être mis à jour car l\'adresse e-mail n\'as pas été fournie.');

                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                \FlashMessage\FlashMessage::push('danger', 'L\'utilisateur #' . (int) $id_user . ' n\'as pas pu être mis à jour car l\'adresse e-mail fournie n\'est pas valide.');

                return $this->redirect(\descartes\Router::url('User', 'add'));
            }

            //Forge quota for user if needed
            $quota = false;
            if ($quota_enable)
            {
                $quota = [];
                $quota['credit'] = (int) $quota_credit;
                $quota['consumed'] = (int) $quota_consumed;
                $quota['additional'] = (int) $quota_additional;

                if (false === $quota_start_date || !\controllers\internals\Tool::validate_date($quota_start_date, 'Y-m-d H:i:s'))
                {
                    \FlashMessage\FlashMessage::push('danger', 'L\'utilisateur #' . (int) $id_user . ' n\'as pas pu être mis à jour car la date de début du quota associé n\'est pas valide.');

                    continue;
                }
                $quota['start_date'] = new \DateTime($quota_start_date);

                if (false === $quota_renew_interval || !\controllers\internals\Tool::validate_period($quota_renew_interval))
                {
                    \FlashMessage\FlashMessage::push('danger', 'L\'utilisateur #' . (int) $id_user . ' n\'as pas pu être mis à jour car la durée du quota associé n\'est pas valide.');

                    continue;
                }
                $quota['renew_interval'] = $quota_renew_interval;

                $quota['expiration_date'] = clone $quota['start_date'];
                $quota['expiration_date']->add(new \DateInterval($quota_renew_interval));

                $quota['auto_renew'] = (bool) $quota_auto_renew;
                $quota['report_unused'] = (bool) $quota_report_unused;
                $quota['report_unused_additional'] = (bool) $quota_report_unused_additional;

                //Format dates
                $quota['start_date'] = $quota['start_date']->format('Y-m-d H:i:s');
                $quota['expiration_date'] = $quota['expiration_date']->format('Y-m-d H:i:s');
            }

            $updated_user = [
                'email' => $email,
                'admin' => $admin,
            ];

            if ($password)
            {
                $updated_user['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $success = $this->internal_user->update($id_user, $updated_user, $quota);
            if (!$success)
            {
                \FlashMessage\FlashMessage::push('danger', 'L\'utilisateur #' . (int) $id_user . ' n\'as pas pu être mis à jour.');

                continue;
            }

            ++$nb_update;
        }

        if ($nb_update != count($users))
        {
            \FlashMessage\FlashMessage::push('danger', 'Certains utilisateurs n\'ont pas pu être mis à jour.');

            return $this->redirect(\descartes\Router::url('User', 'list'));
        }

        \FlashMessage\FlashMessage::push('success', 'Tous les utilisateurs ont bien été mis à jour.');

        return $this->redirect(\descartes\Router::url('User', 'list'));
    }
}
