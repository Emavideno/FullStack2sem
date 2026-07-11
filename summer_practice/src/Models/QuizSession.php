<?php

namespace App\Models;

use App\Database\Database;

class QuizSession
{
    private ?int $id = null;
    private int $user_id;
    private int $question_id;
    private ?string $user_answer = null;
    private bool $is_correct = false;
    private string $answered_at;

    public function __construct(int $userId, int $questionId, string $userAnswer, bool $isCorrect)
    {
        $this->user_id = $userId;
        $this->question_id = $questionId;
        $this->user_answer = $userAnswer;
        $this->is_correct = $isCorrect;
    }

    public static function createFromAnswer(int $userId, int $questionId, string $userAnswer, bool $isCorrect): self
    {
        return new self($userId, $questionId, $userAnswer, $isCorrect);
    }

    public static function getUserHistory(int $userId, int $limit = 50): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT qs.*, q.type, q.question_data, c.name as country_name
            FROM quiz_sessions qs
            JOIN quiz_questions q ON qs.question_id = q.id
            JOIN countries c ON q.country_id = c.id
            WHERE qs.user_id = ?
            ORDER BY qs.answered_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public static function getUserStats(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_answers,
                SUM(is_correct) as correct_answers,
                ROUND(AVG(is_correct) * 100, 2) as success_rate
            FROM quiz_sessions
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: ['total_answers' => 0, 'correct_answers' => 0, 'success_rate' => 0];
    }

    public static function getUserStatsByType(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                q.type,
                COUNT(*) as total,
                SUM(qs.is_correct) as correct,
                ROUND(AVG(qs.is_correct) * 100, 2) as rate
            FROM quiz_sessions qs
            JOIN quiz_questions q ON qs.question_id = q.id
            WHERE qs.user_id = ?
            GROUP BY q.type
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getGlobalLeaderboard(?string $type = null, int $limit = 10): array
    {
        $db = Database::getConnection();

        $sql = "
        SELECT 
            u.id as user_id,
            u.login,
            COUNT(qs.id) as total_answers,
            SUM(qs.is_correct) as correct_answers,
            ROUND(CAST(SUM(qs.is_correct) AS FLOAT) / COUNT(qs.id) * 100, 2) as success_rate
        FROM quiz_sessions qs
        JOIN users u ON qs.user_id = u.id
        JOIN quiz_questions q ON qs.question_id = q.id
        WHERE qs.is_correct IS NOT NULL
          AND u.is_blocked = 0  -- Исключаем заблокированных
    ";

        $params = [];

        if ($type !== null && $type !== '') {
            $sql .= " AND q.type = ?";
            $params[] = $type;
        }

        $sql .= "
        GROUP BY u.id, u.login
        HAVING COUNT(qs.id) >= 1
        ORDER BY success_rate DESC, total_answers DESC
        LIMIT ?
    ";

        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            INSERT INTO quiz_sessions (user_id, question_id, user_answer, is_correct) 
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $this->user_id,
            $this->question_id,
            $this->user_answer,
            $this->is_correct ? 1 : 0
        ]);

        if ($result) {
            $this->id = (int) $db->lastInsertId();
            $this->updateUserStats();
        }
        return $result;
    }

    private function updateUserStats(): void
    {
        $question = QuizQuestion::findById($this->question_id);
        if (!$question) {
            return;
        }

        $country = $question->getCountry();
        if (!$country) {
            return;
        }

        $stat = UserStat::findOrCreate(
            $this->user_id,
            $question->getType(),
            $country->getRegion()
        );
        $stat->incrementAttempt($this->is_correct);
    }

    public static function cleanHistoryOlderThan(int $days): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            DELETE FROM quiz_sessions 
            WHERE answered_at < datetime('now', '-' || ? || ' days')
        ");
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUserId(): int
    {
        return $this->user_id;
    }
    public function getQuestionId(): int
    {
        return $this->question_id;
    }
    public function getUserAnswer(): ?string
    {
        return $this->user_answer;
    }
    public function isCorrect(): bool
    {
        return $this->is_correct;
    }
    public function getAnsweredAt(): string
    {
        return $this->answered_at;
    }
}
