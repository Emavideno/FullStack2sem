<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\QuizController;
use App\Services\QuizService;
use App\Models\QuizQuestion;
use App\Models\Country;
use App\Database\Database;

class QuizControllerTest extends TestCase
{
    private QuizController $controller;
    private int $testUserId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new QuizController();
        
        $db = Database::getConnection();
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', 'password', 'user')");
        
        $count = QuizQuestion::getTotalCount();
        if ($count === 0) {
            $this->createTestQuestions();
        }
    }

    private function createTestQuestions(): void
    {
        $db = Database::getConnection();
        
        $db->exec("
            INSERT OR IGNORE INTO countries (id, name, capital, region, flag_url, created_at, updated_at) 
            VALUES (100, 'Test Quiz Country', 'Test Quiz Capital', 'Europe', 'https://example.com/flag.png', datetime('now'), datetime('now'))
        ");
        
        $questionTypes = ['flag_to_country', 'country_to_flag', 'capital_to_country', 'country_to_capital'];
        foreach ($questionTypes as $type) {
            for ($i = 0; $i < 3; $i++) {
                $db->exec("
                    INSERT INTO quiz_questions (country_id, type, question_data, created_at)
                    VALUES (
                        100,
                        '{$type}',
                        '{\"correct_answer\":\"Test Answer {$i}\",\"options\":[\"Option A\",\"Option B\",\"Option C\"],\"question_text\":\"Test question {$i}?\"}',
                        datetime('now')
                    )
                ");
            }
        }
    }

    public function testGetAvailableQuestionTypes()
    {
        $quizService = new QuizService();
        $types = $quizService->getAvailableQuestionTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('flag_to_country', $types);
        $this->assertArrayHasKey('country_to_flag', $types);
        $this->assertArrayHasKey('capital_to_country', $types);
        $this->assertArrayHasKey('country_to_capital', $types);
    }

    public function testGetRandomQuestion()
    {
        $quizService = new QuizService();
        $question = $quizService->getRandomQuestion('flag_to_country');
        
        $this->assertNotNull($question);
        $this->assertArrayHasKey('id', $question);
        $this->assertArrayHasKey('type', $question);
        $this->assertArrayHasKey('question_data', $question);
        $this->assertArrayHasKey('options', $question);
    }

    public function testGetRandomQuestionWithExclude()
    {
        $quizService = new QuizService();
        $firstQuestion = $quizService->getRandomQuestion('flag_to_country');
        $this->assertNotNull($firstQuestion);
        
        $secondQuestion = $quizService->getRandomQuestion('flag_to_country', [$firstQuestion['id']]);
        $this->assertNotNull($secondQuestion);
        $this->assertNotEquals($firstQuestion['id'], $secondQuestion['id']);
    }

    public function testGetRandomQuestionWithRegion()
    {
        $quizService = new QuizService();
        $question = $quizService->getRandomQuestion('flag_to_country', [], 'Europe');
        
        if ($question !== null) {
            $this->assertArrayHasKey('id', $question);
            $this->assertArrayHasKey('type', $question);
        }
    }

    public function testCheckAnswerCorrect()
    {
        $quizService = new QuizService();
        $question = $quizService->getRandomQuestion('flag_to_country');
        $this->assertNotNull($question);
        
        $correctAnswer = $question['correct_answer'] ?? 'Unknown';
        $result = $quizService->checkAnswer(
            $this->testUserId,
            $question['id'],
            $correctAnswer
        );
        
        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_correct']);
        $this->assertEquals($correctAnswer, $result['correct_answer']);
    }

    public function testCheckAnswerIncorrect()
    {
        $quizService = new QuizService();
        $question = $quizService->getRandomQuestion('flag_to_country');
        $this->assertNotNull($question);
        
        $result = $quizService->checkAnswer(
            $this->testUserId,
            $question['id'],
            'WrongAnswer'
        );
        
        $this->assertTrue($result['success']);
        $this->assertFalse($result['is_correct']);
    }

    public function testCheckAnswerQuestionNotFound()
    {
        $quizService = new QuizService();
        $result = $quizService->checkAnswer(
            $this->testUserId,
            99999,
            'Answer'
        );
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Вопрос не найден', $result['message']);
    }

    public function testGetUserStats()
    {
        $quizService = new QuizService();
        $stats = $quizService->getUserStats($this->testUserId);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_answers', $stats);
        $this->assertArrayHasKey('correct_answers', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
    }

    public function testGetUserStatsByType()
    {
        $quizService = new QuizService();
        $stats = $quizService->getUserStatsByType($this->testUserId);
        
        $this->assertIsArray($stats);
    }

    public function testGetUserHistory()
    {
        $quizService = new QuizService();
        $history = $quizService->getUserHistory($this->testUserId, 10);
        
        $this->assertIsArray($history);
        $this->assertLessThanOrEqual(10, count($history));
    }

    public function testMixedModeWithRegion()
    {
        $quizService = new QuizService();
        $question = $quizService->getRandomQuestion('mixed', [], 'Europe');
        
        if ($question !== null) {
            $this->assertArrayHasKey('id', $question);
            $this->assertArrayHasKey('type', $question);
        }
    }
}
