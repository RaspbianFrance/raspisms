#!/usr/bin/php
<?php
    require_once(__DIR__ . '/descartes/load.php');

    #####################
    # RASPISMS SETTINGS #
    #####################
    $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    $internal_setting = new \controllers\internals\Setting($bdd);
    
    //Execute command
    descartes\Console::execute_command($argv);
