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
 * Page des phones.
 */
class Phone extends \descartes\Controller
{
    private $internal_phone;
    private $internal_adapter;

    public function __construct()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $this->internal_phone = new \controllers\internals\Phone($bdd);
        $this->internal_adapter = new \controllers\internals\Adapter($bdd);

        \controllers\internals\Tool::verifyconnect();
    }

    /**
     * Cette fonction retourne tous les phones, sous forme d'un tableau permettant l'administration de ces phones.
     *
     * @param mixed $page
     */
    public function list($page = 0)
    {
        $id_user = $_SESSION['user']['id'];
        $page = (int) $page;
        $phones = $this->internal_phone->list_for_user($id_user, 25, $page);

        $adapters = [];
        $adapters_metas = $this->internal_adapter->list_adapters();
        foreach ($adapters_metas as $adapter_metas)
        {
            $adapters[$adapter_metas['meta_classname']] = $adapter_metas['meta_name'];
        }

        foreach ($phones as &$phone)
        {
            $phone['adapter'] = $adapters[$phone['adapter']] ?? 'Inconnu';
        }

        $this->render('phone/list', ['phones' => $phones]);
    }

    /**
     * Cette fonction va supprimer une liste de phones.
     *
     * @param array int $_GET['ids'] : Les id des phonees à supprimer
     * @param mixed     $csrf
     *
     * @return boolean;
     */
    public function delete($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('Phone', 'list'));
        }

        if (!\controllers\internals\Tool::is_admin())
        {
            \FlashMessage\FlashMessage::push('danger', 'Vous devez être administrateur pour supprimer un utilisateur !');

            return $this->redirect(\descartes\Router::url('Phone', 'list'));
        }

        $ids = $_GET['ids'] ?? [];
        foreach ($ids as $id)
        {
            $this->internal_phone->delete_for_user($_SESSION['user']['id'], $id);
        }

        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }

    /**
     * Cette fonction retourne la page d'ajout d'un phone.
     */
    public function add()
    {
        $adapters = $this->internal_adapter->list_adapters();
        return $this->render('phone/add', ['adapters' => $adapters]);
    }

    /**
     * Create a new phone
     * @param $csrf : CSRF token
     * @param string  $_POST['number'] : Phone number
     * @param string  $_POST['adapter'] : Phone adapter
     * @param string $_POST['adapter_datas'] : Phone adapter datas
     */
    public function create($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $id_user = $_SESSION['user']['id'];
        $number = $_POST['number'] ?? false;
        $adapter = $_POST['adapter'] ?? false;
        $adapter_datas = !empty($_POST['adapter_datas']) ? $_POST['adapter_datas'] : '{}';

        if (!$number || !$adapter)
        {
            \FlashMessage\FlashMessage::push('danger', 'Des champs obligatoires sont manquants.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        
        $number = \controllers\internals\Tool::parse_phone($number);
        if (!$number)
        {
            \FlashMessage\FlashMessage::push('danger', 'Numéro de téléphone incorrect.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $number_exist = $this->internal_phone->get_by_number($number);
        if ($number_exist)
        {
            \FlashMessage\FlashMessage::push('danger', 'Ce numéro de téléphone est déjà utilisé.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }


        $adapters = $this->internal_adapter->list_adapters();
        $adapter_exists = false;
        foreach ($adapters as $metas)
        {
            if ($metas['meta_classname'] === $adapter)
            {
                $adapter_exists = true;
                break;
            }
        }

        if (!$adapter_exists)
        {
            \FlashMessage\FlashMessage::push('danger', 'Cet adaptateur n\'existe pas.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }


        if (NULL === json_decode($adapter_datas))
        {
            \FlashMessage\FlashMessage::push('danger', 'La chaîne de configuration n\'est pas valide.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }


        $success = $this->internal_phone->create($id_user, $number, $adapter, $adapter_datas);
        if (!$success)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce téléphone.');
            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }
        
        \FlashMessage\FlashMessage::push('success', 'Le téléphone a bien été créé.');
        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }
}
