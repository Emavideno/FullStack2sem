<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$home = function(): void {
    echo "Зашли на home страницу";
};

$about = function(): void {
    echo "Зашли на about страницу";
};

$acc = function(): void {
    echo "Зашли на acc страницу";
};

$notFound = function(): void {
    http_response_code(404);
    echo "404 - Страница не найдена";
};

$obrabotchik = [
    '/home' => $home,
    '/about' => $about,
    '/acc' => $acc,
];

if (isset($obrabotchik[$path])) {
    $obrabotchik[$path]();
} else {
    $notFound();
}