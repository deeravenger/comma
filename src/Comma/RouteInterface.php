<?php
declare(strict_types=1);

namespace Comma;

/**
 * Interface RouteInterface
 * @package Comma
 */
interface RouteInterface
{
    /**
     * @param string $url
     * @return bool
     */
    public function match(string $url): bool;

    /**
     * @return mixed
     */
    public function handle();
}