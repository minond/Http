<?php

namespace Efficio\Tests\Mocks\Http;

use Efficio\Http\Rule;

class PublicRule extends Rule
{
    public function getExpressions()
    {
        return $this->expressions;
    }
}

