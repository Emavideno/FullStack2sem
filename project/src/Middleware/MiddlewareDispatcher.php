<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class MiddlewareDispatcher implements RequestHandlerInterface
{
    private array $middlewares = [];
    private int $index = 0;
    private RequestHandlerInterface $fallbackHandler;

    public function __construct(array $middlewares, RequestHandlerInterface $fallbackHandler)
    {
        $this->middlewares = $middlewares;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->index >= count($this->middlewares)) {
            return $this->fallbackHandler->handle($request);
        }

        $middleware = $this->middlewares[$this->index];
        $this->index++;

        return $middleware->process($request, $this);
    }
}
