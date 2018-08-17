<?php
declare(strict_types=1);

namespace Comma\Response;

use Comma\ResponseInterface;

class RedirectResponse implements ResponseInterface
{
    /**
     * @var array
     */
    protected $headers = [];

    public function __construct(string $url, int $code = null)
    {
        header('Location: ' . $url, true, $code ?? 302);
    }

    /**
     * @return string
     * @throws \Comma\Exception
     */
    public function getContent(): string
    {
        return '';
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

        exit;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}