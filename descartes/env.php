<?php
    namespace descartes;

    /*
     * Define Descartes env
     */
    $http_dir_path = '/raspisms'; //Path we need to put after servername in url to access app
    $http_protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://';
    $http_server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    $http_server_port = isset($_SERVER['SERVER_PORT']) ? ($_SERVER['SERVER_PORT'] == 80) ? '' : ':' . $_SERVER['SERVER_PORT'] : '';
    $https = $_SERVER['HTTPS'] ?? false;

    if ( !isset($_SERVER['SERVER_PORT']) || ($_SERVER['SERVER_PORT'] == 80 && !$https) || ($_SERVER['SERVER_PORT'] == 443 && $https) )
    {
        $http_server_port = '';
    }
    else
    {
        $http_server_port = ':' . $_SERVER['SERVER_PORT'];
    }


    $pwd = substr(__DIR__, 0, strrpos(__DIR__, '/'));
    $http_pwd = $http_protocol . $http_server_name . $http_server_port . $http_dir_path;


    $env = [
        //Global http and file path
        'HTTP_DIR_PATH' => $http_dir_path,
        'HTTP_PROTOCOL' => $http_protocol,
        'HTTP_SERVER_NAME' => $http_server_name,
        'HTTP_SERVER_PORT' => $http_server_port,
        'PWD' => $pwd,
        'HTTP_PWD' => $http_pwd,

        //path of back resources
        'PWD_CONTROLLER' =>  $pwd . '/controllers', //Controllers dir
        'PWD_MODEL' => $pwd . '/models', //Models dir
        'PWD_TEMPLATES' => $pwd . '/templates', //Templates dir

        //path of front resources
        'PWD_ASSETS' => $pwd . '/assets', //Assets dir
        'HTTP_PWD_ASSETS' => $http_pwd . '/assets', //HTTP path of asset dir

        //images
        'PWD_IMG' => $pwd . '/assets' . '/img',
        'HTTP_PWD_IMG' => $http_pwd . '/assets' . '/img', 

        //css
        'PWD_CSS' => $pwd . '/assets' . '/css', 
        'HTTP_PWD_CSS' => $http_pwd . '/assets' . '/css', 

        //javascript
        'PWD_JS' => $pwd . '/assets' . '/js', 
        'HTTP_PWD_JS' => $http_pwd . '/assets' . '/js', 

        //fonts
        'PWD_FONT' => $pwd . '/assets' . '/font', 
        'HTTP_PWD_FONT' => $http_pwd . '/assets' . '/font', 
    ];

