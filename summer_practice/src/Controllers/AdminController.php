<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Models\User;
use App\Models\Country;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use App\Services\CountryApiService;
use App\Services\QuestionGeneratorService;
use Psr\Http\Message\ServerRequestInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', 'GET')]
    public function dashboard(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/');
            return;
        }

        $users = User::findAll();
        $userCount = count($users);
        $countryCount = Country::getCount();
        $questionCount = QuizQuestion::getTotalCount();

        $apiService = new CountryApiService();
        $lastUpdate = $apiService->getLastUpdateInfo();
        $needsUpdate = $apiService->needsUpdate();

        $this->render('admin/dashboard', [
            'user_count' => $userCount,
            'users' => $users,
            'country_count' => $countryCount,
            'question_count' => $questionCount,
            'last_update' => $lastUpdate,
            'needs_update' => $needsUpdate,
        ]);
    }

    #[Route('/admin/users', 'GET')]
    public function users(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/');
            return;
        }

        $users = User::findAll();
        $userStats = [];
        $typeLabels = [
            'flag_to_country' => 'По флагу',
            'country_to_flag' => 'Флаг по стране',
            'capital_to_country' => 'Страна по столице',
            'country_to_capital' => 'Столица по стране',
            'population' => 'По населению',
            'area' => 'По площади',
        ];

        foreach ($users as $user) {
            $userId = $user['id'];

            // Общая статистика
            $totalStats = QuizSession::getUserStats($userId);

            // Статистика по типам
            $statsByType = QuizSession::getUserStatsByType($userId);

            // История (последние 5 ответов)
            $history = QuizSession::getUserHistory($userId, 5);

            $userStats[$userId] = [
                'total_answers' => $totalStats['total_answers'] ?? 0,
                'correct_answers' => $totalStats['correct_answers'] ?? 0,
                'success_rate' => $totalStats['success_rate'] ?? 0,
                'types' => $statsByType,
                'history' => $history,
            ];
        }

        $this->render('admin/users', [
            'users' => $users,
            'userStats' => $userStats,
            'type_labels' => $typeLabels,
        ]);
    }

    #[Route('/admin/users/block', 'POST')]
    public function blockUser(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->jsonResponse(['error' => 'Доступ запрещён'], 403);
            return;
        }

        // Проверяем CSRF-токен
        $postData = $request->getParsedBody();
        $token = $postData['csrf_token'] ?? '';
        if ($token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->jsonResponse(['error' => 'Ошибка CSRF: неверный токен'], 403);
            return;
        }

        $userId = (int) ($postData['user_id'] ?? 0);

        if (!$userId) {
            $this->jsonResponse(['error' => 'Неверный ID пользователя'], 400);
            return;
        }

        $user = User::findById($userId);
        if (!$user) {
            $this->jsonResponse(['error' => 'Пользователь не найден'], 404);
            return;
        }

        if ($userId === (int) $_SESSION['user_id']) {
            $this->jsonResponse(['error' => 'Нельзя заблокировать себя'], 400);
            return;
        }

        $user->block();
        $this->jsonResponse(['success' => true, 'message' => 'Пользователь заблокирован']);
    }

    #[Route('/admin/users/unblock', 'POST')]
    public function unblockUser(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->jsonResponse(['error' => 'Доступ запрещён'], 403);
            return;
        }

        // Проверяем CSRF-токен
        $postData = $request->getParsedBody();
        $token = $postData['csrf_token'] ?? '';
        if ($token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->jsonResponse(['error' => 'Ошибка CSRF: неверный токен'], 403);
            return;
        }

        $userId = (int) ($postData['user_id'] ?? 0);

        if (!$userId) {
            $this->jsonResponse(['error' => 'Неверный ID пользователя'], 400);
            return;
        }

        $user = User::findById($userId);
        if (!$user) {
            $this->jsonResponse(['error' => 'Пользователь не найден'], 404);
            return;
        }

        $user->unblock();
        $this->jsonResponse(['success' => true, 'message' => 'Пользователь разблокирован']);
    }

    #[Route('/admin/update', 'POST')]
    public function updateData(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->jsonResponse(['error' => 'Доступ запрещён'], 403);
            return;
        }

        $apiService = new CountryApiService();

        // Проверяем, нужно ли обновление
        if (!$apiService->needsUpdate()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Данные актуальны (обновлены менее 24 часов назад). Обновление недоступно.',
                'last_update' => $apiService->getLastUpdateInfo()
            ], 400);
            return;
        }

        try {
            $result = $apiService->importCountries();

            // Автоматически перегенерируем вопросы после импорта
            $generator = new QuestionGeneratorService();
            $questionResult = $generator->generateAllQuestions();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Данные обновлены!',
                'imported' => $result['imported'],
                'errors' => $result['errors'],
                'total' => $result['total'],
                'questions_generated' => $questionResult['total'],
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/admin/stats', 'GET')]
    public function stats(ServerRequestInterface $request): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/');
            return;
        }

        $apiService = new CountryApiService();
        $lastUpdate = $apiService->getLastUpdateInfo();

        $this->render('admin/stats', [
            'country_count' => Country::getCount(),
            'question_count' => QuizQuestion::getTotalCount(),
            'user_count' => count(User::findAll()),
            'last_update' => $lastUpdate,
        ]);
    }
}
