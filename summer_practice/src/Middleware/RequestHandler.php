<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

class RequestHandler implements RequestHandlerInterface
{
    private $router;

    public function __construct(callable $router)
    {
        $this->router = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = ($this->router)($request);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return new Response(200);
    }
}
