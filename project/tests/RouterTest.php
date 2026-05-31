<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Router;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    }

    public function testRouterCanBeInstantiated(): void
    {
        $router = new Router('GET', '/');
        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHasRoutes(): void
    {
        $router = new Router('GET', '/');
        $routes = $router->getRoutes();
        $this->assertIsArray($routes);
    }

    public function testCreateRequestReturnsServerRequest(): void
    {
        $router = new Router('GET', '/');
        $request = $router->createRequest();
        $this->assertInstanceOf(\Nyholm\Psr7\ServerRequest::class, $request);
    }

    public function testCreateRequestWithParams(): void
    {
        $router = new Router('GET', '/');
        $request = $router->createRequest(['id' => 1]);
        $this->assertEquals(['id' => 1], $request->getAttribute('routeParams'));
    }

    public function testGetRoutesReturnsArray(): void
    {
        $router = new Router('GET', '/');
        $routes = $router->getRoutes();
        $this->assertIsArray($routes);
    }

    public function testConstructorSetsRequestMethod(): void
    {
        $router = new Router('POST', '/test');
        $this->assertEquals('POST', $router->getRequestMethod());
    }

    public function testCreateErrorResponseReturnsResponse(): void
    {
        $router = new Router('GET', '/');
        $response = $router->createErrorResponsePublic(404, 'Not Found');

        $this->assertInstanceOf(\Nyholm\Psr7\Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDispatchWithInvalidRouteReturns404(): void
    {
        $router = new Router('GET', '/invalid-route-that-does-not-exist');
        $response = $router->dispatch();

        $this->assertEquals(404, $response->getStatusCode());
    }
}
