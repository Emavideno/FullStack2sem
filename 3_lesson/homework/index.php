<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes_get = [
    '/' => function() {
        echo '<h1><a href="/home">home</a></h1>';
        echo '<h1><a href="/about">about</a></h1>';
        echo '<h1><a href="/acc">acc</a></h1>';
        echo '<h1><a href="/form">form</a></h1>';
    },
    '/home' => function() {
        echo "<h1>Вы на /home</h1>";
    },
    '/about' => function() {
        echo "<h1>Вы на /about</h1>";
    },
    '/acc' => function() {
        echo "<h1>Вы на /acc</h1>";
    },
    '/form' => function() {
        echo '
        <h1>Форма входа</h1>
        <form method="POST" action="/form">
            <input name="login" placeholder="Логин">
            <br><br>
            <input type="password" name="pass" placeholder="Пароль">
            <br><br>
            <input type="submit" value="Войти">
        </form>
        ';
    }
];

$routes_post = [
    '/form' => function() {
        $login = $_POST['login'] ?? '';
        $pass = $_POST['pass'] ?? '';
        
        echo "<h1>Успешно</h1>";
        echo "<p>Логин: " . htmlspecialchars($login) . "</p>";
        echo "<p>Пароль: " . htmlspecialchars($pass) . "</p>";
    }
];

$routes = ($method === 'POST') ? $routes_post : $routes_get;

if (isset($routes[$path])) {
    $routes[$path]();
} else {
    http_response_code(404);
    echo "<h1>404 - Страница не найдена</h1>";
}

?>