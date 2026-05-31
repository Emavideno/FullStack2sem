<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$db = Database::getConnection();

$login = 'admin';
$password = 'admin';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

echo "Хеш для пароля 'admin': " . $passwordHash . "<br>";

try {
    $db->exec("DELETE FROM users WHERE login = 'admin'");

    $stmt = $db->prepare("INSERT INTO users (login, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$login, $passwordHash]);
    echo "Администратор создан! Логин: admin, Пароль: admin";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
