<?php
declare(strict_types=1);

namespace Comma;

/**
 * Class Response
 * @package Comma
 */
class Response implements ResponseInterface
{
    /**
     * @var ViewInterface
     */
    protected $view;
    /**
     * @var array
     */
    protected $headers = [];

    public function __construct(ViewInterface $view, string $charset = null)
    {
        $this->view = $view;
        $this->setHeader(sprintf('Content-Type: text/html; charset=%s', $charset ?? 'utf-8'));
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->view->render();
    }

    /**
     * @param string $header
     * @return ResponseInterface
     */
    public function setHeader(string $header): ResponseInterface
    {
        $this->headers[] = $header;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function sendHeaders(): ResponseInterface
    {
        while (count($this->headers)) {
            header(array_shift($this->headers));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}