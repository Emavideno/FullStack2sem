<?php

namespace App\Console;

use App\Services\CountryApiService;
use App\Services\QuestionGeneratorService;
use App\Logger\LoggerFactory;

class ImportCountriesCommand
{
    private bool $force = false;

    public function __construct(bool $force = false)
    {
        $this->force = $force;
    }

    public function execute(array $args = []): void
    {
        if (in_array('--force', $args)) {
            $this->force = true;
        }

        echo "Starting country import...\n\n";

        $logger = LoggerFactory::create();
        $service = new CountryApiService();

        if (!$this->force && !$service->needsUpdate()) {
            $lastUpdate = $service->getLastUpdateInfo();
            $lastUpdateTime = $lastUpdate
                ? date('d.m.Y H:i', strtotime($lastUpdate['created_at']))
                : 'unknown';
            echo "Data is up to date (updated less than 24 hours ago).\n";
            echo "Last update: {$lastUpdateTime}\n";
            echo "For forced update use: php import.php --force\n";
            return;
        }

        if ($this->force) {
            echo "Forced update (--force)\n";
        } else {
            echo "Data is outdated (more than 24 hours). Starting import...\n";
        }

        try {
            $result = $service->importCountries();

            echo "\nImport results:\n";
            echo "─────────────────────\n";
            echo "Total countries:  {$result['total']}\n";
            echo "Imported:         {$result['imported']}\n";
            echo "Errors:           {$result['errors']}\n";

            if (!empty($result['error_details'])) {
                echo "\nCountries with errors:\n";
                foreach ($result['error_details'] as $error) {
                    echo "  - {$error['name']}: {$error['reason']}\n";
                }
            }

            if ($result['imported'] > 0) {
                echo "\nSample imported countries:\n";
                $sample = array_slice($result['details'], 0, 5);
                foreach ($sample as $item) {
                    if ($item['status'] === 'success') {
                        echo "  - {$item['name']} ({$item['region']})\n";
                    }
                }

                echo "\nGenerating questions for imported countries...\n";
                $generator = new QuestionGeneratorService();
                $questionResult = $generator->generateAllQuestions();
                echo "Generated {$questionResult['total']} questions for {$questionResult['countries']} countries\n";
            }

            echo "\nImport completed successfully!\n";
        } catch (\Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
            $logger->error('Import command failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            exit(1);
        }
    }
}
