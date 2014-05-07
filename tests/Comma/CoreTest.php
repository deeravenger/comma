<?php

class CoreTest extends PHPUnit_Framework_TestCase
{
    public function testStandardPackage()
    {
        $app = new \Comma\Core();
        $this->assertTrue($app['router'] instanceof \Comma\Router);
        $this->assertTrue($app['request'] instanceof \Comma\Request);
        $this->assertTrue($app->response() instanceof \Comma\Response);
        $this->assertTrue($app->view('fake/path') instanceof \Comma\View);
        $this->assertTrue($app->route('/', 'CoreTestCallable::__invoke') instanceof \Comma\Route);
    }

    public function testRun()
    {
        $expected = 'Result!';
        $app = new \Comma\Core();
        $app->route('/', function () use ($expected) {
            return $expected;
        });

        ob_start();
        $app->run('/');
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($expected, $actual);
    }

    public function testOverride()
    {
        $routerClass = $this->getMock('FakeRouter');
        $requestClass = $this->getMock('FakeRequest');
        $responseClass = $this->getMock('FakeResponse');
        $viewClass = $this->getMock('FakeView');
        $routeClass = $this->getMock('FakeRoute');
        $values = array(
            'router' => new $routerClass(),
            'request' => new $requestClass(),
            'response' => function () use ($responseClass) {
                    return new $responseClass;
                },
            'view' => function () use ($viewClass) {
                    return new $viewClass;
                },
            'route' => function () use ($routeClass) {
                    return new $routeClass;
                }
        );
        $app = new \Comma\Core($values);

        $this->assertTrue($app['router'] instanceof $routerClass);
        $this->assertTrue($app['request'] instanceof $requestClass);
        $this->assertTrue($app->response() instanceof $responseClass);
        $this->assertTrue($app->view('fake/path') instanceof $viewClass);
        $this->assertTrue($app->route('/', 'CoreTestCallable::__invoke') instanceof $routeClass);
    }

    public function testResponse()
    {
        $app = new \Comma\Core();

        $expected = 'windows-1251';
        $response = $app->response($expected);
        $this->assertTrue($response instanceof \Comma\Response);

        $reflection = new \ReflectionClass($response);
        $property = $reflection->getProperty('_charset');
        $property->setAccessible(true);
        $actual = $property->getValue($response);
        $this->assertEquals($actual, $expected);
    }

    public function testView()
    {
        $app = new \Comma\Core();

        $expected = 'path/to/template.php';
        $view = $app->view($expected);
        $this->assertTrue($view instanceof \Comma\View);

        $reflection = new \ReflectionClass($view);
        $property = $reflection->getProperty('_path');
        $property->setAccessible(true);
        $actual = $property->getValue($view);
        $this->assertEquals($actual, $expected);
    }

    public function testRoute()
    {
        $app = new \Comma\Core();

        $expected = 'FakeController::action';
        $route = $app->route('/hello/{name}', $expected);
        $this->assertTrue($route instanceof \Comma\Route);

        $reflection = new \ReflectionClass($route);
        $property = $reflection->getProperty('_controller');
        $property->setAccessible(true);
        $actual = $property->getValue($route);
        $this->assertEquals($actual, $expected);
    }


}
