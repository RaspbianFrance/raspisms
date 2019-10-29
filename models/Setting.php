<?php
    namespace models;

    /**
     * Cette classe gère les accès bdd pour les settinges
     */
    class Setting extends \descartes\Model
    {
        /**
         * Return array of all settings
         */
        public function all() : array
        {
            return $this->_select('setting', [], '', false);
        }
        
        /**
         * Update a setting by his name
         * @return int : number of modified lines
         */
        public function update(string $name, $value) : int
        {
            return $this->_update('setting', ['value' => $value], ['name' => $name]);
        }
    }
