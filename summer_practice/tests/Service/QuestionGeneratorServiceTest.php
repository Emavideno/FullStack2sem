<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\QuestionGeneratorService;
use App\Models\Country;
use App\Models\QuizQuestion;
use App\Database\Database;

class QuestionGeneratorServiceTest extends TestCase
{
    private QuestionGeneratorService $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new QuestionGeneratorService();
        
        // Проверяем, что в БД есть страны
        $countries = Country::findAll();
        if (empty($countries)) {
            $this->markTestSkipped('No countries in database. Cannot test question generation.');
        }
    }

    public function testGenerateAllQuestions()
    {
        $result = $this->generator->generateAllQuestions();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('countries', $result);
        $this->assertArrayHasKey('errors', $result);
        
        // Проверяем, что сгенерировались вопросы
        $this->assertGreaterThan(0, $result['total'], 'No questions were generated');
        $this->assertGreaterThan(0, $result['countries'], 'No countries were processed');
    }

    public function testGeneratedQuestionsInDatabase()
    {
        $count = QuizQuestion::getTotalCount();
        $this->assertGreaterThan(0, $count, 'No questions found in database');
        
        // Проверяем, что есть вопросы хотя бы для некоторых типов
        $types = QuizQuestion::getTypes();
        $typesWithQuestions = 0;
        
        foreach ($types as $type) {
            $countByType = QuizQuestion::getCountByType($type);
            if ($countByType > 0) {
                $typesWithQuestions++;
            }
        }
        
        // Проверяем, что есть вопросы хотя бы для 4 типов (из 6)
        $this->assertGreaterThanOrEqual(4, $typesWithQuestions, 'Less than 4 question types have questions');
        
        // Проверяем основные типы
        $mainTypes = ['flag_to_country', 'country_to_flag', 'capital_to_country', 'country_to_capital'];
        foreach ($mainTypes as $type) {
            $this->assertGreaterThan(0, QuizQuestion::getCountByType($type), "No {$type} questions");
        }
    }

    public function testEachCountryHasQuestions()
    {
        $countries = Country::findAll();
        foreach ($countries as $countryData) {
            $country = Country::findById($countryData['id']);
            $this->assertNotNull($country);
            
            // Проверяем, что у каждой страны есть вопросы
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM quiz_questions WHERE country_id = ?");
            $stmt->execute([$country->getId()]);
            $count = $stmt->fetchColumn();
            
            $this->assertGreaterThan(0, $count, "Country {$country->getName()} has no questions");
        }
    }
}
