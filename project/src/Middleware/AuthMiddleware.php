<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function check(): void
    {
        $publicPaths = ['/login', '/register'];
        $currentPath = $_SERVER['REQUEST_URI'];
        $currentPath = strtok($currentPath, '?');

        if (!in_array($currentPath, $publicPaths) && !isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
}
