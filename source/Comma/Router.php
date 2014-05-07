<?php
namespace Comma;

/**
 * Simple route manager
 * @package Comma
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Router
{
    /**
     * @var \Comma\Route[]
     */
    protected $_routes = array();

    /**
     * @param string $url Request uri
     * @param string $method Request method (get, post, ..)
     * @throws \Comma\Exception\PageNotFound
     * @return \Comma\Route
     */
    public function match($url, $method = null)
    {
        $result = null;
        foreach ($this->_routes as $route) {
            if ($route->match($url)) {
                $result = $route;
                break;
            }
        }
        if (!$result) {
            throw new \Comma\Exception\PageNotFound(sprintf("No route matched for url \"%s\"!", $url), 404);
        }
        return $result;
    }

    /**
     * Append new route
     * @param \Comma\Route $route
     * @return self
     */
    public function append(\Comma\Route $route)
    {
        $this->_routes[] = $route;
        return $this;
    }
}