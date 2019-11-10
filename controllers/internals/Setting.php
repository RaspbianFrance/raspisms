<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    class Setting extends \descartes\InternalController
    {
        private $model_setting;

        public function __construct(\PDO $bdd)
        {
            $this->model_setting = new \models\Setting($bdd);
        }

        /**
         * Return all settings.
         *
         * @return array || false
         */
        public function get_all()
        {
            return $this->model_setting->get_all();
        }

        /**
         * Update a setting by his name.
         *
         * @param mixed $value
         */
        public function update(string $name, $value): bool
        {
            return (bool) $this->model_setting->update($name, $value);
        }
    }
