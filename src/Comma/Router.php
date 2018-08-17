<?php
declare(strict_types=1);

namespace Comma;

use Comma\Exception\PageNotFound;

/**
 * Router class
 * @package Comma
 */
class Router implements RouterMatcherInterface
{
    /**
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * @param string $url Request uri
     * @param string $method Request method (get, post, ..)
     * @throws PageNotFound
     * @return RouteInterface
     */
    public function match(string $url, string $method = null): RouteInterface
    {
        $result = null;
        foreach ($this->routes as $route) {
            if ($route->match($url)) {
                $result = $route;
                break;
            }
        }
        if (!$result) {
            throw new PageNotFound(sprintf('No route matched for url "%s"!', $url), 404);
        }

        return $result;
    }

    /**
     * Append new route
     * @param RouteInterface $route
     * @return self
     */
    public function append(RouteInterface $route): Router
    {
        $this->routes[] = $route;

        return $this;
    }
}