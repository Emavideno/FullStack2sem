<?php
namespace App;

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

    protected function getParam(array $request, string $key, $default = null)
    {
        if (isset($request['routeParams'][$key])) {
            return $request['routeParams'][$key];
        }
        if (isset($request['get'][$key])) {
            return $request['get'][$key];
        }
        if (isset($request['post'][$key])) {
            return $request['post'][$key];
        }
        return $default;
    }

    protected function sendError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo $message;
    }
}