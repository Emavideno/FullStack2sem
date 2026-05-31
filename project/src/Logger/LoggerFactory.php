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
            self::$logger->pushHandler(new StreamHandler($logFile, Logger::ERROR));

            $debug = $_ENV['DEBUG'] ?? false;
            if ($debug) {
                self::$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::DEBUG));
            }
        }

        return self::$logger;
    }
}
