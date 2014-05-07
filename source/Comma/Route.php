<?php
namespace Comma;

/**
 * Class Route
 * @package Comma
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Route
{
    /**
     * @var callable Controller
     */
    protected $_controller;
    /**
     * @var string Regular expression
     */
    protected $_path;
    /**
     * @var string Regular expression
     */
    protected $_compiled;
    /**
     * @var array Dependency list
     */
    protected $_dependencies = array();
    /**
     * @var array
     */
    protected $_values;
    /**
     * @var array
     */
    protected $_parts = array();

    /**
     * @param string $path
     * @param callable|string $controller Function or string with className
     */
    public function __construct($path, $controller)
    {
        $this->_controller = null;
        $this->_path = null;
        $this->_compiled = null;
        $this->_dependencies = array();
        $this->_values = null;
        $this->_parts = array();
        $this
            ->_setPath($path)
            ->_setController($controller);
    }

    /**
     * @param string $name
     * @param string $pattern
     * @return self
     */
    public function assert($name, $pattern)
    {
        $this->_parts[$name] = sprintf('(%s)', trim($pattern, '()'));
        return $this;
    }

    /**
     * Inject dependency to controller
     * @param string $name Var name
     * @param mixed $value
     * @return self
     */
    public function inject($name, $value)
    {
        $this->_dependencies[$name] = $value;
        return $this;
    }

    /**
     * Compile rule
     * @return self
     */
    public function compile()
    {
        $parts = $this->_parts;
        $keys = array_keys($parts);
        if (preg_match_all('/\{([^}]+)\}/i', $this->_path, $matches)) {
            $matches = $matches[1];
            $tail = array_diff($matches, $keys);
            foreach ($tail as $name) {
                $parts[$name] = '([^/]*)';
            }
        }
        $keys = array_map(function ($item) {
            return sprintf('{%s}', $item);
        }, array_keys($parts));
        $this->_compiled = str_replace($keys, array_values($parts), $this->_path);
        return $this;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function match($url)
    {
        $url = (string)$url;
        if (!$this->_compiled) {
            $this->compile();
        }
        $result = preg_match($this->_compiled, $url, $this->_values);
//        preg_match('//(/?#([^)]+)/)/i', $this->_pattern, $this->_keys);
        return (bool)$result;
    }

    /**
     * Run controller
     * @throws Exception
     * @return mixed|null
     */
    public function run()
    {
        $result = null;
        if (is_null($this->_values)) {
            throw new \Comma\Exception(sprintf("Route \"%s\" not matched!", $this->_path));
        } else {
            $values = array_slice($this->_values, 1);

            $countRequired = 0;
            $countValues = count($values);
            $controller = $this->_controller;
            if ($this->_controller instanceof \Closure) {
                $func = new \ReflectionFunction($this->_controller);
            } elseif (is_string($this->_controller)) {
                list($className, $method) = explode('::', $this->_controller);
                $reflection = new \ReflectionClass($className);
                $func = $reflection->getMethod($method);
                $controller = array($reflection->newInstance(), $method);
            } else {
                throw new \Comma\Exception(sprintf("Bad controller for route \"%s\"!", $this->_path));
            }
            $i = 0;
            $params = array();
            foreach ($func->getParameters() as $number => $argument) {
                if (array_key_exists($argument->getName(), $this->_dependencies)) {
                    $params[$number] = $this->_dependencies[$argument->getName()];
                    continue;
                }
                if (!$argument->isOptional()) {
                    $params[$number] = array_key_exists($i, $values) ? $values[$i] : null;
                    $countRequired++;
                } else {
                    $params[$number] = array_key_exists($i, $values) ? $values[$i] : $argument->getDefaultValue();
                }
                $i++;
            }
            if ($countRequired > $countValues) {
                throw new \Comma\Exception(sprintf("Bad count of arguments for route \"%s\".", $this->_path));
            }
            $result = call_user_func_array($controller, $params);
        }
        return $result;
    }

    /**
     * Set controller
     * @param callable|string $controller Closure, object or class name
     * @throws Exception
     * @return self
     */
    protected function _setController($controller)
    {
        if ($controller instanceof \Closure) {
            $this->_controller = $controller;
        } elseif (is_string($controller)) {
            $this->_controller = trim($controller);
        } else {
            throw new \Comma\Exception(sprintf("Bad controller for route \"%s\"!", $this->_path));
        }
        return $this;
    }

    /**
     * Set pattern for route
     * @param string $pattern
     * @return self
     */
    protected function _setPath($pattern)
    {
        $pattern = trim($pattern);
        $pattern = ltrim($pattern, '^');
        if (substr($pattern, -1, 1) !== '$') {
            $pattern = rtrim($pattern, '/');
            $pattern = sprintf('#^%s/?(\?.*)?$#', $pattern);
        } else {
            $pattern = sprintf('#^%s#', $pattern);
        }
        $this->_path = $pattern;
        return $this;
    }
}