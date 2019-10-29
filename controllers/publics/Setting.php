<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
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
         * @param string $_POST['setting_value'] : Setting's new value
         *
         * @return boolean;
         */
        public function update(string $setting_name, string $csrf)
        {
            if (!$this->verify_csrf($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être administrateur pour pouvoir modifier un réglage.');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            $setting_value = $_POST['setting_value'] ?? false;

            if (false === $setting_value)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez renseigner une valeure pour le réglage.');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            $update_setting_result = $this->internal_setting->update($setting_name, $setting_value);
            if (false === $update_setting_result)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de mettre à jour ce réglage.');

                return $this->redirect(\descartes\Router::url('Setting', 'show'));
            }

            \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le réglage a bien été mis à jour.');

            return $this->redirect(\descartes\Router::url('Setting', 'show'));
        }
    }
