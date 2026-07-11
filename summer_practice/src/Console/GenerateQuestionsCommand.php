<?php

namespace App\Console;

use App\Services\QuestionGeneratorService;
use App\Logger\LoggerFactory;

class GenerateQuestionsCommand
{
    public function execute(array $args = []): void
    {
        echo "Generating quiz questions...\n\n";

        $logger = LoggerFactory::create();

        try {
            $generator = new QuestionGeneratorService();
            $result = $generator->generateAllQuestions();

            echo "Generated {$result['total']} questions for {$result['countries']} countries\n";

            $logger->info('Questions generated', $result);
        } catch (\Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
            $logger->error('Generate questions command failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            exit(1);
        }
    }
}
