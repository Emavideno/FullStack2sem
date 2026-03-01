<?php

echo "<h1>Добрый день, ";

$role = $_GET['role'] ?? ' ';
if ($role == 'admin') {
    $safeRole = htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "$safeRole ";
}

$name = $_GET['name'] ?? 'Гость';
$safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
echo "$safeName</h1>";

echo "<h2> Был использован метод: " . $_SERVER["REQUEST_METHOD"] . " </h2>";
