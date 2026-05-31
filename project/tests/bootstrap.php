<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$_ENV['DB_PATH'] = ':memory:';
