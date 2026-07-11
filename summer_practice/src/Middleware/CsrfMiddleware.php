<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    private array $excludedPaths = ['/api/answer', '/api/question'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $path = $request->getUri()->getPath();

            foreach ($this->excludedPaths as $excluded) {
                if (strpos($path, $excluded) === 0) {
                    return $handler->handle($request);
                }
            }

            $sessionToken = $_SESSION['csrf_token'] ?? null;
            $postData = $request->getParsedBody();
            $requestToken = $postData['csrf_token'] ?? null;

            if (!$sessionToken || !$requestToken || $sessionToken !== $requestToken) {
                $response = new \Nyholm\Psr7\Response(403, ['Content-Type' => 'text/html']);
                $response->getBody()->write('<h1>Ошибка CSRF</h1><p>Неверный токен безопасности.</p>');
                return $response;
            }
        }

        return $handler->handle($request);
    }

    public static function getTokenField(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}
