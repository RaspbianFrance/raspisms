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
    private $internal_sended;

    public function __construct()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $this->internal_phone = new \controllers\internals\Phone($bdd);
        $this->internal_sended = new \controllers\internals\Sended($bdd);
        $this->internal_adapter = new \controllers\internals\Adapter();

        \controllers\internals\Tool::verifyconnect();
    }

    /**
     * Cette fonction retourne tous les phones, sous forme d'un tableau permettant l'administration de ces phones.
     */
    public function list()
    {
        $this->render('phone/list');
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
            $limits = $this->internal_phone->get_limits($phone['id']);
            $phone['limits'] = $limits;

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

            if ($adapter['meta_support_inbound_call_callback'])
            {
                $phone['callback_inbound_call'] = \descartes\Router::url('Callback', 'inbound_call', ['id_phone' => $phone['id']], ['api_key' => $api_key]);
            }

            if ($adapter['meta_support_end_call_callback'])
            {
                $phone['callback_end_call'] = \descartes\Router::url('Callback', 'end_call', ['id_phone' => $phone['id']], ['api_key' => $api_key]);
            }

            if ($adapter['meta_support_phone_status'])
            {
                $phone['support_phone_status'] = true;
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
     * @param ?array  $_POST['adapter_data'] : Phone adapter data
     * @param ?array $_POST['limits']        : Array of limits in number of SMS for a period to be applied to this phone.
     * @param int $_POST['priority']         : Priority with which to use phone to send SMS. Default 0.
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
        $priority = $_POST['priority'] ?? 0;
        $priority = max(((int) $priority), 0);
        $adapter = $_POST['adapter'] ?? false;
        $adapter_data = !empty($_POST['adapter_data']) ? $_POST['adapter_data'] : [];
        $limits = $_POST['limits'] ?? [];
        $limits = is_array($limits) ? $limits : [$limits];

        if (!$name || !$adapter)
        {
            \FlashMessage\FlashMessage::push('danger', 'Des champs obligatoires sont manquants.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $name_exist = $this->internal_phone->get_by_name_and_user($id_user, $name);
        if ($name_exist)
        {
            \FlashMessage\FlashMessage::push('danger', 'Ce nom est déjà utilisé pour un autre téléphone.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        if ($limits)
        {
            foreach ($limits as $key => $limit)
            {
                if (!is_array($limit))
                {
                    unset($limits[$key]);
                    continue;
                }

                $startpoint = $limit['startpoint'] ?? false;
                $volume = $limit['volume'] ?? false;

                if (!$startpoint || !$volume)
                {
                    unset($limits[$key]);
                    continue;
                }

                $volume = (int) $volume;
                $limits[$key]['volume'] = max($volume, 1);

                if (!\controllers\internals\Tool::validate_relative_date($startpoint))
                {
                    unset($limits[$key]);
                    continue;
                }
            }
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
            if ('phone_number' !== ($field['type'] ?? false))
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

        $success = $this->internal_phone->create($id_user, $name, $adapter, $adapter_data, $priority, $limits);
        if (!$success)
        {
            \FlashMessage\FlashMessage::push('danger', 'Impossible de créer ce téléphone.');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        \FlashMessage\FlashMessage::push('success', 'Le téléphone a bien été créé.');

        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }


    /**
     * Return the edit page for phones
     *
     * @param int... $ids : Phones ids
     */
    public function edit()
    {
        $ids = $_GET['ids'] ?? [];
        $id_user = $_SESSION['user']['id'];

        $phones = $this->internal_phone->gets_in_for_user($id_user, $ids);

        if (!$phones)
        {
            return $this->redirect(\descartes\Router::url('Phone', 'list'));
        }

        $adapters = $this->internal_adapter->list_adapters();

        foreach ($phones as &$phone)
        {
            $limits = $this->internal_phone->get_limits($phone['id']);
            $phone['limits'] = $limits;
        }

        $this->render('phone/edit', [
            'phones' => $phones,
            'adapters' => $adapters,
        ]);
    }


    /**
     * Update multiple phones.
     *
     * @param $csrf : CSRF token
     * @param string $_POST['phones']['id']['name']          : Phone name
     * @param string $_POST['phones']['id']['adapter']       : Phone adapter
     * @param ?array $_POST['phones']['id']['adapter_data'] : Phone adapter data
     * @param ?array $_POST['phones']['id']['limits']        : Array of limits in number of SMS for a period to be applied to this phone.
     * @param int    $_POST['phones']['id']['priority']         : Priority with which to use phone to send SMS. Default 0.
     */
    public function update($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        if (!$_POST['phones'])
        {
            return $this->redirect(\descartes\Router::url('Phone', 'list'));
        }

        $id_user = $_SESSION['user']['id'];

        $nb_update = 0;
        foreach ($_POST['phones'] as $id_phone => $phone)
        {
            $name = $phone['name'] ?? false;
            $priority = $phone['priority'] ?? 0;
            $priority = max(((int) $priority), 0);
            $adapter = $phone['adapter'] ?? false;
            $adapter_data = !empty($phone['adapter_data']) ? $phone['adapter_data'] : [];
            $limits = $phone['limits'] ?? [];
            $limits = is_array($limits) ? $limits : [$limits];

            if (!$name || !$adapter)
            {
                continue;
            }

            $phone_with_same_name = $this->internal_phone->get_by_name_and_user($id_user, $name);
            if ($phone_with_same_name && $phone_with_same_name['id'] != $id_phone)
            {
                continue;
            }

            if ($limits)
            {
                foreach ($limits as $key => $limit)
                {
                    if (!is_array($limit))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                    
                    $startpoint = $limit['startpoint'] ?? false;
                    $volume = $limit['volume'] ?? false;

                    if (!$startpoint || !$volume)
                    {
                        unset($limits[$key]);
                        continue;
                    }

                    $volume = (int) $volume;
                    $limits[$key]['volume'] = max($volume, 1);

                    if (!\controllers\internals\Tool::validate_relative_date($startpoint))
                    {
                        unset($limits[$key]);
                        continue;
                    }
                }
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
                continue;
            }

            $current_phone = $this->internal_phone->get_for_user($id_user, $id_phone);
            if (!$current_phone)
            {
                continue;
            }

            // We can only use an hidden adapter if it was already the adapter we was using
            if ($find_adapter['meta_hidden'] && $adapter != $current_phone['adapter']) 
            {
                continue;
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

                continue 2;
            }

            //If field phone number is invalid
            foreach ($find_adapter['meta_data_fields'] as $field)
            {
                if ('phone_number' !== ($field['type'] ?? false))
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

                continue 2;
            }

            $adapter_data = json_encode($adapter_data);

            //Check adapter is working correctly with thoses names and data
            $adapter_classname = $find_adapter['meta_classname'];
            $adapter_instance = new $adapter_classname($adapter_data);
            $adapter_working = $adapter_instance->test();

            if (!$adapter_working)
            {
                continue;
            }

            $success = $this->internal_phone->update_for_user($id_user, $id_phone, $name, $adapter, $adapter_data, $priority, $limits);
            if (!$success)
            {
                continue;
            }

            $nb_update ++;
        }

        if ($nb_update !== \count($_POST['phones']))
        {
            \FlashMessage\FlashMessage::push('danger', 'Certains téléphones n\'ont pas pu êtres mis à jour.');

            return $this->redirect(\descartes\Router::url('Phone', 'list'));
        }

        \FlashMessage\FlashMessage::push('success', 'Tous les téléphones ont été modifiés avec succès.');

        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }


    /**
     * Re-check phone status
     * @param array int $_GET['ids'] : ids of phones we want to update status
     * @param $csrf : CSRF token
     */
    public function update_status ($csrf)
    {
        if (!$this->verify_csrf($csrf))
        {
            \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

            return $this->redirect(\descartes\Router::url('Phone', 'add'));
        }

        $ids = $_GET['ids'] ?? [];
        $id_user = $_SESSION['user']['id'];

        foreach ($ids as $id)
        {
            $phone = $this->internal_phone->get_for_user($id_user, $id);

            // If user have activated phone limits, check if RaspiSMS phone limit have already been reached
            $limit_reached = false;
            if ((int) ($_SESSION['user']['settings']['phone_limit'] ?? false))
            {
                $limits = $this->internal_phone->get_limits($id);

                $remaining_volume = PHP_INT_MAX;
                foreach ($limits as $limit)
                {
                    $startpoint = new \DateTime($limit['startpoint']);
                    $consumed = $this->internal_sended->count_since_for_phone_and_user($_SESSION['user']['id'], $id, $startpoint);
                    $remaining_volume = min(($limit['volume'] - $consumed), $remaining_volume);
                }

                if ($remaining_volume < 1)
                {
                    $limit_reached = true;
                }
            }

            if ($limit_reached)
            {
                $new_status = \models\Phone::STATUS_LIMIT_REACHED;
            }
            else
            {
                //Check status on provider side 
                $adapter_classname = $phone['adapter'];
                if (!call_user_func([$adapter_classname, 'meta_support_phone_status']))
                {
                    continue;
                }

                $adapter_instance = new $adapter_classname($phone['adapter_data']);
                $new_status = $adapter_instance->check_phone_status();
            }

            $status_update = $this->internal_phone->update_status($id, $new_status);
        }

        \FlashMessage\FlashMessage::push('success', 'Les status des téléphones ont bien été mis à jour.');
            
        return $this->redirect(\descartes\Router::url('Phone', 'list'));
    }

    /**
     * Return a list of phones as a JSON array
     */
    public function json_list()
    {
        header('Content-Type: application/json');
        echo json_encode($this->internal_phone->list_for_user($_SESSION['user']['id']));
    }
}
