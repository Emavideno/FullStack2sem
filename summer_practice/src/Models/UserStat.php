<?php

namespace App\Models;

use App\Database\Database;

class UserStat
{
    private ?int $id = null;
    private int $user_id;
    private string $question_type;
    private string $region;
    private int $total_attempts = 0;
    private int $correct_attempts = 0;
    private string $last_played_at;

    public function __construct(int $userId, string $questionType, string $region)
    {
        $this->user_id = $userId;
        $this->question_type = $questionType;
        $this->region = $region;
    }

    public static function findOrCreate(int $userId, string $questionType, string $region): self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM user_stats 
            WHERE user_id = ? AND question_type = ? AND region = ?
        ");
        $stmt->execute([$userId, $questionType, $region]);
        $data = $stmt->fetch();

        if ($data) {
            return self::hydrate($data);
        }

        return new self($userId, $questionType, $region);
    }

    public static function getUserStats(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function incrementAttempt(bool $correct): void
    {
        $this->total_attempts++;
        if ($correct) {
            $this->correct_attempts++;
        }
        $this->last_played_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            $stmt = $db->prepare("
    INSERT INTO user_stats 
    (user_id, question_type, region, total_attempts, correct_attempts, last_played_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
            $result = $stmt->execute([
                $this->user_id,
                $this->question_type,
                $this->region,
                $this->total_attempts,
                $this->correct_attempts,
                $this->last_played_at
            ]);
            if ($result) {
                $this->id = (int) $db->lastInsertId();
            }
            return $result;
        }

        $stmt = $db->prepare("
            UPDATE user_stats SET 
                total_attempts = ?, correct_attempts = ?, last_played_at = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $this->total_attempts,
            $this->correct_attempts,
            $this->last_played_at,
            $this->id
        ]);
    }

    private static function hydrate(array $data): self
    {
        $stat = new self($data['user_id'], $data['question_type'], $data['region']);
        $stat->id = $data['id'];
        $stat->total_attempts = $data['total_attempts'];
        $stat->correct_attempts = $data['correct_attempts'];
        $stat->last_played_at = $data['last_played_at'];
        return $stat;
    }

    public function getSuccessRate(): float
    {
        if ($this->total_attempts === 0) {
            return 0;
        }
        return round(($this->correct_attempts / $this->total_attempts) * 100, 2);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUserId(): int
    {
        return $this->user_id;
    }
    public function getQuestionType(): string
    {
        return $this->question_type;
    }
    public function getRegion(): string
    {
        return $this->region;
    }
    public function getTotalAttempts(): int
    {
        return $this->total_attempts;
    }
    public function getCorrectAttempts(): int
    {
        return $this->correct_attempts;
    }
    public function getLastPlayedAt(): string
    {
        return $this->last_played_at;
    }
}
