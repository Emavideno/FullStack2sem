<?php

namespace App\Console;

use App\Models\User;
use App\Logger\LoggerFactory;

class CreateAdminCommand
{
    public function execute(array $args = []): void
    {
        $login = $args[0] ?? null;
        $password = $args[1] ?? null;

        if (!$login || !$password) {
            echo "Usage: php create-admin.php <login> <password>\n";
            echo "Example: php create-admin.php admin secret123\n";
            exit(1);
        }

        echo "Creating admin user...\n\n";

        $logger = LoggerFactory::create();

        try {
            // Проверяем, существует ли пользователь
            $existing = User::findByLogin($login);
            if ($existing) {
                echo "User '{$login}' already exists\n";
                exit(1);
            }

            $user = new User($login, $password, 'admin');
            if ($user->save()) {
                echo "Admin user '{$login}' created successfully!\n";
                $logger->info('Admin user created', ['login' => $login]);
            } else {
                echo "Failed to create admin user\n";
                exit(1);
            }
        } catch (\Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
            $logger->error('Create admin command failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            exit(1);
        }
    }
}
