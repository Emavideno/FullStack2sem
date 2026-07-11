<?php

namespace App\Services;

use App\Models\QuizQuestion;
use App\Models\QuizSession;
use App\Models\UserStat;

class QuizService
{
    private const QUESTIONS_PER_GAME = 10;

    public function getRandomQuestion(string $type, array $excludeIds = [], ?string $region = null): ?array
    {
        $question = null;

        if ($type === 'mixed' && $region) {
            $question = QuizQuestion::getRandomQuestionByRegion($region, $excludeIds);
        } elseif ($region) {
            $countries = \App\Models\Country::findByRegion($region);
            $countryIds = array_column($countries, 'id');
            if (empty($countryIds)) {
                return null;
            }
            $question = QuizQuestion::getRandomQuestionByCountries($type, $countryIds, $excludeIds);
        } else {
            $question = QuizQuestion::getRandomQuestion($type, $excludeIds);
        }

        if (!$question) {
            return null;
        }

        $questionData = $question->getQuestionData();

        return [
            'id' => $question->getId(),
            'type' => $question->getType(),
            'type_label' => QuizQuestion::getTypeLabel($question->getType()),
            'question_data' => $questionData,
            'options' => $questionData['options'] ?? [],
            'correct_answer' => $questionData['correct_answer'] ?? null,
        ];
    }

    public function checkAnswer(int $userId, int $questionId, string $userAnswer): array
    {
        $question = QuizQuestion::findById($questionId);
        if (!$question) {
            return ['success' => false, 'message' => 'Вопрос не найден'];
        }

        $questionData = $question->getQuestionData();
        $correctAnswer = $questionData['correct_answer'] ?? '';

        if (empty($correctAnswer)) {
            $country = $question->getCountry();
            if ($country) {
                $correctAnswer = $country->getName();
            }
        }

        $isCorrect = $userAnswer === $correctAnswer;

        $session = QuizSession::createFromAnswer($userId, $questionId, $userAnswer, $isCorrect);
        $session->save();

        return [
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'question_id' => $questionId,
        ];
    }

    public function getUserStats(int $userId): array
    {
        return QuizSession::getUserStats($userId);
    }

    public function getUserStatsByType(int $userId): array
    {
        return QuizSession::getUserStatsByType($userId);
    }

    public function getUserHistory(int $userId, int $limit = 50): array
    {
        return QuizSession::getUserHistory($userId, $limit);
    }

    public function getAvailableQuestionTypes(): array
    {
        $types = QuizQuestion::getTypes();
        $result = [];

        foreach ($types as $type) {
            $count = QuizQuestion::getCountByType($type);
            $result[$type] = [
                'label' => QuizQuestion::getTypeLabel($type),
                'count' => $count,
            ];
        }

        return $result;
    }
}
