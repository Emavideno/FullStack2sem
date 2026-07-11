<?php

namespace App\Middleware;

use App\Models\User;

class AuthMiddleware
{
    public static function check(): void
    {
        $publicPaths = ['/login', '/register'];
        $currentPath = $_SERVER['REQUEST_URI'];
        $currentPath = strtok($currentPath, '?');

        // Если пользователь авторизован
        if (isset($_SESSION['user_id'])) {
            // Проверяем, не заблокирован ли пользователь
            $user = User::findById($_SESSION['user_id']);
            if ($user && $user->isBlocked()) {
                // Очищаем сессию
                session_destroy();
                session_start();
                $_SESSION['blocked_message'] = 'Ваша учётная запись заблокирована. Обратитесь к администратору.';
                header('Location: /login');
                exit;
            }
        }

        // Проверка доступа к защищённым страницам
        if (!in_array($currentPath, $publicPaths) && !isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
}
