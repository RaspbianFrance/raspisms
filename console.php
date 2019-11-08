#!/usr/bin/php
<?php
    require_once(__DIR__ . '/descartes/load.php');

    #####################
    # RASPISMS SETTINGS #
    #####################
    $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    $internal_setting = new \controllers\internals\Setting($bdd);
    
    $settings = $internal_setting->get_all();
	foreach ($settings as $setting)
	{
		define('RASPISMS_SETTINGS_' . mb_convert_case($setting['name'],  MB_CASE_UPPER), $setting['value']);
	}
    

    //Execute command
    descartes\Console::execute_command($argv);
