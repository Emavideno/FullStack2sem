<?php

namespace App\Models;

use App\Database\Database;

class Category
{
    private ?int $id = null;
    private string $name;
    private string $slug;

    public function __construct(string $name, string $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    public static function all(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function save(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $result = $stmt->execute([$this->name, $this->slug]);

        if ($result) {
            $this->id = (int) $db->lastInsertId();
        }

        return $result;
    }

    public function delete(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getSlug(): string
    {
        return $this->slug;
    }
}
