<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../bootstrap.php';

\App\Database\Database::enableTestMode();

try {
    $db = \App\Database\Database::getConnection();
    
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', '{$hashedPassword}', 'user')");
    $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (2, 'admin', '{$hashedPassword}', 'admin')");
    
    $stmt = $db->query("SELECT COUNT(*) FROM countries");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $db->exec("
            INSERT INTO countries (id, name, capital, region, population, area, flag_url, created_at, updated_at) 
            VALUES 
                (1, 'Тестовая страна 1', 'Тестовая столица 1', 'Europe', 10000000, 500000, 'https://example.com/flag1.png', datetime('now'), datetime('now')),
                (2, 'Тестовая страна 2', 'Тестовая столица 2', 'Asia', 20000000, 800000, 'https://example.com/flag2.png', datetime('now'), datetime('now')),
                (3, 'Тестовая страна 3', 'Тестовая столица 3', 'Africa', 15000000, 600000, 'https://example.com/flag3.png', datetime('now'), datetime('now')),
                (4, 'Тестовая страна 4', 'Тестовая столица 4', 'Americas', 25000000, 700000, 'https://example.com/flag4.png', datetime('now'), datetime('now'))
        ");
    }
    
    $generator = new \App\Services\QuestionGeneratorService();
    $result = $generator->generateAllQuestions();
    
    error_log('Test bootstrap: Generated ' . $result['total'] . ' questions for ' . $result['countries'] . ' countries');
    
} catch (\Exception $e) {
    error_log('Test bootstrap error: ' . $e->getMessage());
    throw $e;
}
