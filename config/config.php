<?php

$config = [
    'homepage_route' => 'home',
    'db' => [
        'name'     => 'filer-final', //DB NAME
        'user'     => 'root',        //DB USER
        'password' => '',            //DB USER PASSWORD
        'host'     => '127.0.0.1',
        'port'     => NULL
    ],
    'routes' => [
        'home'    => 'Main:home',
        'register'=> 'Main:register',
        'login'   => 'Main:login',
        'logout'  => 'Main:logout',
        'download'=> 'Main:download',
        'view'    => 'Main:view'
    ]
];
