<?php

namespace Efficio\Http;

use InvalidArgumentException;
use Efficio\Utilitatis\Word;
use Efficio\Utilitatis\PublicObject;

/**
 * holds basic information about a request
 */
class Request
{
    const HEADER_SERVER_PREFIX = 'HTTP_';

    /**
     * request data
     * @var string
     */
    protected static $globalinput = '';

    /**
     * php://input read flag
     * @var boolean
     */
    protected static $inputread = false;

    /**
     * request data
     * @var string
     */
    protected $input = '';

    /**
     * @see Efficio\Http\Verb
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * request parameters
     * @var PublicObject
     */
    public $param;

    /**
     * request headers
     * @var PublicObject
     */
    public $header;

    /**
     *
     */
    public function __construct()
    {
        $this->param = new PublicObject;
        $this->header = new PublicObject;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     */
    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * reads input stream
     * @return string
     */
    public function getInput()
    {
        if (!static::$inputread && !strlen($this->input)) {
            static::$globalinput = file_get_contents('php://input');
            static::$inputread = true;
            $this->input = static::$globalinput;
        }

        return $this->input;
    }

    /**
     * input setter
     * @param string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * parameters getter
     * @return PublicObject
     */
    public function getParameters()
    {
        return $this->param;
    }

    /**
     * parameters getter
     * @param PublicObject $param
     */
    public function setParameters(PublicObject $param)
    {
        $this->param = $param;
    }

    /**
     * headers getter
     * @return PublicObject
     */
    public function getHeaders()
    {
        return $this->header;
    }

    /**
     * headers getter
     * @param PublicObject $header
     */
    public function setHeaders(PublicObject $header)
    {
        $this->header = $header;
    }

    /**
     * method setter
     * @param string $method
     * @throws InvalidArgumentException
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);

        if (!Verb::valid($method)) {
            throw new InvalidArgumentException(
                sprintf('Invalid method type: %s, expects: [%s]', $method,
                    implode(', ', Verb::options()))
            );
        }

        $this->method = $method;
    }

    /**
     * method getter
     * @param string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * uri setter
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * uri getter
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * port setter
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * port getter
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * reads super-globals to create itself
     * @return Request
     */
    public static function create()
    {
        $req = new static;
        $word = new Word;

        $req->setUri(explode('?', $_SERVER['REQUEST_URI'], 2)[0]);
        $req->setPort($_SERVER['SERVER_PORT']);
        $req->setMethod($_SERVER['REQUEST_METHOD']);
        $req->setParameters(new PublicObject($_REQUEST));

        foreach ($_SERVER as $key => $val) {
            if (strpos($key, self::HEADER_SERVER_PREFIX) === 0) {
                $key = str_replace(self::HEADER_SERVER_PREFIX, '', $key);
                $key = strtolower($key);
                $key = $word->humanCase($key);
                $key = ucwords($key);
                $key = str_replace(' ', '-', $key);

                $req->header->{ $key } = $val;
            }
        }

        return $req;
    }
}

