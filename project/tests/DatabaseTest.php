<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Database\Database;
use PDO;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Database::enableTestMode(':memory:');
    }

    protected function tearDown(): void
    {
        Database::disableTestMode();
    }

    public function testGetConnectionReturnsPdoInstance(): void
    {
        $db = Database::getConnection();
        $this->assertInstanceOf(PDO::class, $db);
    }

    public function testConnectionIsSingleton(): void
    {
        $db1 = Database::getConnection();
        $db2 = Database::getConnection();
        $this->assertSame($db1, $db2);
    }

    public function testResetConnectionCreatesNewConnection(): void
    {
        $db1 = Database::getConnection();
        Database::resetConnection();
        $db2 = Database::getConnection();

        $this->assertNotSame($db1, $db2);
    }

    public function testTablesAreCreatedForNewDatabase(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains('users', $tables);
        $this->assertContains('news', $tables);
        $this->assertContains('categories', $tables);
        $this->assertContains('comments', $tables);
        $this->assertContains('reactions', $tables);
    }

    public function testCategoriesHaveTestData(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM categories");
        $count = $stmt->fetchColumn();

        $this->assertEquals(12, $count);
    }

    public function testNewsHaveTestData(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM news");
        $count = $stmt->fetchColumn();

        $this->assertEquals(3, $count);
    }

    public function testCommentsHaveTestData(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM comments");
        $count = $stmt->fetchColumn();

        $this->assertEquals(3, $count);
    }

    public function testExistingDatabaseDoesNotRecreateTables(): void
    {
        Database::disableTestMode();

        $testDbPath = __DIR__ . '/../database/test_exists.db';
        if (file_exists($testDbPath)) {
            unlink($testDbPath);
        }

        Database::enableTestMode($testDbPath);

        $db1 = Database::getConnection();

        $db1->exec("DROP TABLE categories");

        Database::resetConnection();
        $db2 = Database::getConnection();

        $stmt = $db2->query("SELECT name FROM sqlite_master WHERE type='table' AND name='categories'");
        $exists = $stmt->fetchColumn();

        $this->assertFalse($exists);

        Database::disableTestMode();
        if (file_exists($testDbPath)) {
            unlink($testDbPath);
        }
        Database::enableTestMode(':memory:');
    }
}
