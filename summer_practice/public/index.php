<?php

$config = require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\MiddlewareDispatcher;
use App\Middleware\RequestHandler;
use App\Middleware\RequestLoggerMiddleware;
use App\Middleware\DebugMiddleware;
use App\Middleware\CsrfMiddleware;

// Проверяем авторизацию (кроме публичных путей)
AuthMiddleware::check();

$router = new Router();

$requestHandler = new RequestHandler(function ($request) use ($router) {
    return $router->dispatch();
});

// ===== ИЗМЕНЯЕМ ЭТОТ БЛОК =====
$middlewares = [];

// Логирование запросов (всегда включено)
$middlewares[] = new RequestLoggerMiddleware(null, $config['debug']);

// CSRF-защита
$middlewares[] = new CsrfMiddleware();

// Отладка (включается в конфиге)
if ($config['debug']) {
    $middlewares[] = new DebugMiddleware(true);
}
// ===== КОНЕЦ БЛОКА =====

$dispatcher = new MiddlewareDispatcher($middlewares, $requestHandler);

$tempRouter = new Router();
$request = $tempRouter->createRequest([]);
$response = $dispatcher->handle($request);

if ($response && $response->getStatusCode() !== 200) {
    http_response_code($response->getStatusCode());
    echo $response->getBody();
}
