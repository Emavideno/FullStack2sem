<?php

namespace App\Database;

use PDO;
use App\Logger\LoggerFactory;

class Database
{
    private static ?PDO $connection = null;
    private static bool $isTestMode = false;
    private static ?string $testDbPath = null;

    public static function enableTestMode(?string $dbPath = null): void
    {
        self::$isTestMode = true;
        self::$testDbPath = $dbPath ?? ':memory:';
        self::resetConnection();
    }

    public static function disableTestMode(): void
    {
        self::$isTestMode = false;
        self::$testDbPath = null;
        self::resetConnection();
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                if (self::$isTestMode) {
                    $dbPath = self::$testDbPath;
                } else {
                    $config = require __DIR__ . '/../../config/config.php';
                    $dbPath = $config['db']['path'] ?? __DIR__ . '/../../database/app.db';
                }

                // Создаём папку, если её нет
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }

                $isNewDb = ($dbPath === ':memory:') ? true : !file_exists($dbPath);

                self::$connection = new PDO("sqlite:{$dbPath}");
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($isNewDb) {
                    self::createTables();
                }
            } catch (\PDOException $e) {
                $logger = LoggerFactory::create();
                $logger->error('Database connection error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }
        return self::$connection;
    }

    public static function resetConnection(): void
    {
        self::$connection = null;
    }

    private static function createTables(): void
    {
        $pdo = self::$connection;

        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                login TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'user',
                is_blocked INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE countries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                capital TEXT NOT NULL,
                region TEXT NOT NULL,
                subregion TEXT,
                population INTEGER,
                area REAL,
                flag_url TEXT NOT NULL,
                lat_lng TEXT,
                timezones TEXT,
                borders TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE quiz_questions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                country_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                question_data TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
            )
        ");

        $pdo->exec("CREATE INDEX idx_questions_type ON quiz_questions(type)");
        $pdo->exec("CREATE INDEX idx_questions_country ON quiz_questions(country_id)");

        $pdo->exec("
            CREATE TABLE quiz_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                question_id INTEGER NOT NULL,
                user_answer TEXT,
                is_correct INTEGER DEFAULT 0,
                answered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
            )
        ");

        $pdo->exec("
            CREATE TABLE user_stats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                question_type TEXT NOT NULL,
                region TEXT NOT NULL,
                total_attempts INTEGER DEFAULT 0,
                correct_attempts INTEGER DEFAULT 0,
                last_played_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(user_id, question_type, region)
            )
        ");

        $pdo->exec("
            CREATE TABLE api_updates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                status TEXT NOT NULL,
                countries_imported INTEGER DEFAULT 0,
                error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO users (login, password, role) VALUES 
                ('admin', '{$hashedPassword}', 'admin')
        ");
    }

    public static function tableExists(string $tableName): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name = ?
        ");
        $stmt->execute([$tableName]);
        return $stmt->fetch() !== false;
    }

    public static function countRows(string $tableName): int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        return (int) $stmt->fetchColumn();
    }

    public static function getLastApiUpdate(): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("
            SELECT * FROM api_updates 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        return $stmt->fetch() ?: null;
    }
}
