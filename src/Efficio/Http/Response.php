<?php

namespace Efficio\Http;

/**
 * response helper
 */
class Response
{
    /**
     * response formats
     */
    const TEXT = 0;
    const HTML = 1;
    const JSON = 2;

    /**
     * standard headers per response format
     * @var array
     */
    protected static $stdheaders = [
        self::TEXT => [ 'Content-Type' => 'text/plain' ],
        self::HTML => [ 'Content-Type' => 'text/html' ],
        self::JSON => [ 'Content-Type' => 'application/javascript' ],
    ];

    /**
     * response headers
     * @var array
     */
    protected $headers = [];

    /**
     * response payload
     * @var mixed
     */
    protected $content;

    /**
     * response format type
     * @var int
     */
    protected $content_type = self::HTML;

    /**
     * @param mixed $content
     * @param int $content_type
     */
    public function __construct($content = '', $content_type = self::HTML)
    {
        $this->content = $content;
        $this->content_type = $content_type;
    }

    /**
     * output content
     * @return string
     */
    public function __toString()
    {
        $out = $this->content;

        if (
            $this->content_type === self::JSON &&
            (is_object($this->content) || is_array($this->content))
        ) {
            $out = json_encode($this->content);
        }

        return $out;
    }

    /**
     * header adder
     * @param string $header
     * @param string $value
     */
    public function setHeader($header, $value)
    {
        $this->headers[ $header ] = $value;
    }

    /**
     * header remover
     * @param string $header
     */
    public function unsetHeader($header)
    {
        unset($this->headers[ $header ]);
    }

    /**
     * get all headers
     * @return array
     */
    public function getHeaders()
    {
        return array_merge(self::$stdheaders[ $this->content_type ], $this->headers);
    }

    /**
     * content setter
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * content getter
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * content type setter
     * @param int $content_type
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    /**
     * content type getter
     * @return int
     */
    public function getContentType()
    {
        return $this->content_type;
    }
}

