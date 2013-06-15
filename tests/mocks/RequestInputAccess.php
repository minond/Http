<?php

namespace Efficio\Test\Mocks\Http;

use Efficio\Http\Request;

/**
 * access to Request::$inputread
 */
class RequestInputAccess extends Request
{
    /**
     * @param boolean $read
     */
    public static function setInputRead($read)
    {
        self::$inputread = $read;
    }

    /**
     * @return boolean
     */
    public static function getInputRead()
    {
        return self::$inputread;
    }
}
