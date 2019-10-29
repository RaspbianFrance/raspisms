<?php
    namespace controllers\internals;

    class Setting extends \InternalController
    {
        private $model_setting;

        public function __construct (\PDO $bdd)
        {
            $this->model_setting = new \models\Setting($bdd);
        }

		/**
         * Return all settings
         * @return array || false
         */	
        public function all ()
		{
            return $this->model_setting->all();
		}

		/**
         * Update a setting by his name
		 */
		public function update (string $name, $value) : boolean
        {
            return (bool) $this->model_setting->update($name, $value);
        }
	}
