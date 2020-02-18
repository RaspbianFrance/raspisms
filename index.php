<?php
    require_once(__DIR__ . '/descartes/load.php');

    ############
    # SESSIONS #
    ############
    session_name(SESSION_NAME);
    session_start();

    //Create csrf token if it didn't exist
    if (!isset($_SESSION['csrf']))
    {
        $_SESSION['csrf'] = str_shuffle(uniqid().uniqid());
    }

    //Routing current query
    descartes\Router::route(ROUTES, $_SERVER['REQUEST_URI']);
