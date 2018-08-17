<?php
declare(strict_types=1);

namespace Comma;

/**
 * Interface ViewInterface
 * @package Comma
 */
interface ViewInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function assign(string $key, $value): ViewInterface;

    /**
     * @return string
     */
    public function render(): string;
}