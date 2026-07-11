<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\QuizQuestion;
use App\Models\Country;
use App\Database\Database;

class QuizQuestionTest extends TestCase
{
    private int $testCountryId;

    protected function setUp(): void
    {
        parent::setUp();
        
        $country = new Country([
            'name' => 'Question Test Country',
            'capital' => 'Question Test Capital',
            'region' => 'Europe',
            'flag_url' => 'https://example.com/flag.png'
        ]);
        $country->save();
        $this->testCountryId = $country->getId();
    }

    public function testCreateQuestion()
    {
        $question = new QuizQuestion(
            $this->testCountryId,
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            [
                'correct_answer' => 'Test Country',
                'options' => ['Option 1', 'Option 2'],
                'question_text' => 'Test question?'
            ]
        );
        
        $result = $question->save();
        $this->assertTrue($result);
        $this->assertNotNull($question->getId());
    }

    public function testFindById()
    {
        $question = new QuizQuestion(
            $this->testCountryId,
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            ['correct_answer' => 'Test', 'options' => []]
        );
        $question->save();
        
        $found = QuizQuestion::findById($question->getId());
        $this->assertNotNull($found);
        $this->assertEquals($question->getId(), $found->getId());
    }

    public function testGetRandomQuestion()
    {
        for ($i = 1; $i <= 5; $i++) {
            $q = new QuizQuestion(
                $this->testCountryId,
                QuizQuestion::TYPE_FLAG_TO_COUNTRY,
                ['correct_answer' => 'Test ' . $i, 'options' => []]
            );
            $q->save();
        }
        
        $question = QuizQuestion::getRandomQuestion(QuizQuestion::TYPE_FLAG_TO_COUNTRY);
        $this->assertNotNull($question);
        $this->assertEquals(QuizQuestion::TYPE_FLAG_TO_COUNTRY, $question->getType());
    }

    public function testGetCountByType()
    {
        $count = QuizQuestion::getCountByType(QuizQuestion::TYPE_FLAG_TO_COUNTRY);
        $this->assertIsInt($count);
    }

    public function testGetTotalCount()
    {
        $total = QuizQuestion::getTotalCount();
        $this->assertIsInt($total);
    }

    public function testGetCorrectAnswer()
    {
        $questionData = ['correct_answer' => 'Correct Answer', 'options' => []];
        $question = new QuizQuestion(
            $this->testCountryId,
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            $questionData
        );
        $question->save();
        
        $this->assertEquals('Correct Answer', $question->getCorrectAnswer());
    }

    public function testGetOptions()
    {
        $options = ['Option A', 'Option B', 'Option C'];
        $question = new QuizQuestion(
            $this->testCountryId,
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            ['correct_answer' => 'A', 'options' => $options]
        );
        $question->save();
        
        $this->assertEquals($options, $question->getOptions());
    }

    public function testGetCountry()
    {
        $question = new QuizQuestion(
            $this->testCountryId,
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            ['correct_answer' => 'Test', 'options' => []]
        );
        $question->save();
        
        $country = $question->getCountry();
        $this->assertNotNull($country);
        $this->assertEquals($this->testCountryId, $country->getId());
    }

    public function testDeleteAll()
    {
        for ($i = 1; $i <= 3; $i++) {
            $q = new QuizQuestion(
                $this->testCountryId,
                QuizQuestion::TYPE_FLAG_TO_COUNTRY,
                ['correct_answer' => 'Test ' . $i, 'options' => []]
            );
            $q->save();
        }
        
        $countBefore = QuizQuestion::getTotalCount();
        $this->assertGreaterThan(0, $countBefore);
        
        QuizQuestion::deleteAll();
        $countAfter = QuizQuestion::getTotalCount();
        $this->assertEquals(0, $countAfter);
    }

    public function testGetTypeLabel()
    {
        $this->assertEquals('Угадай страну по флагу', QuizQuestion::getTypeLabel(QuizQuestion::TYPE_FLAG_TO_COUNTRY));
        $this->assertEquals('Угадай флаг по стране', QuizQuestion::getTypeLabel(QuizQuestion::TYPE_COUNTRY_TO_FLAG));
        $this->assertEquals('Угадай страну по столице', QuizQuestion::getTypeLabel(QuizQuestion::TYPE_CAPITAL_TO_COUNTRY));
    }
}
