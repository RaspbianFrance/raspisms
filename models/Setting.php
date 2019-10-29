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

namespace models;

    /**
     * Cette classe gère les accès bdd pour les settinges.
     */
    class Setting extends \descartes\Model
    {
        /**
         * Return array of all settings.
         */
        public function all(): array
        {
            return $this->_select('setting', [], '', false);
        }

        /**
         * Update a setting by his name.
         *
         * @param mixed $value
         *
         * @return int : number of modified lines
         */
        public function update(string $name, $value): int
        {
            return $this->_update('setting', ['value' => $value], ['name' => $name]);
        }
    }
