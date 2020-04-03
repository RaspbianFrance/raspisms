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
            'update_api_key' => '/account/update_api_key/{csrf}/',
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
            'import' => '/contact/import/{csrf}/',
            'export' => '/contact/export/{format}/',
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
            'list_unread' => [
                '/unread/',
                '/unread/p/{page}/',
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
            'update_status' => '/user/delete/{status}/{csrf}/',
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
        
        'Webhook' => [
            'list' => [
                '/webhook/',
                '/webhook/p/{page}/',
            ],
            'add' => '/webhook/add/',
            'create' => '/webhook/create/{csrf}/',
            'delete' => '/webhook/delete/{csrf}/',
            'edit' => '/webhook/edit/',
            'update' => '/webhook/update/{csrf}/',
        ],

        'Callback' => [
            'update_sended_status' => '/callback/status/{adapter_uid}/',
            'reception' => '/callback/reception/{adapter_uid}/{id_phone}/',
        ],
        
        'Api' => [
            'get_entries' => [
                '/api/list/{entry_type}/',
                '/api/list/{entry_type}/{page}/',
            ],
            'post_scheduled' => [
                '/api/scheduled/',
            ],
            'delete_scheduled' => [
                '/api/scheduled/{id}/',
            ],
        ],
    );

    define('ROUTES', $routes);
    unset($routes);
