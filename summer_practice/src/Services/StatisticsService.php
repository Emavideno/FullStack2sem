<?php

namespace App\Services;

use App\Models\QuizSession;
use App\Models\UserStat;
use App\Models\Country;

class StatisticsService
{
    public function getUserWeakRegions(int $userId): array
    {
        $user = \App\Models\User::findById($userId);
        if ($user && $user->isBlocked()) {
            return [];
        }

        $stats = UserStat::getUserStats($userId);
        $grouped = [];

        foreach ($stats as $stat) {
            $region = $stat['region'];

            if (!isset($grouped[$region])) {
                $grouped[$region] = [
                    'total_attempts' => 0,
                    'correct_attempts' => 0,
                ];
            }

            $grouped[$region]['total_attempts'] += $stat['total_attempts'];
            $grouped[$region]['correct_attempts'] += $stat['correct_attempts'];
        }

        $weakRegions = [];
        foreach ($grouped as $region => $data) {
            $rate = $data['total_attempts'] > 0
                ? ($data['correct_attempts'] / $data['total_attempts']) * 100
                : 0;

            if ($rate < 50) {
                $weakRegions[] = [
                    'region' => $region,
                    'success_rate' => round($rate, 2),
                    'total_attempts' => $data['total_attempts'],
                    'correct_attempts' => $data['correct_attempts'],
                ];
            }
        }

        usort($weakRegions, function ($a, $b) {
            return $a['success_rate'] <=> $b['success_rate'];
        });

        return $weakRegions;
    }

    public function getUserStrongRegions(int $userId): array
    {
        $user = \App\Models\User::findById($userId);
        if ($user && $user->isBlocked()) {
            return [];
        }

        $stats = UserStat::getUserStats($userId);
        $grouped = [];

        foreach ($stats as $stat) {
            $region = $stat['region'];

            if (!isset($grouped[$region])) {
                $grouped[$region] = [
                    'total_attempts' => 0,
                    'correct_attempts' => 0,
                ];
            }

            $grouped[$region]['total_attempts'] += $stat['total_attempts'];
            $grouped[$region]['correct_attempts'] += $stat['correct_attempts'];
        }

        $strongRegions = [];
        foreach ($grouped as $region => $data) {
            $rate = $data['total_attempts'] > 0
                ? ($data['correct_attempts'] / $data['total_attempts']) * 100
                : 0;

            if ($rate >= 50) {
                $strongRegions[] = [
                    'region' => $region,
                    'success_rate' => round($rate, 2),
                    'total_attempts' => $data['total_attempts'],
                    'correct_attempts' => $data['correct_attempts'],
                ];
            }
        }

        usort($strongRegions, function ($a, $b) {
            return $b['success_rate'] <=> $a['success_rate'];
        });

        return $strongRegions;
    }

    public function getRegionStats(int $userId): array
    {
        $user = \App\Models\User::findById($userId);
        if ($user && $user->isBlocked()) {
            return [];
        }

        $stats = UserStat::getUserStats($userId);
        $grouped = [];

        foreach ($stats as $stat) {
            $region = $stat['region'];

            if (!isset($grouped[$region])) {
                $grouped[$region] = [
                    'total_attempts' => 0,
                    'correct_attempts' => 0,
                ];
            }

            $grouped[$region]['total_attempts'] += $stat['total_attempts'];
            $grouped[$region]['correct_attempts'] += $stat['correct_attempts'];
        }

        $result = [];
        foreach ($grouped as $region => $data) {
            $rate = $data['total_attempts'] > 0
                ? ($data['correct_attempts'] / $data['total_attempts']) * 100
                : 0;

            $result[] = [
                'region' => $region,
                'total_attempts' => $data['total_attempts'],
                'correct_attempts' => $data['correct_attempts'],
                'success_rate' => round($rate, 2),
            ];
        }

        usort($result, function ($a, $b) {
            return $b['success_rate'] <=> $a['success_rate'];
        });

        return $result;
    }

    public function getGlobalLeaderboard(?string $type = null, int $limit = 10): array
    {
        return QuizSession::getGlobalLeaderboard($type, $limit);
    }

    public function getUserOverallStats(int $userId): array
    {
        $user = \App\Models\User::findById($userId);
        if ($user && $user->isBlocked()) {
            return [
                'total_questions_answered' => 0,
                'correct_answers' => 0,
                'total_answers' => 0,
                'overall_rate' => 0,
                'types_played' => 0,
            ];
        }

        $stats = QuizSession::getUserStats($userId);
        $statsByType = QuizSession::getUserStatsByType($userId);
        $typesPlayed = \count($statsByType);

        return [
            'total_questions_answered' => $stats['total_answers'] ?? 0,
            'correct_answers' => $stats['correct_answers'] ?? 0,
            'total_answers' => $stats['total_answers'] ?? 0,
            'overall_rate' => $stats['success_rate'] ?? 0,
            'types_played' => $typesPlayed,
        ];
    }

    public function getAllRegionsStats(): array
    {
        return Country::getRegionsStats();
    }
}
