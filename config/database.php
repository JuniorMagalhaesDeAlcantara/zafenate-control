<?php

return [
    'driver'   => 'mysql', // Adicionado o driver padrão que seu Core pede
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => env('DB_PORT', '3306'),
    'database' => env('DB_NAME', 'zafenate_control'), // corrigido de dbname para database
    'username' => env('DB_USER', 'root'),             // corrigido de user para username
    'password' => env('DB_PASS', ''),                 // corrigido de pass para password
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];