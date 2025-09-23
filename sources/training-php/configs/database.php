<?php
define('DB_HOST', 'web-mysql');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', 3306);
define('DB_NAME', 'app_web1');

$redis = [
    'client' => 'phpredis',
    'default' => [
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => 0,
    ],
];