<?php

$profile = [];

$name = $_GET['name'] ?? 'Гость';
$profile['name'] = $name;
$safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$role = $_GET['role'] ?? 'пользователь';
$profile['role'] = $role;
$safeRole = htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
echo "<h1> $profile[name] $profile[role]";

$skills = $_GET['skills'] ?? [];

echo "<ul>";
foreach ($skills as $skill) {
    $profile['skills'][] = $skill;
    $safeSkill = htmlspecialchars($skill, ENT_QUOTES | ENT_SUBSTITUTE, '');
    echo "<li> $safeSkill </li>";
}
echo "</ul>";