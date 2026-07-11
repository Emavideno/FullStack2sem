<?php

namespace App\Models;

use App\Database\Database;

class ApiUpdate
{
    private ?int $id = null;
    private string $status;
    private int $countries_imported = 0;
    private ?string $error_message = null;
    private string $created_at;

    public function __construct(string $status, int $countries_imported, ?string $error_message = null)
    {
        $this->status = $status;
        $this->countries_imported = $countries_imported;
        $this->error_message = $error_message;
    }

    public static function create(array $data): self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO api_updates (status, countries_imported, error_message) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['status'],
            $data['countries_imported'],
            $data['error_message'] ?? null
        ]);

        $update = new self(
            $data['status'],
            $data['countries_imported'],
            $data['error_message'] ?? null
        );
        $update->id = (int) $db->lastInsertId();
        $update->created_at = date('Y-m-d H:i:s');
        return $update;
    }

    public static function getLast(): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT * FROM api_updates 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        return $stmt->fetch() ?: null;
    }

    public static function needsUpdate(): bool
    {
        $lastUpdate = Country::getLastUpdated();

        if (!$lastUpdate) {
            return true;
        }

        $lastUpdateTime = strtotime($lastUpdate);
        $hoursPassed = (time() - $lastUpdateTime) / 3600;

        return $hoursPassed >= 24;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getCountriesImported(): int
    {
        return $this->countries_imported;
    }
    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}
