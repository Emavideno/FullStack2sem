<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Models\User;
use App\Services\StatisticsService;
use App\Services\QuizService;
use Psr\Http\Message\ServerRequestInterface;

class StatisticsController extends AbstractController
{
    private StatisticsService $statisticsService;
    private QuizService $quizService;

    public function __construct()
    {
        $this->statisticsService = new StatisticsService();
        $this->quizService = new QuizService();
    }
    #[Route('/stats', 'GET')]
    public function index(ServerRequestInterface $request): void
    {

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        // Общая статистика
        $overall = $this->statisticsService->getUserOverallStats($userId);
        $statsByType = $this->quizService->getUserStatsByType($userId);

        // Регионы
        $weakRegions = $this->statisticsService->getUserWeakRegions($userId);
        $strongRegions = $this->statisticsService->getUserStrongRegions($userId);
        $regionStats = $this->statisticsService->getRegionStats($userId);

        // История
        $history = $this->quizService->getUserHistory($userId, 20);

        // Глобальный топ
        $leaderboard = $this->statisticsService->getGlobalLeaderboard(null, 10);

        $this->render('statistics/index', [
            'overall' => $overall,
            'stats_by_type' => $statsByType,
            'weak_regions' => $weakRegions,
            'strong_regions' => $strongRegions,
            'region_stats' => $regionStats,
            'history' => $history,
            'leaderboard' => $leaderboard,
            'type_labels' => [
                'flag_to_country' => 'По флагу',
                'country_to_flag' => 'Флаг по стране',
                'capital_to_country' => 'Страна по столице',
                'country_to_capital' => 'Столица по стране',
                'population' => 'По населению',
                'area' => 'По площади',
            ]
        ]);
    }
    #[Route('/stats/leaderboard', 'GET')]

    public function leaderboard(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $type = $this->getParam($request, 'type', null);
        if ($type === '' || $type === 'all') {
            $type = null;
        }

        $leaderboard = $this->statisticsService->getGlobalLeaderboard($type, 20);

        // Добавляем текущего пользователя, если его нет в списке
        $userInList = false;
        foreach ($leaderboard as $player) {
            if (isset($player['user_id']) && $player['user_id'] == $userId) {
                $userInList = true;
                break;
            }
        }

        if (!$userInList) {
            $user = User::findById($userId);
            if ($user) {
                $leaderboard[] = [
                    'user_id' => $user->getId(),
                    'login' => $user->getLogin(),
                    'total_answers' => 0,
                    'correct_answers' => 0,
                    'success_rate' => 0,
                ];
            }
        }

        // Сортируем по проценту и количеству ответов
        usort($leaderboard, function ($a, $b) {
            if ($a['success_rate'] != $b['success_rate']) {
                return $b['success_rate'] <=> $a['success_rate'];
            }
            return $b['total_answers'] <=> $a['total_answers'];
        });

        $this->render('statistics/leaderboard', [
            'leaderboard' => $leaderboard,
            'current_type' => $type,
            'type_labels' => [
                'flag_to_country' => 'По флагу',
                'country_to_flag' => 'Флаг по стране',
                'capital_to_country' => 'Страна по столице',
                'country_to_capital' => 'Столица по стране',
                'population' => 'По населению',
                'area' => 'По площади',
            ]
        ]);
    }

    #[Route('/stats/regions', 'GET')]
    public function regions(ServerRequestInterface $request): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $regionStats = $this->statisticsService->getRegionStats($userId);
        $allRegionsStats = $this->statisticsService->getAllRegionsStats();

        $this->render('statistics/regions', [
            'region_stats' => $regionStats,
            'all_regions_stats' => $allRegionsStats,
        ]);
    }
}
