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
     * Page des settings.
     */
    class Setting extends \descartes\Controller
    {
        private $internal_setting;

        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_setting = new \controllers\internals\Setting($bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Return all settings to administrate them.
         */
        public function show()
        {
            return $this->render('setting/show');
        }

        /**
         * Update a setting value identified by his name.
         *
         * @param string $setting_name : Name of the setting to modify
         * @param $csrf : CSRF token
         * @param string $_POST['setting_value']  : Setting's new value
         * @param bool   $_POST['allow_no_value'] : Default false, if true then allow $_POST['setting_value'] to dont exists, and treat it as empty string
         *
         * @return boolean;
         */
        public function update(string $setting_name, string $csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \FlashMessage\FlashMessage::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            $setting_value = $_POST['setting_value'] ?? false;
            $allow_no_value = $_POST['allow_no_value'] ?? false;

            //if no value allowed and no value fund, default to ''
            if ($allow_no_value && (false === $setting_value))
            {
                $setting_value = '';
            }

            if (false === $setting_value)
            {
                \FlashMessage\FlashMessage::push('danger', 'Vous devez renseigner une valeure pour le réglage.');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            //If setting is an array, join with comas
            if (is_array($setting_value))
            {
                $setting_value = json_encode($setting_value);
            }

            $update_setting_result = $this->internal_setting->update_for_user($_SESSION['user']['id'], $setting_name, $setting_value);
            if (false === $update_setting_result)
            {
                \FlashMessage\FlashMessage::push('danger', 'Impossible de mettre à jour ce réglage.');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            $settings = $this->internal_setting->gets_for_user($_SESSION['user']['id']);
            $_SESSION['user']['settings'] = $settings;

            \FlashMessage\FlashMessage::push('success', 'Le réglage a bien été mis à jour.');

            return $this->redirect(\descartes\Router::url('Setting', 'show'));
        }
    }
