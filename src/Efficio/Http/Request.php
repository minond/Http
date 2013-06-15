<?php

namespace Efficio\Http;

use InvalidArgumentException;

class Request
{
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
     * request arguments
     * @var array
     */
    protected $args = [];

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
     * argument getter shortcut
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->args[ $key ]) ? $this->args[ $key ] : null;
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
     * arguments getter
     * @return array
     */
    public function getArguments()
    {
        return $this->args;
    }

    /**
     * arguments getter
     * @param array $args
     */
    public function setArguments(array $args)
    {
        $this->args = $args;
    }

    /**
     * argument setter
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->args[ $key ] = $value;
    }

    /**
     * checks if parameter exists
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->args[ $key ]);
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
     * reads super-globals to create it self
     * @return Request
     */
    public static function create()
    {
        $req = new Request;

        $req->setArguments($_REQUEST);
        $req->setUri($_SERVER['SCRIPT_NAME']);
        $req->setPort($_SERVER['SERVER_PORT']);
        $req->setMethod($_SERVER['REQUEST_METHOD']);

        return $req;
    }
}
