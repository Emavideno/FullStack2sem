<?php

return [
    'debug' => $_ENV['DEBUG'] ?? false,
    'db' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'sqlite',
        'path' => __DIR__ . '/../' . ($_ENV['DB_PATH'] ?? 'database/app.db'),
    ],
];