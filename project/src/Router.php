<?php
namespace App;

class Router
{
    private array $routes = [];
    private string $requestMethod;
    private string $requestUri;

    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Корректировка пути
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($this->requestUri, $basePath) === 0) {
            $this->requestUri = substr($this->requestUri, strlen($basePath));
        }
        $this->requestUri = $this->requestUri ?: '/';

        // Сканируем контроллеры и ищем атрибуты Route
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

                    // Преобразуем /news/{id} в регулярное выражение
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

    public function dispatch(): void
    {
        $uri = rtrim($this->requestUri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        if (!isset($this->routes[$this->requestMethod])) {
            $this->sendNotFound();
            return;
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
                return;
            }
        }

        $this->sendNotFound();
    }

    private function createRequestObject(array $routeParams): array
    {
        return [
            'method' => $this->requestMethod,
            'uri' => $this->requestUri,
            'get' => $_GET,
            'post' => $_POST,
            'body' => file_get_contents('php://input'),
            'routeParams' => $routeParams,
            'server' => $_SERVER
        ];
    }

    private function sendNotFound(): void
    {
        http_response_code(404);
        echo "404 - Страница не найдена";
    }
}