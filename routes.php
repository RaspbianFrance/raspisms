<?php
	$routes = array(
		'Connect' => [
            'login' => '/',
            'connection' => '/connection/',
            'forget_password' => '/forget_password/',
            'send_reset_password' => '/send_reset_password/{csrf}/',
            'reset_password' => '/reset_password/{user_id}/{token}/',
            'logout' => '/logout/',
        ],

        'Dashboard' => [
            'show' => '/dashboard/',
        ],

        'Account' => [
            'show' => '/account/',
            'update_password' => '/account/update_password/{csrf}/',
            'update_transfer' => '/account/update_transfer/{csrf}/',
            'update_email' => '/account/update_email/{csrf}/',
            'delete' => '/account/delete/{csrf}/',
            'logout' => '/logout/',
        ],

        'Command' => [
            'list' => [
                '/command/',
                '/command/p/{page}/',
            ],
            'add' => '/command/add/',
            'create' => '/command/create/{csrf}/',
            'delete' => '/command/delete/{csrf}/',
            'edit' => '/command/edit/',
            'update' => '/command/update/{csrf}/',
        ],

        'Contact' => [
            'list' => [
                '/contact/',
                '/contact/p/{page}/',
            ],
            'add' => '/contact/add/',
            'create' => '/contact/create/{csrf}/',
            'delete' => '/contact/delete/{csrf}/',
            'edit' => '/contact/edit/',
            'update' => '/contact/update/{csrf}/',
            'json_list' => '/contacts.json/',
        ],

        'Discussion' => [
            'list' => [
                '/discussion/',
                '/discussion/p/{page}/',
            ],
            'show' => '/discussion/show/{number}/',
            'send' => '/discussion/send/{csrf}/',
            'get_messages' => '/discussion/getmessage/{number}/{transaction_id}/',
        ],

        'Event' => [
            'list' => [
                '/event/',
                '/event/p/{page}/',
            ],
            'delete' => '/event/delete/{csrf}/',
        ],

        'Group' => [
            'list' => [
                '/group/',
                '/group/p/{page}/',
            ],
            'add' => '/group/add/',
            'create' => '/group/create/{csrf}/',
            'delete' => '/group/delete/{csrf}/',
            'edit' => '/group/edit/',
            'update' => '/group/update/{csrf}/',
            'json_list' => '/groups.json/',
        ],
        
        'ConditionalGroup' => [
            'list' => [
                '/conditional_group/',
                '/conditional_group/p/{page}/',
            ],
            'add' => '/conditional_group/add/',
            'create' => '/conditional_group/create/{csrf}/',
            'delete' => '/conditional_group/delete/{csrf}/',
            'edit' => '/conditional_group/edit/',
            'update' => '/conditional_group/update/{csrf}/',
            'contacts_preview' => '/conditional_group/preview/',
            'json_list' => '/conditional_groups.json/',
        ],

        'Received' => [
            'list' => [
                '/received/',
                '/received/p/{page}/',
            ],
            'delete' => '/received/delete/{csrf}/',
            'popup' => '/received/popup/',
        ],

        'Scheduled' => [
            'list' => [
                '/scheduled/',
                '/scheduled/p/{page}/',
            ],
            'add' => [
                '/scheduled/add/',
                '/scheduled/add/{prefilled}/',
            ],
            'create' => '/scheduled/create/{csrf}/',
            'edit' => '/scheduled/edit/',
            'update' => '/scheduled/update/{csrf}/',
            'delete' => '/scheduled/delete/{csrf}/',
        ],

        'Sended' => [
            'list' => [
                '/sended/',
                '/sended/p/{page}/',
            ],
            'delete' => '/sended/delete/{csrf}/',
        ],

        'Setting' => [
            'show' => '/setting/',
            'update' => '/setting/update/{setting_name}/{csrf}/',
        ],

        'SmsStop' => [
            'list' => [
                '/smsstop/',
                '/smsstop/p/{page}/',
            ],
            'delete' => '/smsstop/delete/{csrf}/',
        ],

        'Templating' => [
            'render_preview' => '/template/preview/',
        ],

        'User' => [
            'list' => [
                '/user/',
                '/user/p/{page}/',
            ],
            'add' => '/user/add/',
            'create' => '/user/create/{csrf}/',
            'delete' => '/user/delete/{csrf}/',
        ],

        'Phone' => [
            'list' => [
                '/phone/',
                '/phone/p/{page}/',
            ],
            'add' => '/phone/add/',
            'create' => '/phone/create/{csrf}/',
            'delete' => '/phone/delete/{csrf}/',
        ],
    );

    define('ROUTES', $routes);
    unset($routes);
