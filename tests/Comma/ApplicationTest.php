<?php
declare(strict_types=1);

use Comma\Application;
use Comma\Container;
use Comma\ContainerInterface;
use Comma\Request;
use Comma\Router;
use Comma\RouterMatcherInterface;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testApplicationPackage()
    {
        $app = new Application();
        $this->assertInstanceOf(Router::class, $app->getRouter());
        $this->assertInstanceOf(RouterMatcherInterface::class, $app->getRouter());
        $this->assertInstanceOf(Request::class, $app->getRequest());
        $this->assertInstanceOf(Container::class, $app->getContainer());
        $this->assertInstanceOf(ContainerInterface::class, $app->getContainer());
    }

    public function testApplicationParameters()
    {
        $parameters = [
            'view.path' => __DIR__,
            'parameter1' => 1
        ];

        $app = new Application($parameters);
        foreach ($parameters as $key => $value) {
            $this->assertEquals($value, $app->getContainer()->getParameter($key));
        }
    }

    public function testRun()
    {
        $expected = 'Hello world';
        $app = new Application();
        $app->route('/', function () use ($expected) {
            return new \Comma\Response\HtmlResponse($expected);
        });

        $app->run('/');
        $this->assertEquals($expected, file_get_contents('php://input'));
    }
}
