<?php
declare(strict_types=1);

namespace Comma;

/**
 * Interface RouterMatcherInterface
 * @package Comma
 */
interface RouterMatcherInterface
{
    /**
     * @param string $url
     * @param string|null $method
     * @return RouteInterface
     */
    public function match(string $url, string $method = null): RouteInterface;
}