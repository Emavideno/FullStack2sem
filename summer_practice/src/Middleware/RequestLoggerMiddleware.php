<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    private string $logFile;
    private bool $debug;

    public function __construct(?string $logFile = null, bool $debug = false)
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/requests.log';
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);

        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $logMessage = sprintf(
            "[%s] %s %s | IP: %s\n",
            date('Y-m-d H:i:s'),
            $method,
            $uri,
            $ip
        );

        file_put_contents($this->logFile, $logMessage, FILE_APPEND);

        if ($this->debug) {
            error_log(trim($logMessage));
        }

        $response = $handler->handle($request);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $timeLog = sprintf("  → Выполнено за %s ms\n", $executionTime);
        file_put_contents($this->logFile, $timeLog, FILE_APPEND);

        if ($this->debug) {
            error_log(trim($timeLog));
        }

        return $response;
    }
}
