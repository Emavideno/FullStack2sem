<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Database\Database;

class UserTest extends TestCase
{
    private static $db;

    public static function setUpBeforeClass(): void
    {
        self::$db = Database::getConnection();
    }

    protected function setUp(): void
    {
        self::$db->exec("DELETE FROM users");
    }

    public function testCreateUser(): void
    {
        $user = new User('testuser', 'password123');
        $result = $user->save();
        $this->assertTrue($result);
        $this->assertNotNull($user->getId());
    }

    public function testFindByLogin(): void
    {
        $user = new User('testuser', 'password123');
        $user->save();

        $found = User::findByLogin('testuser');
        $this->assertNotNull($found);
        $this->assertEquals('testuser', $found->getLogin());
    }

    public function testVerifyPassword(): void
    {
        $user = new User('testuser', 'password123');
        $user->save();

        $found = User::findByLogin('testuser');
        $this->assertTrue($found->verifyPassword('password123'));
        $this->assertFalse($found->verifyPassword('wrong'));
    }

    public function testUserRole(): void
    {
        $user = new User('admin', 'admin', 'admin');
        $user->save();

        $found = User::findByLogin('admin');
        $this->assertEquals('admin', $found->getRole());
        $this->assertTrue($found->isAdmin());
    }

    public function testDuplicateLoginFails(): void
    {
        $user = new User('duplicate', 'pass');
        $first = $user->save();
        $this->assertTrue($first);

        $this->expectException(\PDOException::class);

        $user2 = new User('duplicate', 'pass2');
        $user2->save();
    }
}
