<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\QuizSession;
use App\Models\QuizQuestion;
use App\Models\Country;
use App\Database\Database;

class QuizSessionTest extends TestCase
{
    private int $testUserId = 1;
    private int $testQuestionId;

    protected function setUp(): void
    {
        parent::setUp();

        $db = Database::getConnection();
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', 'password', 'user')");

        $country = new Country([
            'name' => 'Session Test Country',
            'capital' => 'Session Test Capital',
            'region' => 'Europe',
            'flag_url' => 'https://example.com/flag.png'
        ]);
        $country->save();

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            ['correct_answer' => 'Session Test', 'options' => []]
        );
        $question->save();
        $this->testQuestionId = $question->getId();
    }

    public function testCreateSession()
    {
        $session = QuizSession::createFromAnswer(
            $this->testUserId,
            $this->testQuestionId,
            'Session Test',
            true
        );

        $result = $session->save();
        $this->assertTrue($result);
        $this->assertNotNull($session->getId());
    }

    public function testGetUserHistory()
    {
        for ($i = 1; $i <= 3; $i++) {
            $session = QuizSession::createFromAnswer(
                $this->testUserId,
                $this->testQuestionId,
                'Answer ' . $i,
                $i % 2 == 0
            );
            $session->save();
        }

        $history = QuizSession::getUserHistory($this->testUserId, 10);
        $this->assertNotEmpty($history);
        $this->assertLessThanOrEqual(10, count($history));
    }

    public function testGetUserStats()
    {
        $db = Database::getConnection();
        $db->exec("DELETE FROM quiz_sessions WHERE user_id = {$this->testUserId}");

        $session1 = QuizSession::createFromAnswer($this->testUserId, $this->testQuestionId, 'Answer 1', true);
        $session1->save();

        $session2 = QuizSession::createFromAnswer($this->testUserId, $this->testQuestionId, 'Answer 2', false);
        $session2->save();

        $session3 = QuizSession::createFromAnswer($this->testUserId, $this->testQuestionId, 'Answer 3', true);
        $session3->save();

        $stats = QuizSession::getUserStats($this->testUserId);
        $this->assertArrayHasKey('total_answers', $stats);
        $this->assertArrayHasKey('correct_answers', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
        $this->assertEquals(3, $stats['total_answers']);
        $this->assertEquals(2, $stats['correct_answers']);
    }

    public function testGetUserStatsByType()
    {
        $stats = QuizSession::getUserStatsByType($this->testUserId);
        $this->assertIsArray($stats);
    }

    public function testCleanHistoryOlderThan()
    {
        $deleted = QuizSession::cleanHistoryOlderThan(1);
        $this->assertIsInt($deleted);
    }
}
