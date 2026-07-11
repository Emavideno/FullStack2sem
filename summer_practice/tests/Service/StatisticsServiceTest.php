<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\StatisticsService;
use App\Models\UserStat;
use App\Database\Database;

class StatisticsServiceTest extends TestCase
{
    private StatisticsService $statisticsService;
    private int $testUserId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = new StatisticsService();
        
        $db = Database::getConnection();
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', 'password', 'user')");
    }

    public function testGetUserWeakRegions()
    {
        $weakRegions = $this->statisticsService->getUserWeakRegions($this->testUserId);
        
        $this->assertIsArray($weakRegions);
        foreach ($weakRegions as $region) {
            $this->assertArrayHasKey('region', $region);
            $this->assertArrayHasKey('success_rate', $region);
            $this->assertArrayHasKey('total_attempts', $region);
            $this->assertArrayHasKey('correct_attempts', $region);
            $this->assertLessThan(50, $region['success_rate']);
        }
    }

    public function testGetUserStrongRegions()
    {
        $strongRegions = $this->statisticsService->getUserStrongRegions($this->testUserId);
        
        $this->assertIsArray($strongRegions);
        foreach ($strongRegions as $region) {
            $this->assertArrayHasKey('region', $region);
            $this->assertArrayHasKey('success_rate', $region);
            $this->assertArrayHasKey('total_attempts', $region);
            $this->assertArrayHasKey('correct_attempts', $region);
            $this->assertGreaterThanOrEqual(50, $region['success_rate']);
        }
    }

    public function testGetRegionStats()
    {
        $regionStats = $this->statisticsService->getRegionStats($this->testUserId);
        
        $this->assertIsArray($regionStats);
        foreach ($regionStats as $stat) {
            $this->assertArrayHasKey('region', $stat);
            $this->assertArrayHasKey('total_attempts', $stat);
            $this->assertArrayHasKey('correct_attempts', $stat);
            $this->assertArrayHasKey('success_rate', $stat);
        }
    }

    public function testGetGlobalLeaderboard()
    {
        $leaderboard = $this->statisticsService->getGlobalLeaderboard();
        
        $this->assertIsArray($leaderboard);
        foreach ($leaderboard as $player) {
            $this->assertArrayHasKey('user_id', $player);
            $this->assertArrayHasKey('login', $player);
            $this->assertArrayHasKey('total_answers', $player);
            $this->assertArrayHasKey('correct_answers', $player);
            $this->assertArrayHasKey('success_rate', $player);
        }
    }

    public function testGetGlobalLeaderboardWithType()
    {
        $leaderboard = $this->statisticsService->getGlobalLeaderboard('flag_to_country');
        
        $this->assertIsArray($leaderboard);
    }

    public function testGetUserOverallStats()
    {
        $stats = $this->statisticsService->getUserOverallStats($this->testUserId);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_questions_answered', $stats);
        $this->assertArrayHasKey('correct_answers', $stats);
        $this->assertArrayHasKey('total_answers', $stats);
        $this->assertArrayHasKey('overall_rate', $stats);
        $this->assertArrayHasKey('types_played', $stats);
    }

    public function testGetAllRegionsStats()
    {
        $stats = $this->statisticsService->getAllRegionsStats();
        
        $this->assertIsArray($stats);
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('region', $stat);
            $this->assertArrayHasKey('country_count', $stat);
            $this->assertArrayHasKey('avg_population', $stat);
            $this->assertArrayHasKey('avg_area', $stat);
        }
    }
}
