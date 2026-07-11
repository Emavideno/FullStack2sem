<?php

namespace App\Models;

use App\Database\Database;

class QuizQuestion
{
    private ?int $id = null;
    private int $country_id;
    private string $type;
    private array $question_data;
    private string $created_at;

    public const TYPE_FLAG_TO_COUNTRY = 'flag_to_country';
    public const TYPE_COUNTRY_TO_FLAG = 'country_to_flag';
    public const TYPE_CAPITAL_TO_COUNTRY = 'capital_to_country';
    public const TYPE_COUNTRY_TO_CAPITAL = 'country_to_capital';
    public const TYPE_POPULATION = 'population';
    public const TYPE_AREA = 'area';

    public static function getTypes(): array
    {
        return [
            self::TYPE_FLAG_TO_COUNTRY,
            self::TYPE_COUNTRY_TO_FLAG,
            self::TYPE_CAPITAL_TO_COUNTRY,
            self::TYPE_COUNTRY_TO_CAPITAL,
            self::TYPE_POPULATION,
            self::TYPE_AREA,
        ];
    }

    public static function getTypeLabel(string $type): string
    {
        $labels = [
            self::TYPE_FLAG_TO_COUNTRY => 'Угадай страну по флагу',
            self::TYPE_COUNTRY_TO_FLAG => 'Угадай флаг по стране',
            self::TYPE_CAPITAL_TO_COUNTRY => 'Угадай страну по столице',
            self::TYPE_COUNTRY_TO_CAPITAL => 'Угадай столицу по стране',
            self::TYPE_POPULATION => 'Угадай страну по населению',
            self::TYPE_AREA => 'Угадай страну по площади',
        ];
        return $labels[$type] ?? $type;
    }

    public function __construct(int $countryId, string $type, array $questionData)
    {
        $this->country_id = $countryId;
        $this->type = $type;
        $this->question_data = $questionData;
    }

    public static function findById(int $id): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_questions WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return self::hydrate($data);
    }

    public static function findByType(string $type, int $limit = 10): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM quiz_questions 
            WHERE type = ? 
            ORDER BY RANDOM() 
            LIMIT ?
        ");
        $stmt->execute([$type, $limit]);
        return $stmt->fetchAll();
    }

    public static function getRandomQuestion(string $type, array $excludeIds = []): ?self
    {
        $db = Database::getConnection();
        $exclude = !empty($excludeIds) ? "AND id NOT IN (" . implode(',', $excludeIds) . ")" : "";
        $stmt = $db->prepare("
            SELECT * FROM quiz_questions 
            WHERE type = ? {$exclude} 
            ORDER BY RANDOM() 
            LIMIT 1
        ");
        $stmt->execute([$type]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return self::hydrate($data);
    }

    public static function getCountByType(string $type): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM quiz_questions WHERE type = ?");
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn();
    }

    public static function getTotalCount(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM quiz_questions");
        return (int) $stmt->fetchColumn();
    }

    public static function deleteAll(): void
    {
        $db = Database::getConnection();
        $db->exec("DELETE FROM quiz_questions");
        $db->exec("DELETE FROM sqlite_sequence WHERE name='quiz_questions'");
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            $stmt = $db->prepare("
                INSERT INTO quiz_questions (country_id, type, question_data) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([
                $this->country_id,
                $this->type,
                json_encode($this->question_data)
            ]);

            if ($result) {
                $this->id = (int) $db->lastInsertId();
            }
            return $result;
        }

        $stmt = $db->prepare("
            UPDATE quiz_questions SET 
                country_id = ?, 
                type = ?, 
                question_data = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $this->country_id,
            $this->type,
            json_encode($this->question_data),
            $this->id
        ]);
    }

    public static function getRandomQuestionByCountries(string $type, array $countryIds, array $excludeIds = []): ?self
    {
        if (empty($countryIds)) {
            return null;
        }

        $db = Database::getConnection();
        $exclude = !empty($excludeIds) ? "AND id NOT IN (" . implode(',', $excludeIds) . ")" : "";
        $placeholders = implode(',', array_fill(0, count($countryIds), '?'));

        $stmt = $db->prepare("
        SELECT * FROM quiz_questions 
        WHERE type = ? 
          AND country_id IN ({$placeholders})
          {$exclude}
        ORDER BY RANDOM() 
        LIMIT 1
    ");

        $params = array_merge([$type], $countryIds);
        $stmt->execute($params);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return self::hydrate($data);
    }

    public static function getRandomQuestionByRegion(string $region, array $excludeIds = []): ?self
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id FROM countries WHERE region = ?");
        $stmt->execute([$region]);
        $countryIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($countryIds)) {
            return null;
        }

        $exclude = !empty($excludeIds) ? "AND id NOT IN (" . implode(',', $excludeIds) . ")" : "";
        $placeholders = implode(',', array_fill(0, count($countryIds), '?'));

        $types = [
            self::TYPE_FLAG_TO_COUNTRY,
            self::TYPE_COUNTRY_TO_FLAG,
            self::TYPE_CAPITAL_TO_COUNTRY,
            self::TYPE_COUNTRY_TO_CAPITAL,
            self::TYPE_POPULATION,
            self::TYPE_AREA
        ];
        $randomType = $types[array_rand($types)];

        $stmt = $db->prepare("
        SELECT * FROM quiz_questions 
        WHERE type = ? 
          AND country_id IN ({$placeholders})
          {$exclude}
        ORDER BY RANDOM() 
        LIMIT 1
    ");

        $params = array_merge([$randomType], $countryIds);
        $stmt->execute($params);
        $data = $stmt->fetch();

        if ($data) {
            return self::hydrate($data);
        }

        foreach ($types as $tryType) {
            if ($tryType === $randomType) {
                continue;
            }
            $stmt = $db->prepare("
            SELECT * FROM quiz_questions 
            WHERE type = ? 
              AND country_id IN ({$placeholders})
              {$exclude}
            ORDER BY RANDOM() 
            LIMIT 1
        ");
            $params = array_merge([$tryType], $countryIds);
            $stmt->execute($params);
            $data = $stmt->fetch();
            if ($data) {
                return self::hydrate($data);
            }
        }

        return null;
    }

    public function getCorrectAnswer(): string
    {
        return $this->question_data['correct_answer'] ?? '';
    }

    public function getOptions(): array
    {
        return $this->question_data['options'] ?? [];
    }

    public function getCountry(): ?Country
    {
        return Country::findById($this->country_id);
    }

    private static function hydrate(array $data): self
    {
        $question = new self(
            (int) $data['country_id'],
            $data['type'],
            json_decode($data['question_data'], true) ?? []
        );
        $question->id = (int) $data['id'];
        $question->created_at = $data['created_at'];
        return $question;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCountryId(): int
    {
        return $this->country_id;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getQuestionData(): array
    {
        return $this->question_data;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}
