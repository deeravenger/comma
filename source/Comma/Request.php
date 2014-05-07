<?php
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
    protected $_method;
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @param string|null $requestMethod Request method (GET/POST/PUT/DELETE and other)
     * @param array|null $server $_SERVER data
     * @param array|null $get $_GET data
     * @param array|null $post $_POST data
     * @param array|null $files $_FILES data
     * @param array|null $methods HTTP methods with data (array( 'PUT' => array(), 'DELETE' => array() )
     * @throws \Comma\Exception
     */
    public function __construct($requestMethod = null, array $server = null, array $get = null, array $post = null, array $files = null, array $methods = null)
    {
        $methods = is_array($methods) ? $methods : array();
        foreach ($methods as $method => $data) {
            $method = strtoupper(trim($method));
            $this->_data[$method] = is_array($data) ? $data : array();
        }
        $this->_data['SERVER'] = is_array($server) ? $server : $_SERVER;
        $this->_data['GET'] = is_array($get) ? $get : $_GET;
        $this->_data['POST'] = is_array($post) ? $post : $_POST;
        $this->_data['FILES'] = is_array($files) ? $files : $_FILES;
        $this->_method = !is_null($requestMethod) ? $requestMethod : strtoupper($this->server('REQUEST_METHOD', 'GET'));
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \Comma\Exception
     */
    public function __call($method, $arguments)
    {
        $method = strtoupper(trim($method));
        if (!in_array($method, array_keys($this->_data))) {
            throw new \Comma\Exception(sprintf("Request method %s is undefined", $method));
        }
        array_unshift($arguments, $this->_data[$method]);
        $result = call_user_func_array(array($this, 'getValueOf'), $arguments);
        return $result;
    }

    /**
     * Get info about uploaded file
     * @param string $name
     * @param int|null $index
     * @throws \Comma\Exception
     * @return array
     */
    public function file($name, $index = null)
    {
        $files = & $this->_data['FILES'];
        $result = array();
        if (!isset($files[$name])) {
            throw new \Comma\Exception(sprintf("File \"%s\" not exists in \$_FILES!", $name), 500);
        }
        if (!is_array($files[$name]['tmp_name'])) {
            $result = array($files[$name]);
        } else {
            foreach (array_keys($files[$name]) as $key) {
                foreach (array_keys($files[$name]['tmp_name']) as $i) {
                    $result[$i][$key] = isset($files[$name][$key][$i]) ? $files[$name][$key][$i] : null;
                }
            }
            if (!is_null($index)) {
                $result = array($result[$index]);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function files()
    {
        $result = array();
        foreach ($this->_data['FILES'] as $key => $value) {
            $result[$key] = $this->file($key);
        }
        return $result;
    }

    public function server($name)
    {
        $server = & $this->_data['SERVER'];
        if (in_array($name, array('HTTP_HOST', 'SERVER_NAME'))) {
            $name = isset($server['SERVER_NAME']) ? 'SERVER_NAME' : (isset($server['HTTP_HOST']) ? 'HTTP_HOST' : 'SERVER_NAME');
        }
        if (func_num_args() > 1) {
            $result = $this->getValueOf($server, $name, func_get_arg(1));
        } else {
            $result = $this->getValueOf($server, $name);
        }
        return $result;
    }

    /**
     * Getting list of vars
     * @param null $requestMethod
     * @throws \Comma\Exception
     * @return array
     */
    public function vars($requestMethod = null)
    {
        $result = null;
        if (is_null($requestMethod)) {
            $requestMethod = $this->_method;
        }
        $availableMethods = array_keys($this->_data);
        if (in_array($requestMethod, $availableMethods)) {
            $result = $this->_data[$requestMethod];
        } else {
            throw new \Comma\Exception(sprintf("Undefined request method \"%s\". Use \"%s\"", $requestMethod, implode('" or "', $availableMethods)), 501);
        }
        return $result;
    }

    /**
     * Return request method
     * @return string
     */
    public function method()
    {
        return $this->_method;
    }

    /**
     * Get raw data
     * @return string
     */
    public function raw()
    {
        return file_get_contents('php://input');
    }

    /**
     * Getting value from array
     * @param array $data
     * @param string $name
     * @return array|mixed|string
     * @throws \Comma\Exception
     */
    public function getValueOf(array $data, $name)
    {
        $result = null;
        $haveDefault = func_num_args() > 2;
        if (array_key_exists($name, $data)) {
            $result = $data[$name];
        } else {
            if ($haveDefault) {
                $result = func_get_arg(2);
            } else {
                throw new \Comma\Exception(sprintf("Var \"%s\" not exists!", $name), 500);
            }
        }
        return $result;
    }
}
