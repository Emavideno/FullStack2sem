<?php

namespace App;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Factory\Psr17Factory;

class Router
{
    private array $routes = [];
    private string $requestMethod;
    private string $requestUri;

    public function __construct(?string $requestMethod = null, ?string $requestUri = null)
    {
        $this->requestMethod = $requestMethod ?? $_SERVER['REQUEST_METHOD'];
        $this->requestUri = $requestUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($this->requestUri, $basePath) === 0) {
            $this->requestUri = substr($this->requestUri, strlen($basePath));
        }
        $this->requestUri = $this->requestUri ?: '/';

        $this->scanControllers();
    }


    private function scanControllers(): void
    {
        $files = glob(__DIR__ . '/Controllers/*.php');

        foreach ($files as $file) {
            $className = 'App\\Controllers\\' . pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            foreach ($reflection->getMethods() as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    /** @var Route $route */
                    $route = $attribute->newInstance();

                    $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $route->path);
                    $pattern = '#^' . $pattern . '$#';

                    $this->routes[$route->method][] = [
                        'pattern' => $pattern,
                        'controller' => $className,
                        'action' => $method->getName(),
                        'originalPath' => $route->path
                    ];
                }
            }
        }
    }

    public function dispatch(): \Nyholm\Psr7\Response
    {
        $uri = rtrim($this->requestUri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        if (!isset($this->routes[$this->requestMethod])) {
            return $this->createErrorResponse(404, '404 - Страница не найдена');
        }

        foreach ($this->routes[$this->requestMethod] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                $controller = new $route['controller']();
                $request = $this->createRequestObject($params);

                $controller->{$route['action']}($request);

                return new \Nyholm\Psr7\Response(200);
            }
        }

        return $this->createErrorResponse(404, '404 - Страница не найдена');
    }

    private function createRequestObject(array $routeParams): ServerRequest
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
        $request = $request->withAttribute('routeParams', $routeParams);
        $request = $request->withQueryParams($_GET);
        $request = $request->withParsedBody($_POST);
        $request = $request->withHeader('Content-Type', $_SERVER['CONTENT_TYPE'] ?? '');

        return $request;
    }

    public function createRequest(array $routeParams = []): ServerRequest
    {
        return $this->createRequestObject($routeParams);
    }

    private function createErrorResponse(int $code, string $message): \Nyholm\Psr7\Response
    {
        http_response_code($code);

        if ($code === 404) {
            $logger = \App\Logger\LoggerFactory::create();
            $logger->warning('404 Not Found', [
                'uri' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }

        return new \Nyholm\Psr7\Response($code, ['Content-Type' => 'text/html'], $message);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function createErrorResponsePublic(int $code, string $message): \Nyholm\Psr7\Response
    {
        return $this->createErrorResponse($code, $message);
    }
}
