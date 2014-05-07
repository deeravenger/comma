<?php
namespace Comma;

/**
 * Native template engine
 * @package Comma
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class View
{
    /**
     * @var string View path
     */
    protected $_path;
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @param string $path Path to template
     * @param array $data View data
     */
    public function __construct($path, array $data = array())
    {
        $this->_path = $path;
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Assign param to template
     * @param string $key
     * @param mixed|View $value
     * @return View
     */
    public function assign($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Render content
     * @return string
     */
    public function render()
    {
        ob_start();
        $render = function () {
            extract(func_get_arg(1), EXTR_OVERWRITE);
            require func_get_arg(0);
        };
        $render($this->_path, $this->_data);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}