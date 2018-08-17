<?php
declare(strict_types=1);

namespace Comma;

/**
 * Class View
 * @package Comma
 */
class View implements ViewInterface
{
    /**
     * @var string
     */
    protected $path = '';
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $path Path to template
     * @param array $data View data
     */
    public function __construct($path, array $data = array())
    {
        $this->path = $path;
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ViewInterface
     */
    public function assign(string $key, $value): ViewInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        ob_start();
        $render = function ($path, array $data) {
            extract($data, EXTR_OVERWRITE);
            require_once $path;
        };
        $render($this->path, $this->data);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}