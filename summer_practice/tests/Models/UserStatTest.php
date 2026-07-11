<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\UserStat;
use App\Database\Database;

class UserStatTest extends TestCase
{
    private int $testUserId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        
        $db = Database::getConnection();
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', 'password', 'user')");
        $db->exec("DELETE FROM user_stats WHERE user_id = 1");
    }

    public function testGetSuccessRateWithZeroAttempts()
    {
        $stat = UserStat::findOrCreate(1, 'flag_to_country', 'Europe');
        $this->assertEquals(0, $stat->getSuccessRate());
    }

    public function testGetSuccessRateWithAttempts()
    {
        $stat = UserStat::findOrCreate(1, 'flag_to_country', 'Europe');
        $stat->incrementAttempt(true);
        $stat->incrementAttempt(true);
        $stat->incrementAttempt(false);
        
        $this->assertEquals(66.67, $stat->getSuccessRate());
    }

    public function testGetSuccessRateWithAllCorrect()
    {
        $stat = UserStat::findOrCreate(1, 'flag_to_country', 'Europe');
        $stat->incrementAttempt(true);
        $stat->incrementAttempt(true);
        $stat->incrementAttempt(true);
        
        $this->assertEquals(100, $stat->getSuccessRate());
    }

    public function testGetSuccessRateWithAllWrong()
    {
        $stat = UserStat::findOrCreate(1, 'flag_to_country', 'Europe');
        $stat->incrementAttempt(false);
        $stat->incrementAttempt(false);
        $stat->incrementAttempt(false);
        
        $this->assertEquals(0, $stat->getSuccessRate());
    }

    public function testUserStatsMultipleRegions()
    {
        $stat1 = UserStat::findOrCreate(1, 'flag_to_country', 'Europe');
        $stat1->incrementAttempt(true);
        
        $stat2 = UserStat::findOrCreate(1, 'flag_to_country', 'Asia');
        $stat2->incrementAttempt(true);
        $stat2->incrementAttempt(false);
        
        $stats = UserStat::getUserStats(1);
        $this->assertCount(2, $stats);
        
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('region', $stat);
            $this->assertArrayHasKey('total_attempts', $stat);
            $this->assertArrayHasKey('correct_attempts', $stat);
        }
    }
}
