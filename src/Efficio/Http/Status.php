<?php

namespace Efficio\Http;

/**
 * @linke https://github.com/bigcompany/know-your-http
 * @note status codes should be added as needed
 */
abstract class Status
{
    /**
     * standard response for successful HTTP requests
     */
    const OK = 200;

    /**
     * the resource was not found, though its existence in the future is
     * possible
     */
    const NOT_FOUND = 404;
}
