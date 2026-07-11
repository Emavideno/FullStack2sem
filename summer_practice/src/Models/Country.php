<?php

namespace App\Models;

use App\Database\Database;

class Country
{
    private ?int $id = null;
    private string $name;
    private string $capital;
    private string $region;
    private ?string $subregion = null;
    private ?int $population = null;
    private ?float $area = null;
    private string $flag_url;
    private ?string $lat_lng = null;
    private ?string $timezones = null;
    private ?string $borders = null;
    private string $created_at;
    private ?string $updated_at = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->name = $data['name'] ?? '';
            $this->capital = $data['capital'] ?? '';
            $this->region = $data['region'] ?? '';
            $this->subregion = $data['subregion'] ?? null;
            $this->population = $data['population'] ?? null;
            $this->area = $data['area'] ?? null;
            $this->flag_url = $data['flag_url'] ?? '';
            $this->lat_lng = $data['lat_lng'] ?? null;
            $this->timezones = $data['timezones'] ?? null;
            $this->borders = $data['borders'] ?? null;
        }
    }

    public static function findAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM countries ORDER BY name");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return self::hydrate($data);
    }

    public static function findByName(string $name): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM countries WHERE name = ?");
        $stmt->execute([$name]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return self::hydrate($data);
    }

    public static function findByRegion(string $region): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM countries WHERE region = ? ORDER BY name");
        $stmt->execute([$region]);
        return $stmt->fetchAll();
    }

    public static function getRegions(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT DISTINCT region FROM countries ORDER BY region");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getRegionsStats(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
        SELECT 
            region,
            COUNT(*) as country_count,
            AVG(population) as avg_population,
            AVG(area) as avg_area
        FROM countries
        GROUP BY region
        ORDER BY region
    ");
        return $stmt->fetchAll();
    }

    public static function getCount(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM countries");
        return (int) $stmt->fetchColumn();
    }

    public static function getRandomCountries(int $limit, array $excludeIds = []): array
    {
        $db = Database::getConnection();
        $exclude = !empty($excludeIds) ? "WHERE id NOT IN (" . implode(',', $excludeIds) . ")" : "";
        $stmt = $db->query("
            SELECT * FROM countries 
            {$exclude} 
            ORDER BY RANDOM() 
            LIMIT {$limit}
        ");
        return $stmt->fetchAll();
    }

    public static function getLastUpdated(): ?string
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT MAX(updated_at) FROM countries");
        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    public static function deleteAll(): void
    {
        $db = Database::getConnection();
        $db->exec("DELETE FROM countries");
        $db->exec("DELETE FROM sqlite_sequence WHERE name='countries'");
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id === null) {
            $stmt = $db->prepare("
                INSERT INTO countries (
                    name, capital, region, subregion, population, area, 
                    flag_url, lat_lng, timezones, borders
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $this->name,
                $this->capital,
                $this->region,
                $this->subregion,
                $this->population,
                $this->area,
                $this->flag_url,
                $this->lat_lng,
                $this->timezones,
                $this->borders
            ]);

            if ($result) {
                $this->id = (int) $db->lastInsertId();
            }
            return $result;
        }

        $stmt = $db->prepare("
            UPDATE countries SET 
                name = ?, capital = ?, region = ?, subregion = ?, 
                population = ?, area = ?, flag_url = ?, lat_lng = ?, 
                timezones = ?, borders = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([
            $this->name,
            $this->capital,
            $this->region,
            $this->subregion,
            $this->population,
            $this->area,
            $this->flag_url,
            $this->lat_lng,
            $this->timezones,
            $this->borders,
            $this->id
        ]);
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM countries WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    private static function hydrate(array $data): self
    {
        $country = new self();
        $country->id = (int) $data['id'];
        $country->name = $data['name'];
        $country->capital = $data['capital'];
        $country->region = $data['region'];
        $country->subregion = $data['subregion'];
        $country->population = $data['population'] ? (int) $data['population'] : null;
        $country->area = $data['area'] ? (float) $data['area'] : null;
        $country->flag_url = $data['flag_url'];
        $country->lat_lng = $data['lat_lng'];
        $country->timezones = $data['timezones'];
        $country->borders = $data['borders'];
        $country->created_at = $data['created_at'];
        $country->updated_at = $data['updated_at'];
        return $country;
    }

    public static function getRandomCountriesWithCapitals(int $count, int $excludeId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
        SELECT id, capital FROM countries 
        WHERE id != ? AND capital != 'Unknown'
        ORDER BY RANDOM() 
        LIMIT ?
    ");
        $stmt->execute([$excludeId, $count]);
        return $stmt->fetchAll();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getCapital(): string
    {
        return $this->capital;
    }
    public function getRegion(): string
    {
        return $this->region;
    }
    public function getSubregion(): ?string
    {
        return $this->subregion;
    }
    public function getPopulation(): ?int
    {
        return $this->population;
    }
    public function getArea(): ?float
    {
        return $this->area;
    }
    public function getFlagUrl(): string
    {
        return $this->flag_url;
    }
    public function getLatLng(): ?string
    {
        return $this->lat_lng;
    }
    public function getTimezones(): ?string
    {
        return $this->timezones;
    }
    public function getBorders(): ?string
    {
        return $this->borders;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    public function setCapital(string $capital): self
    {
        $this->capital = $capital;
        return $this;
    }
    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
    }
    public function setSubregion(?string $subregion): self
    {
        $this->subregion = $subregion;
        return $this;
    }
    public function setPopulation(?int $population): self
    {
        $this->population = $population;
        return $this;
    }
    public function setArea(?float $area): self
    {
        $this->area = $area;
        return $this;
    }
    public function setFlagUrl(string $flagUrl): self
    {
        $this->flag_url = $flagUrl;
        return $this;
    }
    public function setLatLng(?string $latLng): self
    {
        $this->lat_lng = $latLng;
        return $this;
    }
    public function setTimezones(?string $timezones): self
    {
        $this->timezones = $timezones;
        return $this;
    }
    public function setBorders(?string $borders): self
    {
        $this->borders = $borders;
        return $this;
    }
}
