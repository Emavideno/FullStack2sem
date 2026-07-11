<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Database\Database;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $db = Database::getConnection();
        $db->exec("DELETE FROM users WHERE login LIKE 'test_%'");
    }

    public function testCreateUser()
    {
        $user = new User('test_user', 'password123');
        $result = $user->save();
        
        $this->assertTrue($result);
        $this->assertNotNull($user->getId());
    }

    public function testFindByLogin()
    {
        $user = new User('test_find', 'password123');
        $user->save();
        
        $found = User::findByLogin('test_find');
        $this->assertNotNull($found);
        $this->assertEquals('test_find', $found->getLogin());
    }

    public function testFindById()
    {
        $user = new User('test_id', 'password123');
        $user->save();
        
        $found = User::findById($user->getId());
        $this->assertNotNull($found);
        $this->assertEquals('test_id', $found->getLogin());
    }

    public function testVerifyPassword()
    {
        $user = new User('test_verify', 'correct_password');
        $user->save();
        
        $found = User::findByLogin('test_verify');
        $this->assertTrue($found->verifyPassword('correct_password'));
        $this->assertFalse($found->verifyPassword('wrong_password'));
    }

    public function testBlockUser()
    {
        $user = new User('test_block', 'password123');
        $user->save();
        
        $user->block();
        $found = User::findByLogin('test_block');
        $this->assertTrue($found->isBlocked());
        
        $user->unblock();
        $found = User::findByLogin('test_block');
        $this->assertFalse($found->isBlocked());
    }

    public function testUpdatePassword()
    {
        $user = new User('test_update_password', 'old_password');
        $user->save();
        
        $user->updatePassword('new_password');
        
        $found = User::findByLogin('test_update_password');
        $this->assertTrue($found->verifyPassword('new_password'));
        $this->assertFalse($found->verifyPassword('old_password'));
    }

    public function testIsAdmin()
    {
        $user = new User('test_admin', 'password123', 'admin');
        $user->save();
        
        $this->assertTrue($user->isAdmin());
    }

    public function testGetCount()
    {
        $initialCount = User::getCount();
        
        $user = new User('test_count', 'password123');
        $user->save();
        
        $newCount = User::getCount();
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function testFindAll()
    {
        $user1 = new User('test_all_1', 'password123');
        $user1->save();
        
        $user2 = new User('test_all_2', 'password123');
        $user2->save();
        
        $users = User::findAll();
        $logins = array_column($users, 'login');
        
        $this->assertContains('test_all_1', $logins);
        $this->assertContains('test_all_2', $logins);
    }
}
