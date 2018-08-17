<?php
declare(strict_types=1);

namespace Comma;

/**
 * Class Request
 * @package Comma
 * @method string get(\string $name, $default = null)
 * @method string post(\string $name, $default = null)
 */
class Request
{
    /**
     * @var string Current request method
     */
    protected $method;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string|null $requestMethod Request method (GET/POST/PUT/DELETE and other)
     * @param array|null $server $_SERVER data
     * @param array|null $get $_GET data
     * @param array|null $post $_POST data
     * @param array|null $files $_FILES data
     * @param array|null $methods HTTP methods with data (array( 'PUT' => array(), 'DELETE' => array() )
     * @throws Exception
     */
    public function __construct($requestMethod = null, array $server = null, array $get = null, array $post = null, array $files = null, array $methods = null)
    {
        $methods = is_array($methods) ? $methods : [];
        foreach ($methods as $method => $data) {
            $method = strtoupper(trim($method));
            $this->data[$method] = is_array($data) ? $data : [];
        }
        $this->data['SERVER'] = is_array($server) ? $server : $_SERVER;
        $this->data['GET'] = is_array($get) ? $get : $_GET;
        $this->data['POST'] = is_array($post) ? $post : $_POST;
        $this->data['FILES'] = is_array($files) ? $files : $_FILES;
        $this->method = $requestMethod ?? strtoupper($this->server('REQUEST_METHOD', 'GET'));
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        $method = strtoupper(trim($method));
        if (!array_key_exists($method, $this->data)) {
            throw new Exception(sprintf('Request method %s is undefined', $method));
        }
        array_unshift($arguments, $this->data[$method]);
        $result = call_user_func_array(array($this, 'getValueOf'), $arguments);

        return $result;
    }

    /**
     * Get info about uploaded file
     * @param string $name
     * @param int|null $index
     * @throws Exception
     * @return array
     */
    public function file($name, $index = null): array
    {
        $files = &$this->data['FILES'];
        $result = array();
        if (!isset($files[$name])) {
            throw new Exception(sprintf('File "%s" not exists in $_FILES!', $name), 500);
        }
        if (!is_array($files[$name]['tmp_name'])) {
            $result = array($files[$name]);
        } else {
            foreach (array_keys($files[$name]) as $key) {
                foreach (array_keys($files[$name]['tmp_name']) as $i) {
                    $result[$i][$key] = $files[$name][$key][$i] ?? null;
                }
            }
            if (null !== $index) {
                $result = array($result[$index]);
            }
        }
        return $result;
    }

    /**
     * @return array
     * @throws \Comma\Exception
     */
    public function files(): array
    {
        $result = array();
        if (!empty($this->data['FILES'])) {
            foreach ($this->data['FILES'] as $key => $value) {
                $result[$key] = $this->file($key);
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @return array|mixed|string
     * @throws \Comma\Exception
     */
    public function server(string $name)
    {
        $server = &$this->data['SERVER'];
        if (in_array($name, array('HTTP_HOST', 'SERVER_NAME'), true)) {
            $name = isset($server['SERVER_NAME']) ? 'SERVER_NAME' : (isset($server['HTTP_HOST']) ? 'HTTP_HOST' : 'SERVER_NAME');
        }
        if (func_num_args() > 0) {
            $result = $this->getValueOf($server, $name, func_get_arg(1));
        } else {
            $result = $this->getValueOf($server, $name);
        }

        return $result;
    }

    /**
     * Get list of vars
     * @param null $requestMethod
     * @throws Exception
     * @return array
     */
    public function vars($requestMethod = null): array
    {
        $result = null;
        if (null === $requestMethod) {
            $requestMethod = $this->method;
        }
        $availableMethods = array_keys($this->data);
        if (in_array($requestMethod, $availableMethods, true)) {
            $result = $this->data[$requestMethod];
        } else {
            throw new Exception(sprintf('Undefined request method "%s". Use "%s"', $requestMethod, implode('" or "', $availableMethods)), 501);
        }

        return $result;
    }

    /**
     * Return request method
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get raw data
     * @return string
     */
    public function raw(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * Getting value from array
     * @param array $data
     * @param string $name
     * @return array|mixed|string
     * @throws Exception
     */
    protected function getValueOf(array $data, $name)
    {
        $result = null;
        $haveDefault = func_num_args() > 2;
        if (array_key_exists($name, $data)) {
            $result = $data[$name];
        } else {
            if ($haveDefault) {
                $result = func_get_arg(2);
            } else {
                throw new Exception(sprintf('Var "%s" not exists!', $name), 500);
            }
        }

        return $result;
    }
}
