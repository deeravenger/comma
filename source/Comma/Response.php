<?php
namespace Comma;

/**
 * Response object
 * @package Comma
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @license The MIT License (MIT)
 */
class Response
{
    /**
     * @var string Charset
     */
    protected $_charset;
    /**
     * @var mixed
     */
    protected $_content;
    /**
     * @var array
     */
    protected $_headers = array();

    public function __construct($charset = null)
    {
        $this->_charset = is_null($charset) ? 'utf-8' : $charset;
    }

    public function __toString()
    {
        return $this->send()->content();
    }

    /**
     * Set view for response
     * @param \Comma\View $view
     * @return self
     */
    public function view(\Comma\View $view)
    {
        return $this->html($view->render());
    }

    /**
     * Set html code for response
     * @param string $html
     * @return self
     */
    public function html($html)
    {
        $this->header(sprintf('Content-Type: text/html; charset=%s', $this->_charset));
        $this->_content = $html;
        return $this;
    }

    /**
     * Set data for response
     * @param string $text
     * @return self
     */
    public function text($text)
    {
        $this->header(sprintf('Content-Type: text/plain; charset=%s', $this->_charset));
        $this->_content = $text;
        return $this;
    }

    /**
     * Set data for response in json
     * @param mixed $data
     * @return self
     */
    public function json($data)
    {
        $this->header(sprintf('Content-Type: application/json; charset=%s', $this->_charset));
        $this->_content = json_encode($data);
        return $this;
    }

    /**
     * Set data for response in xml
     * @param array $data
     * @throws \Comma\Exception
     * @return $this
     */
    public function xml(array $data)
    {
        if (count($data) != 1) {
            throw new \Comma\Exception(sprintf("Data must have root element that convert to xml"), 500);
        }
        $this->header(sprintf('Content-Type: application/xml; charset=%s', $this->_charset));
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', $this->_charset);
        $xmlWriter = function (\XMLWriter $writer, $data) use (&$xmlWriter) {
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
                            $writer->writeCdata($value['@cdata']);
                        } else {
                            $xmlWriter($writer, $value);
                        }
                        $writer->endElement();
                    }
                }
            }
        };
        $xmlWriter($writer, $data);
        $this->_content = $writer->outputMemory(true);
        return $this;
    }

    /**
     * Redirect
     * @param string $url
     * @param int|null $code
     */
    public function redirect($url, $code = null)
    {
        $code = is_null($code) ? 302 : intval($code);
        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Add header for response
     * @param string $header
     * @return self
     */
    public function header($header)
    {
        $this->_headers[] = $header;
        return $this;
    }

    /**
     * Send headers
     * @return self
     */
    public function send()
    {
        while (count($this->_headers)) {
            header(array_shift($this->_headers));
        }
        return $this;
    }

    /**
     * Get result
     * @return mixed
     */
    public function content()
    {
        $data = $this->_content;
        $this->_content = null;
        return $data;
    }
}