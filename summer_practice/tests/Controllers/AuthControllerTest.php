<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\User;
use App\Database\Database;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthController();

        $db = Database::getConnection();
        $db->exec("DELETE FROM users WHERE login LIKE 'test_%'");
        $db->exec("DELETE FROM users WHERE login = 'existing_user'");
    }

    public function testRegisterValidation()
    {
        $login = 'test_user_valid';
        $password = 'password123';

        $user = new User($login, $password);
        $result = $user->save();

        $this->assertTrue($result);
        $this->assertNotNull($user->getId());

        $found = User::findByLogin($login);
        $this->assertNotNull($found);
        $this->assertEquals($login, $found->getLogin());
    }

    public function testLoginValidation()
    {
        $login = 'test_login_user';
        $password = 'password123';

        $user = new User($login, $password);
        $user->save();

        $found = User::findByLogin($login);
        $this->assertNotNull($found);
        $this->assertTrue($found->verifyPassword($password));
        $this->assertFalse($found->verifyPassword('wrong_password'));
    }

    public function testBlockedUserCannotLogin()
    {
        $login = 'test_blocked_user';
        $password = 'password123';

        $user = new User($login, $password);
        $user->save();
        $user->block();

        $found = User::findByLogin($login);
        $this->assertNotNull($found);
        $this->assertTrue($found->isBlocked());
    }

    public function testFindNonExistentUser()
    {
        $found = User::findByLogin('non_existent_user_123');
        $this->assertNull($found);
    }

    public function testCreateAdmin()
    {
        $login = 'test_admin_user';
        $password = 'password123';

        $user = new User($login, $password, 'admin');
        $user->save();

        $found = User::findByLogin($login);
        $this->assertNotNull($found);
        $this->assertTrue($found->isAdmin());
    }

    public function testUserCanUpdatePassword()
    {
        $login = 'test_password_user';
        $password = 'old_password';

        $user = new User($login, $password);
        $user->save();

        $user->updatePassword('new_password');

        $found = User::findByLogin($login);
        $this->assertTrue($found->verifyPassword('new_password'));
        $this->assertFalse($found->verifyPassword('old_password'));
    }

    public function testUserCannotRegisterWithExistingLogin()
    {
        $login = 'existing_user';
        $password = 'password123';

        $user1 = new User($login, $password);
        $result1 = $user1->save();
        $this->assertTrue($result1);

        $user2 = new User($login, 'another_password');
        $result2 = $user2->save();

        $this->assertFalse($result2);

        $found = User::findByLogin($login);
        $this->assertNotNull($found);
        $this->assertTrue($found->verifyPassword($password));
    }
}
