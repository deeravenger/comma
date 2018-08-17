<?php
declare(strict_types=1);

namespace Comma;

/**
 * Class Route
 * @package Comma
 */
class Route implements RouteInterface
{
    /**
     * @var callable Controller
     */
    protected $controller;
    /**
     * @var string Regular expression
     */
    protected $path;
    /**
     * @var string Regular expression
     */
    protected $compiled;
    /**
     * @var array Dependency list
     */
    protected $dependencies = [];
    /**
     * @var array
     */
    protected $values;
    /**
     * @var array
     */
    protected $parts = [];

    /**
     * @param string $path
     * @param callable|string $controller Function or string with className
     * @throws Exception
     */
    public function __construct($path, $controller)
    {
        $this->controller = null;
        $this->path = null;
        $this->compiled = null;
        $this->dependencies = [];
        $this->values = null;
        $this->parts = [];
        $this
            ->setPath($path)
            ->setController($controller);
    }

    /**
     * @param string $name
     * @param string $pattern
     * @return Route
     */
    public function assert($name, $pattern): Route
    {
        $this->parts[$name] = sprintf('(%s)', trim($pattern, '()'));

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Route
     */
    public function inject($name, $value): Route
    {
        $this->dependencies[$name] = $value;

        return $this;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function match(string $url): bool
    {
        if (!$this->compiled) {
            $this->compile();
        }
        $result = preg_match($this->compiled, $url, $this->values);
//        preg_match('//(/?#([^)]+)/)/i', $this->_pattern, $this->_keys);

        return (bool)$result;
    }

    /**
     * @throws Exception
     * @return mixed|null
     * @throws \ReflectionException
     */
    public function handle()
    {
        $result = null;
        if (null === $this->values) {
            throw new Exception(sprintf('Route "%s" not matched!', $this->path));
        } else {
            $values = array_slice($this->values, 1);

            $countRequired = 0;
            $countValues = count($values);
            $controller = $this->controller;
            if ($this->controller instanceof \Closure) {
                $func = new \ReflectionFunction($this->controller);
            } elseif (is_string($this->controller)) {
                list($className, $method) = explode('::', $this->controller);
                $reflection = new \ReflectionClass($className);
                $func = $reflection->getMethod($method);
                $controller = [$reflection->newInstance(), $method];
            } else {
                throw new Exception(sprintf('Bad controller for route "%s"!', $this->path));
            }
            $i = 0;
            $params = [];
            foreach ($func->getParameters() as $number => $argument) {
                if (array_key_exists($argument->getName(), $this->dependencies)) {
                    $params[$number] = $this->dependencies[$argument->getName()];
                    continue;
                }
                if (!$argument->isOptional()) {
                    $params[$number] = $values[$i] ?? null;
                    $countRequired++;
                } else {
                    $params[$number] = array_key_exists($i, $values) ? $values[$i] : $argument->getDefaultValue();
                }
                $i++;
            }
            if ($countRequired > $countValues) {
                throw new Exception(sprintf('Bad count of arguments for route "%s".', $this->path));
            }
            $result = call_user_func_array($controller, $params);
        }

        return $result;
    }

    /**
     * @return Route
     */
    protected function compile(): Route
    {
        $parts = $this->parts;
        $keys = array_keys($parts);
        if (preg_match_all('/\{([^}]+)\}/i', $this->path, $matches)) {
            $matches = $matches[1];
            $tail = array_diff($matches, $keys);
            foreach ($tail as $name) {
                $parts[$name] = '([^/]*)';
            }
        }
        $keys = array_map(function ($item) {
            return sprintf('{%s}', $item);
        }, array_keys($parts));
        $this->compiled = str_replace($keys, array_values($parts), $this->path);

        return $this;
    }

    /**
     * @param callable|string $controller Closure, object or class name
     * @throws Exception
     * @return Route
     */
    protected function setController($controller): Route
    {
        if ($controller instanceof \Closure) {
            $this->controller = $controller;
        } elseif (is_string($controller)) {
            $this->controller = trim($controller);
        } else {
            throw new Exception(sprintf('Bad controller for route "%s"!', $this->path));
        }

        return $this;
    }

    /**
     * @param string $pattern
     * @return Route
     */
    protected function setPath($pattern): Route
    {
        $pattern = trim($pattern);
        $pattern = ltrim($pattern, '^');
        if ($pattern[strlen($pattern) - 1] !== '$') {
            $pattern = rtrim($pattern, '/');
            $pattern = sprintf('#^%s/?(\?.*)?$#', $pattern);
        } else {
            $pattern = sprintf('#^%s#', $pattern);
        }
        $this->path = $pattern;

        return $this;
    }
}