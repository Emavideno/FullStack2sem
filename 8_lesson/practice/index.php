<?php


// $isNewDb = !file_exists($dbPath);

// $pdo = new PDO("sqlite:{$dbPath}");
// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


class Database {
    private string $dbPath;
    private PDO $pdo;

    public function __construct(string $dbPath) {
        $this->dbPath = $dbPath;
        try {
            $this->pdo = new PDO("sqlite:{$dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Ошибка подключения: " . $e->getMessage());
        }
    }

    public function getConnection() : PDO {
        return $this->pdo;
    }

    public function startConnection() {
        $isNewDb = !file_exists($this->dbPath);

        if ($isNewDb) {
            $this->pdo->exec("
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    age INT
                )
            ");
        }
    }
}


$dbPath = __DIR__ . '/../database/app.db';
$database = new Database($dbPath);
$database->startConnection();

print_r($database->getConnection());