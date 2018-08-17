<?php
declare(strict_types=1);

namespace Comma\Response;

use Comma\ResponseInterface;

class HtmlResponse implements ResponseInterface
{
    /**
     * @var string
     */
    protected $body;
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * HtmlResponse constructor.
     * @param string $body
     * @param string|null $charset
     */
    public function __construct(string $body, string $charset = null)
    {
        $this->body = $body;
        $this->setHeader(sprintf('Content-Type: text/html; charset=%s', $charset ?? 'utf-8'));
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->body;
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
        while (\count($this->headers)) {
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