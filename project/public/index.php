<?php

$config = require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\MiddlewareDispatcher;
use App\Middleware\RequestHandler;
use App\Middleware\RequestLoggerMiddleware;

AuthMiddleware::check();

$router = new Router();

$requestHandler = new RequestHandler(function ($request) use ($router) {
    return $router->dispatch();
});

$middlewares = [];

if ($config['debug']) {
    $middlewares[] = new RequestLoggerMiddleware(null, true);
}

$dispatcher = new MiddlewareDispatcher($middlewares, $requestHandler);

$tempRouter = new Router();
$request = $tempRouter->createRequest([]);
$response = $dispatcher->handle($request);

if ($response && $response->getStatusCode() !== 200) {
    http_response_code($response->getStatusCode());
    echo $response->getBody();
}
