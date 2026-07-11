<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AdminController;
use App\Models\User;
use App\Models\Country;
use App\Models\QuizQuestion;
use App\Database\Database;

class AdminControllerTest extends TestCase
{
    private AdminController $controller;
    private int $adminUserId = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AdminController();
        
        $db = Database::getConnection();
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $db->exec("DELETE FROM users WHERE id IN (2, 3, 4)");
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (2, 'admin', '{$hashedPassword}', 'admin')");
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (3, 'testuser2', '{$hashedPassword}', 'user')");
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (4, 'testuser3', '{$hashedPassword}', 'user')");
    }

    public function testGetUserCount()
    {
        $users = User::findAll();
        $this->assertGreaterThan(0, count($users));
    }

    public function testBlockUser()
    {
        $user = User::findById(3);
        $this->assertNotNull($user);
        
        $user->block();
        
        $blocked = User::findById(3);
        $this->assertNotNull($blocked);
        $this->assertTrue($blocked->isBlocked());
        
        $user->unblock();
        
        $unblocked = User::findById(3);
        $this->assertNotNull($unblocked);
        $this->assertFalse($unblocked->isBlocked());
    }

    public function testGetCountryCount()
    {
        $count = Country::getCount();
        $this->assertGreaterThan(0, $count);
    }

    public function testGetQuestionCount()
    {
        $count = QuizQuestion::getTotalCount();
        $this->assertGreaterThan(0, $count);
    }

    public function testFindUserById()
    {
        $existingUser = User::findByLogin('admin');
        $this->assertNotNull($existingUser);
        
        $user = User::findById($existingUser->getId());
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->getLogin());
    }

    public function testFindAllUsers()
    {
        $users = User::findAll();
        $this->assertGreaterThan(0, count($users));
        
        $logins = array_column($users, 'login');
        $this->assertContains('admin', $logins);
    }

    public function testAdminExists()
    {
        $admin = User::findByLogin('admin');
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
    }
}
