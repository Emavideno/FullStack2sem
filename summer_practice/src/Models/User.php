<?php

namespace App\Models;

use App\Database\Database;

class User
{
    private ?int $id = null;
    private string $login;
    private string $password;
    private string $role = 'user';
    private bool $isBlocked = false;
    private string $created_at;

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
        $user->isBlocked = (bool) $data['is_blocked'];
        $user->created_at = $data['created_at'];
        return $user;
    }

    public static function findById(int $id): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new self($data['login'], '', $data['role']);
        $user->id = $data['id'];
        $user->password = $data['password'];
        $user->isBlocked = (bool) $data['is_blocked'];
        $user->created_at = $data['created_at'];
        return $user;
    }

    public static function findAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM users ORDER BY id");
        return $stmt->fetchAll();
    }

    public static function getCount(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn();
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            try {
                $stmt = $db->prepare("
                INSERT INTO users (login, password, role, is_blocked) 
                VALUES (?, ?, ?, ?)
            ");
                $result = $stmt->execute([
                    $this->login,
                    $this->password,
                    $this->role,
                    $this->isBlocked ? 1 : 0
                ]);

                if ($result) {
                    $this->id = (int) $db->lastInsertId();
                }
                return $result;
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000' || strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
                    return false;
                }
                throw $e;
            }
        }

        $stmt = $db->prepare("
        UPDATE users SET 
            login = ?, 
            role = ?, 
            is_blocked = ?
        WHERE id = ?
    ");
        return $stmt->execute([
            $this->login,
            $this->role,
            $this->isBlocked ? 1 : 0,
            $this->id
        ]);
    }

    public function updatePassword(string $newPassword): bool
    {
        $this->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$this->password, $this->id]);
    }

    public function block(): void
    {
        $this->isBlocked = true;
        $this->save();
    }

    public function unblock(): void
    {
        $this->isBlocked = false;
        $this->save();
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
    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
