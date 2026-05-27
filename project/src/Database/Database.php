<?php
namespace App\Database;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dbPath = __DIR__ . '/../../database/app.db';
            $isNewDb = !file_exists($dbPath);
            
            self::$connection = new PDO("sqlite:{$dbPath}");
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if ($isNewDb) {
                self::createTables();
            }
        }
        return self::$connection;
    }

    private static function createTables(): void
    {
        $pdo = self::$connection;
        
        $pdo->exec("
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            CREATE TABLE news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                title TEXT NOT NULL,
                excerpt TEXT,
                content TEXT,
                views INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ");
        
        $pdo->exec("
            CREATE TABLE comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                news_id INTEGER NOT NULL,
                author TEXT,
                text TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
            )
        ");
        
        $pdo->exec("
            CREATE TABLE likes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                news_id INTEGER NOT NULL,
                ip_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
            )
        ");
        
        // Тестовые данные
        $pdo->exec("
            INSERT INTO categories (name, slug) VALUES
                ('Искусственный интеллект', 'ai'),
                ('Веб-разработка', 'web'),
                ('Кибербезопасность', 'security'),
                ('Железо', 'hardware')
        ");
        
        $pdo->exec("
            INSERT INTO news (category_id, title, excerpt, content, views) VALUES
                (1, 'Новый прорыв в ИИ', 'Краткое описание новости про ИИ...', 'Полный текст новости про ИИ...', 100),
                (2, 'PHP 8.5 вышел', 'Краткое описание про PHP...', 'Полный текст про PHP...', 50),
                (1, 'Нейросети в 2026', 'Краткое описание...', 'Полный текст...', 75)
        ");
        
        $pdo->exec("
            INSERT INTO comments (news_id, author, text) VALUES
                (1, 'Иван', 'Отличная новость!'),
                (1, 'Мария', 'Спасибо, очень интересно'),
                (2, 'Алексей', 'Ждал этот релиз')
        ");
    }
}
