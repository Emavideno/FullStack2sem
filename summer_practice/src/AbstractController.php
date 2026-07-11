<?php

namespace App;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View not found: {$view}";
        }
    }

    protected function getParam(ServerRequest $request, string $key, $default = null)
    {
        $routeParams = $request->getAttribute('routeParams', []);
        if (isset($routeParams[$key])) {
            return $routeParams[$key];
        }

        $queryParams = $request->getQueryParams();
        if (isset($queryParams[$key])) {
            return $queryParams[$key];
        }

        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody) && isset($parsedBody[$key])) {
            return $parsedBody[$key];
        }

        return $default;
    }

    protected function sendError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo $message;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function renderPartial(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
        return ob_get_clean();
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function getJsonData(ServerRequest $request): ?array
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $request->getBody()->rewind();
        return $data;
    }

    protected function getPostData(ServerRequestInterface $request): array
    {
        $parsedBody = $request->getParsedBody();
        return is_array($parsedBody) ? $parsedBody : [];
    }
}
