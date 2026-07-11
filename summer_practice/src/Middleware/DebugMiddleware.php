<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Logger\LoggerFactory;

class DebugMiddleware implements MiddlewareInterface
{
    private bool $debug;
    private array $excludedPaths = ['/css/', '/js/', '/fonts/', '/svg/', '/favicon.ico'];

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Если отладка выключена - просто пропускаем
        if (!$this->debug) {
            return $handler->handle($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Пропускаем статику
        $path = parse_url($uri, PHP_URL_PATH) ?? '';
        foreach ($this->excludedPaths as $excluded) {
            if (strpos($path, $excluded) === 0) {
                return $handler->handle($request);
            }
        }

        $logger = LoggerFactory::create('debug');

        // Логируем запрос
        $logger->debug('Request started', [
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'query' => $request->getQueryParams(),
            'body' => $request->getParsedBody(),
        ]);

        try {
            $response = $handler->handle($request);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $memoryUsed = round((memory_get_usage() - $startMemory) / 1024, 2);

            // Логируем ответ
            $logger->debug('Request completed', [
                'method' => $method,
                'uri' => $uri,
                'status' => $response->getStatusCode(),
                'execution_time_ms' => $executionTime,
                'memory_used_kb' => $memoryUsed,
            ]);

            return $response;
        } catch (\Exception $e) {
            $logger->error('Request failed', [
                'method' => $method,
                'uri' => $uri,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
