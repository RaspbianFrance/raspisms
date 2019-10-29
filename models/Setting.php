<?php
	namespace models;
	/**
     * Cette classe gère les accès bdd pour les settinges
	 */
	class Setting extends \Model
    {
		/**
         * Return array of all settings
		 */
		public function all () : array
        {
            return $this->select('setting', [], '', false, $limit, $offset);
		}
        
        /**
         * Update a setting by his name
         * @return int : number of modified lines
         */
        public function update (string $name, $value) : int
        {
            return $this->update('setting', ['value' => $value], ['name' => $name]);
        }

    }
