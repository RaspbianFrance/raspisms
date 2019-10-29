<?php
    ###############
    # ENVIRONMENT #
    ###############
    require_once(__DIR__ . '/load-environment.php');
    
    ############
    # AUTOLOAD #
    ############
    require_once(PWD . '/descartes/autoload.php');
    require_once(PWD . '/vendor/autoload.php');

    ###########
    # ROUTING #
    ###########
    require_once(PWD . '/routes.php'); //Include routes
