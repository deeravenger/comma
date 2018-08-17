<?php
declare(strict_types=1);

namespace Comma\Response;

use Comma\Exception;
use Comma\ResponseInterface;

class XmlResponse implements ResponseInterface
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $charset;

    public function __construct(array $data, string $charset = null)
    {
        $this->data = $data;
        $this->charset = $charset ?? 'utf-8';
        $this->setHeader(sprintf('Content-Type: application/xml; charset=%s', $this->charset));
    }

    /**
     * @return string
     * @throws \Comma\Exception
     */
    public function getContent(): string
    {
        if (1 !== count($this->data)) {
            throw new Exception(sprintf('Data must have root element that convert to xml'), 500);
        }

        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', $this->charset);
        $xmlWriter = function (\XMLWriter $writer, array $data) use (&$xmlWriter) {
            foreach ($data as $key => $value) {
                if (preg_match('/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i', $key)) { // Only valid tags
                    if (is_scalar($value)) {
                        $writer->writeElement($key, $value);
                    } elseif (is_array($value)) {
                        $writer->startElement($key);
                        if (isset($value['@attrs'])) {
                            array_walk($value['@attrs'], function ($value, $key, \XMLWriter $writer) {
                                $writer->writeAttribute($key, $value);
                            }, $writer);
                            unset($value['@attrs']);
                        }
                        if (isset($value['@value'])) {
                            $writer->text($value['@value']);
                        } elseif (isset($value['@cdata'])) {
                            $writer->writeCData($value['@cdata']);
                        } else {
                            $xmlWriter($writer, $value);
                        }
                        $writer->endElement();
                    }
                }
            }
        };
        $xmlWriter($writer, $this->data);
        $result = $writer->outputMemory();

        return $result;
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