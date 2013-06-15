<?php

namespace Efficio\Http;

use InvalidArgumentException;

class Request
{
    /**
     * request data
     * @var string
     */
    private $input = '';

    /**
     * php://input read flag
     * @var boolean
     */
    private $inputread = false;

    /**
     * request arguments
     * @var array
     */
    private $args = [];

    /**
     * @see Efficio\Http\Verb
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

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
        if (!$this->inputread && !strlen($this->input)) {
            $this->input = file_get_contents('php://input');
            $this->inputread = true;
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
                implode(', ', Verb::options())
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
     * reads super-globals to create it self
     * @return Request
     */
    public static function create()
    {
        $req = new Request;
        return $req;
    }
}
