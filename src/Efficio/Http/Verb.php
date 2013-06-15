<?php

namespace Efficio\Http;

/**
 * @link http://en.wikipedia.org/wiki/Http#Request_methods
 * @linke https://github.com/bigcompany/know-your-http
 * @note does not include TRACE or CONNECT
 */
abstract class Verb
{
    /**
     * requests a representation of the specified resource. requests using GET
     * should only retrieve data and should have no other effect.
     */
    const GET = 'GET';

    /**
     * asks for the response identical to the one that would correspond to a
     * GET request, but without the response body.
     */
    const HEAD = 'HEAD';

    /**
     * requests that the server accept the entity enclosed in the request as a
     * new subordinate of the web resource identified by the URI.
     */
    const POST = 'POST';

    /**
     * requests that the enclosed entity be stored under the supplied URI. if
     * the URI refers to an already existing resource, it is modified; if the
     * uri does not point to an existing resource, then the server can create
     * the resource with that URI.
     */
    const PUT = 'PUT';

    /**
     * deletes the specified resource.
     */
    const DEL = 'DELETE';

    /**
     * returns the HTTP methods that the server supports for specified URL.
     */
    const OPTIONS = 'OPTIONS';

    /**
     * is used to apply partial modifications to a resource.
     */
    const PATCH = 'PATCH';

    /**
     * validates a method string
     * @param string $method
     * @param boolean $ignorecase, default: false
     * @return boolean
     */
    public static function valid($method, $ignorecase = false)
    {
        return in_array($ignorecase ? strtoupper($method) : $method,
            self::options());
    }

    /**
     * returns available verbs
     * @return array
     */
    public static function options()
    {
        return [
            self::GET,
            self::HEAD,
            self::POST,
            self::PUT,
            self::DEL,
            self::OPTIONS,
            self::PATCH,
        ];
    }
}
