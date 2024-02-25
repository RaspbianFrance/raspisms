<?php
    namespace descartes;

    /*
     * Define Descartes env
     */
    $http_dir_path = ''; //Path we need to put after servername in url to access app
    $https = $_SERVER['HTTPS'] ?? 0;

    // Check for proxy forward
    $forwarded_https = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['HTTP_FORWARDED_PROTO'] ?? NULL) == 'https';
    $forwarded_ssl = ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? NULL) == 'on';
    $proxy = $forwarded_https || $forwarded_ssl;
    
    $http_protocol = 'http://';
    if ($https)
    {
        $http_protocol = 'https://';
    }

    $http_server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

    // Check port to only set it if not default port
    $port = $_SERVER['SERVER_PORT'] ?? '';
    $port = ($port == 80 && !$https) ? '' : $port;
    $port = ($port == 443 && $https) ? '' : $port;
    $port = $proxy ? '' : $port;
    $http_server_port = $port ? ':' . $port : '';
    
    
    $pwd = substr(__DIR__, 0, strrpos(__DIR__, '/'));
    

    $env = [
        //Global http and file path
        'HTTP_DIR_PATH' => $http_dir_path,
        'HTTP_PROTOCOL' => $http_protocol,
        'HTTP_SERVER_NAME' => $http_server_name,
        'HTTP_SERVER_PORT' => $http_server_port,
        'PWD' => $pwd,
        
        //path of back resources
        'PWD_CONTROLLER' =>  $pwd . '/controllers', //Controllers dir
        'PWD_MODEL' => $pwd . '/models', //Models dir
        'PWD_TEMPLATES' => $pwd . '/templates', //Templates dir

        //path of front resources
        'PWD_ASSETS' => $pwd . '/assets', //Assets dir 

        //images
        'PWD_IMG' => $pwd . '/assets' . '/img',
        
        //css
        'PWD_CSS' => $pwd . '/assets' . '/css', 
        
        //javascript
        'PWD_JS' => $pwd . '/assets' . '/js', 
        
        //fonts
        'PWD_FONT' => $pwd . '/assets' . '/font', 
    ];

