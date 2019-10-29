<?php
namespace controllers\publics;
	/**
	 * Page des settings
	 */
	class Setting extends \Controller
    {
        private $internal_setting;

        public function __construct ()
        {
            $bdd = Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_setting = new \controllers\internals\Setting($bdd);


            \controllers\internals\Tool::verify_connect();
        }

        /**
         * Return all settings to administrate them
		 */	
        public function show ()
        {
            return $this->render('setting/show');
        }    
		
        /**
         * Update a setting value identified by his name
         * @param string $setting_name : Name of the setting to modify
         * @param $csrf : CSRF token
         * @param string $_POST['setting_value'] : Setting's new value
         * @return boolean;
         */
        public function update (string $setting_name, string $csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . \Router::url('Setting', 'show'));
            }

            if (!\controllers\internals\Tool::is_admin())
            {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être administrateur pour pouvoir modifier un réglage.');
                return header('Location: ' . \Router::url('Setting', 'show'));
            }

            $setting_value = $_POST['setting_value'] ?? false;

            if ($setting_value === false)
            {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez renseigner une valeure pour le réglage.');
                return header('Location: ' . \Router::url('Setting', 'show'));
            }

            $update_setting_result = $this->internal_setting->update($setting_name, $setting_value);
            if ($update_setting_result === false)
            {
                \DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de mettre à jour ce réglage.');
                return header('Location: ' . \Router::url('Setting', 'show'));
            }

            \DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'Le réglage a bien été mis à jour.');
            return header('Location: ' . \Router::url('Setting', 'show'));
        }

	}
