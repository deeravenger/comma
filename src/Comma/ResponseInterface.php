<?php
declare(strict_types=1);

namespace Comma;

/**
 * Interface ResponseInterface
 * @package Comma
 */
interface ResponseInterface
{
    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $header
     * @return ResponseInterface
     */
    public function setHeader(string $header): ResponseInterface;

    /**
     * @return ResponseInterface
     */
    public function sendHeaders(): ResponseInterface;

    /**
     * @return array
     */
    public function getHeaders(): array;
}