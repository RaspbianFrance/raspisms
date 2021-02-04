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
        $this->internal_adapter = new \controllers\internals\Adapter();

        \controllers\internals\Tool::verifyconnect();
    }

    /**
     * Cette fonction retourne tous les phones, sous forme d'un tableau permettant l'administration de ces phones.
     */
    public function list()
    {
        $id_user = $_SESSION['user']['id'];
        $api_key = $_SESSION['user']['api_key'];
        $phones = $this->internal_phone->list_for_user($id_user);

        $adapters = [];
        $adapters = $this->internal_adapter->list_adapters();
        foreach ($adapters as $key => $adapter)
        {
            unset($adapters[$key]);
            $adapters[$adapter['meta_classname']] = $adapter;
        }

        foreach ($phones as &$phone)
        {
            $adapter = $adapters[$phone['adapter']] ?? false;

            if (!$adapter)
            {
                $phone['adapter'] = 'Inconnu';

                continue;
            }

            $phone['adapter'] = $adapter['meta_name'];

            if ($adapter['meta_support_reception'])
            {
                $phone['callback_reception'] = \descartes\Router::url('Callback', 'reception', ['adapter_uid' => $adapter['meta_uid'], 'id_phone' => $phone['id']], ['api_key' => $api_key]);
            }

            if ($adapter['meta_support_status_change'])
            {
                $phone['callback_status'] = \descartes\Router::url('Callback', 'update_sended_status', ['adapter_uid' => $adapter['meta_uid']], ['api_key' => $api_key]);
            }
        }

        $this->render('phone/list', ['phones' => $phones]);
    }

    /**
     * Return phones as json with additionnals data about callbacks.
     */
    public function list_json()
    {
        $id_user = $_SESSION['user']['id'];
        $api_key = $_SESSION['user']['api_key'];
        $phones = $this->internal_phone->list_for_user($id_user);

        $adapters = [];
        $adapters = $this->internal_adapter->list_adapters();
        foreach ($adapters as $key => $adapter)
        {
            unset($adapters[$key]);
            $adapters[$adapter['meta_classname']] = $adapter;
        }

        foreach ($phones as &$phone)
        {
            $adapter = $adapters[$phone['adapter']] ?? false;

            if (!$adapter)
            {
                $phone['adapter'] = 'Inconnu';

                continue;
            }

            $phone['adapter'] = $adapter['meta_name'];

            if ($adapter['meta_support_reception'])
            {
                $phone['callback_reception'] = \descartes\Router::url('Callback', 'reception', ['adapter_uid' => $adapter['meta_uid'], 'id_phone' => $phone['id']], ['api_key' => $api_key]);
            }

            if ($adapter['meta_support_status_change'])
            {
                $phone['callback_status'] = \descartes\Router::url('Callback', 'update_sended_status', ['adapter_uid' => $adapter['meta_uid']], ['api_key' => $api_key]);
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $phones]);
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
     * Create a new phone.
     *
     * @param $csrf : CSRF token
     * @param string $_POST['name']          : Phone name
     * @param string $_POST['adapter']       : Phone adapter
     * @param array  $_POST['adapter_data'] : Phone adapter data
     */
    public function create($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $id_user = $_SESSION['user']['id'];
        $name = $_POST['name'] ?? false;
        $adapter = $_POST['adapter'] ?? false;
        $adapter_data = !empty($_POST['adapter_data']) ? $_POST['adapter_data'] : [];

        if (!$name || !$adapter)
        {
            \FlashMessage\FlashMessage::push('danger', 'Des champs obligatoires sont manquants.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $name_exist = $this->internal_phone->get_by_name($name);
        if ($name_exist)
        {
            \FlashMessage\FlashMessage::push('danger', 'Ce nom est déjà utilisé pour un autre téléphone.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $adapters = $this->internal_adapter->list_adapters();
        $find_adapter = false;
        foreach ($adapters as $metas)
        {
            if ($metas['meta_classname'] === $adapter)
            {
                $find_adapter = $metas;

                break;
            }
        }

        if (!$find_adapter)
        {
            \FlashMessage\FlashMessage::push('danger', 'Ce type de téléphone n\'existe pas.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        if ($find_adapter['meta_hidden'])
        {
            \FlashMessage\FlashMessage::push('danger', 'Ce type de téléphone ne peux pas être créé via l\'interface graphique.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        //If missing required data fields, error
        foreach ($find_adapter['meta_data_fields'] as $field)
        {
            if (false === $field['required'])
            {
                continue;
            }

            if (!empty($adapter_data[$field['name']]))
            {
                continue;
            }

            \FlashMessage\FlashMessage::push('danger', 'Vous n\'avez pas rempli certains champs obligatoires pour ce type de téléphone.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        //If field phone number is invalid
        foreach ($find_adapter['meta_data_fields'] as $field)
        {
            if (false === ($field['number'] ?? false))
            {
                continue;
            }

            if (!empty($adapter_data[$field['name']]))
            {
                $adapter_data[$field['name']] = \controllers\internals\Tool::parse_phone($adapter_data[$field['name']]);

                if ($adapter_data[$field['name']])
                {
                    continue;
                }
            }

            \FlashMessage\FlashMessage::push('danger', 'Vous avez fourni un numéro de téléphone avec un format invalide.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $adapter_data = json_encode($adapter_data);

        //Check adapter is working correctly with thoses names and data
        $adapter_classname = $find_adapter['meta_classname'];
        $adapter_instance = new $adapter_classname($adapter_data);
        $adapter_working = $adapter_instance->test();

        if (!$adapter_working)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible d\'utiliser ce type de téléphone avec les données fournies. Vérifiez les réglages.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $success = $this->internal_phone->create($id_user, $name, $adapter, $adapter_data);
        if (!$success)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce téléphone.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        \FlashMessage\FlashMessage::push('success', 'Le téléphone a bien été créé.');

        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }
}
