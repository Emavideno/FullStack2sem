<?php

namespace App\Models;

use App\Database\Database;

class Comment
{
    private ?int $id = null;
    private int $newsId;
    private string $author;
    private string $text;
    private string $createdAt;

    public function __construct(int $newsId, string $author, string $text)
    {
        $this->newsId = $newsId;
        $this->author = $author;
        $this->text = $text;
    }

    public static function findByNewsId(int $newsId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                id,
                COALESCE(author, 'Аноним') as author,
                text,
                created_at as date
            FROM comments 
            WHERE news_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$newsId]);
        return $stmt->fetchAll();
    }

    public function save(): int|false
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO comments (news_id, author, text) VALUES (?, ?, ?)");

        if ($stmt->execute([$this->newsId, $this->author, $this->text])) {
            $this->id = (int) $db->lastInsertId();
            return (int) $db->lastInsertId();
        }

        return false;
    }

    public static function getAllWithNewsTitle(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
        SELECT c.*, n.title as news_title 
        FROM comments c
        LEFT JOIN news n ON c.news_id = n.id
        ORDER BY c.created_at DESC
    ");
        return $stmt->fetchAll();
    }

    public static function deleteById(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNewsId(): int
    {
        return $this->newsId;
    }
    public function getAuthor(): string
    {
        return $this->author;
    }
    public function getText(): string
    {
        return $this->text;
    }
}
