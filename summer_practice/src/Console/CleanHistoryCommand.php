<?php

namespace App\Console;

use App\Models\QuizSession;
use App\Logger\LoggerFactory;

class CleanHistoryCommand
{
    public function execute(array $args = []): void
    {
        $days = $args[0] ?? 30;
        $days = (int) $days;

        echo "Cleaning quiz history older than {$days} days...\n\n";

        $logger = LoggerFactory::create();

        try {
            $deleted = QuizSession::cleanHistoryOlderThan($days);

            echo "Deleted {$deleted} records\n";

            $logger->info('History cleaned', [
                'days' => $days,
                'deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
            $logger->error('Clean history command failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            exit(1);
        }
    }
}
