<?php

namespace Efficio\Http;

use Efficio\Utilitatis\PublicObject;

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
     * response static code
     * @var int
     */
    protected $status_code = Status::OK;

    /**
     * response headers
     * @var PublicObject
     */
    public $header;

    /**
     * @param mixed $content
     * @param int $content_type
     */
    public function __construct($content = '', $content_type = self::HTML)
    {
        $this->content = $content;
        $this->content_type = $content_type;
        $this->header = new PublicObject;
    }

    /**
     * get all headers
     * @return array
     */
    public function getHeaders()
    {
        return array_merge(
            self::$stdheaders[ $this->content_type ],
            $this->header->getArrayCopy());
    }

    /**
     * send all headers
     * @codeCoverageIgnore
     */
    public function sendHeaders()
    {
        http_response_code($this->status_code);
        foreach ($this->getHeaders() as $header => $value)
            header("{$header}: {$value}");
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

    /**
     * output content
     * @return string
     */
    public function sendContent()
    {
        $out = $this->content;

        if (
            $this->content_type === self::JSON &&
            (is_object($this->content) || is_array($this->content))
        ) {
            $out = json_encode($this->content);
        }

        echo $out;
    }

    /**
     * @param int $status_code
     */
    public function setStatusCode($status_code)
    {
        $this->status_code = $status_code;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
}

