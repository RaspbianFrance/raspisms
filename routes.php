<?php
	$descartesRoutes = array(
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

        'Groupe' => [
            'list' => [
                '/groupe/',
                '/groupe/p/{page}/',
            ],
            'add' => '/groupe/add/',
            'create' => '/groupe/create/{csrf}/',
            'delete' => '/groupe/delete/{csrf}/',
            'edit' => '/groupe/edit/',
            'update' => '/groupe/update/{csrf}/',
            'json_list' => '/groupes.json/',
        ],

        'Received' => [
            'list' => [
                '/received/',
                '/received/p/{page}/',
            ],
            'delete' => '/received/delete/{csrf}/',
        ],

        'Scheduled' => [
            'list' => [
                '/scheduled/',
                '/scheduled/p/{page}/',
            ],
            'add' => '/scheduled/add/',
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

        'SMSStop' => [
            'list' => [
                '/smsstop/',
                '/smsstop/p/{page}/',
            ],
            'delete' => '/smsstop/delete/{csrf}/',
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
	);
