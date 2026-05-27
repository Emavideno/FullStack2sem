<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Router;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$router = new Router();
$router->dispatch();
