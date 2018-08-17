<?php

class RouteTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider testRoutePatternProvider
     */
    public function testRoutePattern($pattern, $parts, $expected)
    {
        $route = new \Comma\Route($pattern, 'FakeController');
        if (is_array($parts)) {
            foreach ($parts as $key => $value) {
                $route->assert($key, $value);
            }
        }
        $route->compile();

        $reflection = new \ReflectionClass($route);
        $property = $reflection->getProperty('_compiled');
        $property->setAccessible(true);
        $actual = $property->getValue($route);

        $this->assertEquals($expected, $actual);
    }

    public function testRoutePatternProvider()
    {
        $data = array();
        $data[] = array(
            'pattern' => '/',
            'parts' => array(),
            'expected' => '#^/?(\?.*)?$#'
        );
        $data[] = array(
            'pattern' => '/hello',
            'parts' => array(),
            'expected' => '#^/hello/?(\?.*)?$#'
        );
        $data[] = array(
            'pattern' => '/hello$',
            'parts' => array(),
            'expected' => '#^/hello$#'
        );
        $data[] = array(
            'pattern' => '/hello/(\d+)$',
            'parts' => array(),
            'expected' => '#^/hello/(\d+)$#'
        );
        $data[] = array(
            'pattern' => '^/hello/(\d+)$',
            'parts' => array(),
            'expected' => '#^/hello/(\d+)$#'
        );
        $data[] = array(
            'pattern' => '^/hello/{number}$',
            'parts' => array('number' => '\d+'),
            'expected' => '#^/hello/(\d+)$#'
        );
        $data[] = array(
            'pattern' => '^/hello/(\d+)',
            'parts' => array(),
            'expected' => '#^/hello/(\d+)/?(\?.*)?$#'
        );
        $data[] = array(
            'pattern' => '^/hello/(\w+)/{surname}',
            'parts' => array('surname' => '([a-z]+)'),
            'expected' => '#^/hello/(\w+)/([a-z]+)/?(\?.*)?$#'
        );

        return $data;
    }

    public function testControllerClosure()
    {
        $controller = function () {
            return 1;
        };
        $route = new \Comma\Route('/', $controller);
        $reflection = new \ReflectionClass($route);
        $property = $reflection->getProperty('_controller');
        $property->setAccessible(true);
        $actual = $property->getValue($route);
        $this->assertEquals($controller, $actual);
    }

    public function testControllerClassName()
    {
        $expected = 'FakeController::action';
        $route = new \Comma\Route('/', $expected);
        $reflection = new \ReflectionClass($route);
        $property = $reflection->getProperty('_controller');
        $property->setAccessible(true);
        $actual = $property->getValue($route);
        $this->assertEquals($expected, $actual);
    }

    public function testControllerException()
    {
        $this->setExpectedException('\Comma\Exception');

        $stdClass = new stdClass();
        new \Comma\Route('/', $stdClass);
    }

    public function testRunException()
    {
        $this->setExpectedException('\Comma\Exception');

        $stdClass = new stdClass();
        $route = new \Comma\Route('/', $stdClass);
        $route->handle();
    }

    public function testDependencies()
    {
        $expected = array(
            'name' => 'Darien',
            'surname' => 'Fawkes',
            'role' => 'Invisible man'
        );

        $route = new \Comma\Route('/(\w+)/(\d+)', function () {
        });
        $route
            ->inject('name', $expected['name'])
            ->inject('surname', $expected['surname'])
            ->inject('role', $expected['role']);

        $reflection = new \ReflectionClass($route);
        $property = $reflection->getProperty('_dependencies');
        $property->setAccessible(true);
        $actual = $property->getValue($route);
        $this->assertEquals($expected, $actual);
    }

    public function testRunClosure()
    {
        $nameValue = 'Darien';
        $dependencyValue = array('my_dependency');
        $ageValue = 28;

        $route = new \Comma\Route('/(\w+)/(\d+)$', function ($name, $dependency, $age) {
            return array($name, $dependency, $age);
        });
        $route->inject('dependency', $dependencyValue)
            ->inject('dependency2', 123);

        $route->match(sprintf('/%s/%d', $nameValue, $ageValue));
        $actual = $route->handle();
        $expected = array($nameValue, $dependencyValue, $ageValue);
        $this->assertEquals($expected, $actual);
    }

    public function testRunClosureOptional()
    {
        $nameValue = 'Darien';
        $dependencyValue = array('my_dependency');

        $route = new \Comma\Route('/(\w+)/(\d+)?$', function ($name, $dependency, $age = 28) {
            return array($name, $dependency, $age);
        });
        $route->inject('dependency', $dependencyValue)
            ->inject('dependency2', 123);

        $route->match(sprintf('/%s/', $nameValue));
        $actual = $route->handle();
        $expected = array($nameValue, $dependencyValue, 28);
        $this->assertEquals($expected, $actual);
    }

    public function testRunClosureException()
    {
        $this->setExpectedException('\Comma\Exception');

        $nameValue = 'Darien';
        $dependencyValue = array('my_dependency');
        $ageValue = 28;

        $route = new \Comma\Route('/(\w+)/(\d+)$', function ($name, $dependency, $age, $err) {
            return array($name, $dependency, $age, $err);
        });
        $route->inject('dependency', $dependencyValue)
            ->inject('dependency2', 123);

        $route->match(sprintf('/%s/%d', $nameValue, $ageValue));
        $route->handle();
    }
}
