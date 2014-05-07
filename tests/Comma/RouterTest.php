<?php

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function testAppend()
    {
        $route1 = new \Comma\Route('/', 'FakeController::fakeMethod1');
        $route2 = new \Comma\Route('/', 'FakeController::fakeMethod2');
        $expected = array(
            $route1,
            $route2
        );

        $router = new \Comma\Router();
        $router->append($route1);
        $router->append($route2);

        $reflection = new \ReflectionClass($router);
        $property = $reflection->getProperty('_routes');
        $property->setAccessible(true);

        $this->assertEquals($expected, $property->getValue($router));
    }


    public function testMatchException()
    {
        $this->setExpectedException('\Comma\Exception\PageNotFound');

        $router = new \Comma\Router();
        $router->match('/');
    }

    public function testMatch()
    {
        $route1 = new \Comma\Route('/', 'FakeController::fakeMethod1');
        $route2 = new \Comma\Route('/fake', 'FakeController::fakeMethod2');

        $router = new \Comma\Router();
        $router->append($route1);
        $router->append($route2);

        $actual = $router->match('/fake');

        $this->assertEquals($route2, $actual);
    }
}
