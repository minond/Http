<?php

namespace Efficio\Tests\Mocks\Http;

use Efficio\Http\Rule;

class PublicRule extends Rule
{
    public function getExpressions()
    {
        return $this->expressions;
    }

    public static function getPool()
    {
        return self::$pool;
    }

    public static function flushPool()
    {
        self::$pool = [];
    }
}

