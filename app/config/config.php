<?php

return [
    'application_mode' => getenv('APP_MODE'),
    'db' => array (
        'dbname' => getenv('DB_NAME'),
        'host' => getenv('DB_HOST'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASSWORD'),
        'charset' => getenv('DB_CHARSET'),
        'sqlType' => getenv('DB_DRIVER'),
    ),
];