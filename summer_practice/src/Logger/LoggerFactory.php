<?php

namespace App\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;

class LoggerFactory
{
    private static ?Logger $logger = null;

    public static function create(string $channel = 'app'): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger($channel);

            $logFile = __DIR__ . '/../../logs/app.log';

            // Проверяем, что папка существует
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // Логируем ВСЁ в режиме отладки
            $debug = $_ENV['DEBUG'] ?? false;
            if ($debug) {
                // Все уровни логирования
                self::$logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
            } else {
                // Только ошибки
                self::$logger->pushHandler(new StreamHandler($logFile, Logger::ERROR));
            }
        }

        return self::$logger;
    }
}
