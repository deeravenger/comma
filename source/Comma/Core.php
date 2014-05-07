<?php
namespace Comma;

/**
 * Dependency Injection Container
 * @package Comma
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Core extends \Pimple
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $comma = $this;
        if (!isset($this['request'])) {
            $this['request'] = new \Comma\Request();
        }
        if (!isset($this['router'])) {
            $this['router'] = new \Comma\Router();
        }
        if (!isset($this['response'])) {
            $this['response'] = $this->factory(function ($charset = null) {
                return new \Comma\Response($charset);
            });
        }
        if (!isset($this['view'])) {
            $this['view'] = $this->factory(function ($path, array $data = array()) use ($comma) {
                if (isset($comma['view.config'], $comma['view.config']['path']) && !is_file($path)) {
                    $path = rtrim($comma['view.config']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
                }
                return new \Comma\View($path, $data);
            });
        }
        if (!isset($this['route'])) {
            $this['route'] = $this->factory(function ($pattern, $controller) use ($comma) {
                /** @var \Comma\Router $router */
                $router = $comma['router'];
                $route = new \Comma\Route($pattern, $controller);
                $router->append($route);
                return $route;
            });
        }
    }

    /**
     * @param string|null $charset
     * @return \Comma\Response
     */
    public function response($charset = null)
    {
        /** @var $factory callable */
        $factory = $this->raw('response');
        return $factory($charset);
    }

    /**
     * @param string $path
     * @param array $data
     * @return \Comma\View
     */
    public function view($path, array $data = array())
    {
        /** @var $factory callable */
        $factory = $this->raw('view');
        return $factory($path, $data);
    }

    /**
     * @param string $pattern
     * @param callable|string $controller
     * @return \Comma\Route
     */
    public function route($pattern, $controller)
    {
        /** @var $factory callable */
        $factory = $this->raw('route');
        return $factory($pattern, $controller);
    }

    /**
     * Run application
     * @param string $requestedUri
     * @return int
     */
    public function run($requestedUri)
    {
        /** @var \Comma\Router $router */
        $router = $this['router'];
        $route = $router->match($requestedUri);
        $result = $route->run();
        return print($result);
    }
}