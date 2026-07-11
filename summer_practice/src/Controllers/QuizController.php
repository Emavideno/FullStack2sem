<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Services\QuizService;
use App\Services\StatisticsService;
use Psr\Http\Message\ServerRequestInterface;

class QuizController extends AbstractController
{
    private QuizService $quizService;
    private StatisticsService $statisticsService;

    public function __construct()
    {
        $this->quizService = new QuizService();
        $this->statisticsService = new StatisticsService();
    }

    #[Route('/quiz', 'GET')]
    public function index(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $types = $this->quizService->getAvailableQuestionTypes();

        $this->render('quiz/index', [
            'types' => $types,
        ]);
    }

    #[Route('/quiz/play', 'GET')]
    public function play(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $type = $this->getParam($request, 'type', 'flag_to_country');
        $region = $this->getParam($request, 'region', null);

        if ($type === 'all') {
            $type = 'mixed';
        }

        $typeLabel = $region
            ? "Смешанный режим: {$region}"
            : \App\Models\QuizQuestion::getTypeLabel($type);

        $this->render('quiz/play', [
            'type' => $type,
            'region' => $region,
            'type_label' => $typeLabel,
        ]);
    }

    #[Route('/api/question', 'GET')]
    public function getQuestion(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->jsonResponse(['error' => 'Не авторизован'], 401);
            return;
        }

        $type = $this->getParam($request, 'type', 'flag_to_country');
        $region = $this->getParam($request, 'region', null);
        $exclude = $this->getParam($request, 'exclude', '');
        $excludeIds = $exclude ? array_map('intval', explode(',', $exclude)) : [];

        $question = $this->quizService->getRandomQuestion($type, $excludeIds, $region);

        if (!$question) {
            $this->jsonResponse(['error' => 'Нет вопросов', 'finished' => true], 404);
            return;
        }

        $this->jsonResponse($question);
    }

    #[Route('/api/answer', 'POST')]
    public function submitAnswer(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->jsonResponse(['error' => 'Не авторизован'], 401);
            return;
        }

        // Получаем данные из POST (FormData)
        $postData = $request->getParsedBody();

        $questionId = (int) ($postData['question_id'] ?? 0);
        $answer = trim($postData['answer'] ?? '');

        if (!$questionId || !$answer) {
            $this->jsonResponse(['error' => 'Неверные данные', 'question_id' => $questionId, 'answer' => $answer], 400);
            return;
        }

        // Проверяем ответ
        $result = $this->quizService->checkAnswer($userId, $questionId, $answer);
        $this->jsonResponse($result);
    }

    #[Route('/quiz/stats', 'GET')]
    public function stats(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $overall = $this->statisticsService->getUserOverallStats($userId);
        $statsByType = $this->quizService->getUserStatsByType($userId);
        $weakRegions = $this->statisticsService->getUserWeakRegions($userId);
        $strongRegions = $this->statisticsService->getUserStrongRegions($userId);
        $regionStats = $this->statisticsService->getRegionStats($userId);
        $history = $this->quizService->getUserHistory($userId, 20);

        $this->render('quiz/stats', [
            'overall' => $overall,
            'stats_by_type' => $statsByType,
            'weak_regions' => $weakRegions,
            'strong_regions' => $strongRegions,
            'region_stats' => $regionStats,
            'history' => $history,
        ]);
    }
}
