<?php

$dbUrl = parse_url(getenv('DATABASE_URL'));

return [
    'db' => [
        'driver' => 'pgsql',
        'host' => $dbUrl['host'],
        'database' => ltrim($dbUrl['path'], '/'),
        'username' => $dbUrl['user'],
        'password' => $dbUrl['pass'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];

