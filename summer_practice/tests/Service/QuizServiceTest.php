<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\QuizService;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use App\Database\Database;

class QuizServiceTest extends TestCase
{
    private QuizService $quizService;
    private int $testUserId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quizService = new QuizService();
        
        $db = Database::getConnection();
        $db->exec("INSERT OR IGNORE INTO users (id, login, password, role) VALUES (1, 'testuser', 'password', 'user')");
        
        $count = QuizQuestion::getTotalCount();
        if ($count === 0) {
            $this->markTestSkipped('No questions in database. Run generate-questions.php first.');
        }
    }

    public function testGetRandomQuestionReturnsQuestion()
    {
        $question = $this->quizService->getRandomQuestion('flag_to_country');
        
        $this->assertNotNull($question, 'No question returned');
        $this->assertArrayHasKey('id', $question);
        $this->assertArrayHasKey('type', $question);
        $this->assertArrayHasKey('question_data', $question);
        $this->assertArrayHasKey('options', $question);
    }

    public function testGetRandomQuestionWithExcludeIds()
    {
        $firstQuestion = $this->quizService->getRandomQuestion('flag_to_country');
        $this->assertNotNull($firstQuestion, 'First question is null');
        
        $secondQuestion = $this->quizService->getRandomQuestion('flag_to_country', [$firstQuestion['id']]);
        $this->assertNotNull($secondQuestion, 'Second question is null');
        
        $this->assertNotEquals($firstQuestion['id'], $secondQuestion['id']);
    }

    public function testGetRandomQuestionReturnsNullWhenNoQuestions()
    {
        $question = $this->quizService->getRandomQuestion('non_existent_type');
        $this->assertTrue($question === null || is_array($question));
    }

    public function testCheckAnswer()
    {
        $question = $this->quizService->getRandomQuestion('flag_to_country');
        $this->assertNotNull($question, 'No question available for test');
        
        $correctAnswer = $question['correct_answer'] ?? 'Unknown';
        $result = $this->quizService->checkAnswer(
            $this->testUserId,
            $question['id'],
            $correctAnswer
        );
        
        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_correct']);
        $this->assertEquals($correctAnswer, $result['correct_answer']);
        
        $result = $this->quizService->checkAnswer(
            $this->testUserId,
            $question['id'],
            'WrongAnswer'
        );
        
        $this->assertTrue($result['success']);
        $this->assertFalse($result['is_correct']);
    }

    public function testGetUserStats()
    {
        $stats = $this->quizService->getUserStats($this->testUserId);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_answers', $stats);
        $this->assertArrayHasKey('correct_answers', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
    }

    public function testGetUserStatsByType()
    {
        $stats = $this->quizService->getUserStatsByType($this->testUserId);
        
        $this->assertIsArray($stats);
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('type', $stat);
            $this->assertArrayHasKey('total', $stat);
            $this->assertArrayHasKey('correct', $stat);
            $this->assertArrayHasKey('rate', $stat);
        }
    }

    public function testGetAvailableQuestionTypes()
    {
        $types = $this->quizService->getAvailableQuestionTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('flag_to_country', $types);
        $this->assertArrayHasKey('country_to_flag', $types);
        $this->assertArrayHasKey('capital_to_country', $types);
        $this->assertArrayHasKey('country_to_capital', $types);
        $this->assertArrayHasKey('population', $types);
        $this->assertArrayHasKey('area', $types);
        
        foreach ($types as $type) {
            $this->assertArrayHasKey('label', $type);
            $this->assertArrayHasKey('count', $type);
            $this->assertIsInt($type['count']);
        }
    }
}
