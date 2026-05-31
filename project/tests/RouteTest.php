<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Route;

class RouteTest extends TestCase
{
    public function testRouteAttributeCanBeCreated(): void
    {
        $route = new Route('/test', 'GET');
        $this->assertEquals('/test', $route->path);
        $this->assertEquals('GET', $route->method);
    }

    public function testRouteDefaultMethodIsGet(): void
    {
        $route = new Route('/test');
        $this->assertEquals('GET', $route->method);
    }
}
