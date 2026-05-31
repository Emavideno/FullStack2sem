<?php

namespace App\Models;

use App\Database\Database;

class User
{
    private ?int $id = null;
    private string $login;
    private string $password;
    private string $role = 'user';

    public function __construct(string $login, string $password, string $role = 'user')
    {
        $this->login = $login;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->role = $role;
    }

    public static function findByLogin(string $login): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new self($data['login'], '', $data['role']);
        $user->id = $data['id'];
        $user->password = $data['password'];
        return $user;
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            $stmt = $db->prepare("INSERT INTO users (login, password, role) VALUES (?, ?, ?)");
            $result = $stmt->execute([$this->login, $this->password, $this->role]);

            if ($result) {
                $this->id = (int) $db->lastInsertId();
            }

            return $result;
        }

        return false;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getLogin(): string
    {
        return $this->login;
    }
    public function getRole(): string
    {
        return $this->role;
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
