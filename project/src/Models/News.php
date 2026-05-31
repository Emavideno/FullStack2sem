<?php

namespace App\Models;

use App\Database\Database;

class News
{
    private ?int $id = null;
    private ?int $categoryId;
    private string $title;
    private string $excerpt;
    private string $content;
    private string $sourceUrl = '';
    private int $views = 0;

    public function __construct(?int $categoryId, string $title, string $excerpt, string $content, string $sourceUrl = '')
    {
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->content = $content;
        $this->sourceUrl = $sourceUrl;
    }

    public static function all(int $page = 1, int $perPage = 5): array
    {
        $offset = ($page - 1) * $perPage;
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '👍') as likes,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '❤️') as hearts,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '🔥') as fire,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😊') as smile,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😢') as sad,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count,
                c.name as category_name
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            ORDER BY n.created_at DESC
            LIMIT :offset, :perPage
        ");

        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function allAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT 
                n.*,
                c.name as category_name
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            ORDER BY n.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '👍') as likes,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '❤️') as hearts,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '🔥') as fire,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😊') as smile,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😢') as sad,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count,
                c.name as category_name
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public static function countAll(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM news");
        return (int) $stmt->fetchColumn();
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            $stmt = $db->prepare("
                INSERT INTO news (category_id, title, excerpt, content, source_url, views) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $this->categoryId,
                $this->title,
                $this->excerpt,
                $this->content,
                $this->sourceUrl,
                $this->views
            ]);

            if ($result) {
                $this->id = (int) $db->lastInsertId();
            }

            return $result;
        } else {
            $stmt = $db->prepare("
                UPDATE news 
                SET category_id = ?, title = ?, excerpt = ?, content = ?, source_url = ?, views = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $this->categoryId,
                $this->title,
                $this->excerpt,
                $this->content,
                $this->sourceUrl,
                $this->views,
                $this->id
            ]);
        }
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public static function update(int $id, int $categoryId, string $title, string $excerpt, string $content, string $sourceUrl = ''): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE news 
            SET category_id = ?, title = ?, excerpt = ?, content = ?, source_url = ?
            WHERE id = ?
        ");
        return $stmt->execute([$categoryId, $title, $excerpt, $content, $sourceUrl, $id]);
    }

    public static function deleteById(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function search(string $query, int $page = 1, int $perPage = 5): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$query}%";
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '👍') as likes,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '❤️') as hearts,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '🔥') as fire,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😊') as smile,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😢') as sad,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count,
                c.name as category_name
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE n.title LIKE :query OR n.content LIKE :query
            ORDER BY n.created_at DESC
            LIMIT :offset, :perPage
        ");

        $stmt->bindValue(':query', $searchTerm);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countSearchResults(string $query): int
    {
        $searchTerm = "%{$query}%";
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM news WHERE title LIKE :query OR content LIKE :query");
        $stmt->bindValue(':query', $searchTerm);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function findByCategories(array $categoryIds, int $page = 1, int $perPage = 5): array
    {
        if (empty($categoryIds)) {
            return self::all($page, $perPage);
        }

        $offset = ($page - 1) * $perPage;
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT 
                n.*,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '👍') as likes,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '❤️') as hearts,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '🔥') as fire,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😊') as smile,
                (SELECT COUNT(*) FROM reactions WHERE news_id = n.id AND reaction_type = '😢') as sad,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id) as comments_count,
                c.name as category_name
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE n.category_id IN ($placeholders)
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?
        ");

        $params = array_merge($categoryIds, [$perPage, $offset]);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByCategories(array $categoryIds): int
    {
        if (empty($categoryIds)) {
            return self::countAll();
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM news WHERE category_id IN ($placeholders)");
        $stmt->execute($categoryIds);
        return (int) $stmt->fetchColumn();
    }

    public static function toggleReaction(int $newsId, int $userId, string $reactionType): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id FROM reactions WHERE news_id = ? AND user_id = ? AND reaction_type = ?");
        $stmt->execute([$newsId, $userId, $reactionType]);

        if ($stmt->fetch()) {
            $stmt = $db->prepare("DELETE FROM reactions WHERE news_id = ? AND user_id = ? AND reaction_type = ?");
            $stmt->execute([$newsId, $userId, $reactionType]);
            return false;
        } else {
            $stmt = $db->prepare("INSERT INTO reactions (news_id, user_id, reaction_type) VALUES (?, ?, ?)");
            $stmt->execute([$newsId, $userId, $reactionType]);
            return true;
        }
    }

    public static function getReactionCount(int $newsId, string $reactionType): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM reactions WHERE news_id = ? AND reaction_type = ?");
        $stmt->execute([$newsId, $reactionType]);
        return (int) $stmt->fetchColumn();
    }

    public static function hasUserReaction(int $newsId, int $userId, string $reactionType): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM reactions WHERE news_id = ? AND user_id = ? AND reaction_type = ?");
        $stmt->execute([$newsId, $userId, $reactionType]);
        return (bool) $stmt->fetch();
    }

    public static function getUserReactions(int $newsId, int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT reaction_type FROM reactions WHERE news_id = ? AND user_id = ?");
        $stmt->execute([$newsId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function incrementViews(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE news SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getExcerpt(): string
    {
        return $this->excerpt;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }
    public function getViews(): int
    {
        return $this->views;
    }
}
