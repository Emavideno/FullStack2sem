<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();

return [
    'debug' => $_ENV['DEBUG'] ?? false,
];
