<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Verb;
use PHPUnit_Framework_TestCase;

class VerbTest extends PHPUnit_Framework_TestCase
{
    public function testAllVerbOptionsAreValid()
    {
        foreach (Verb::options() as $op) {
            $this->assertTrue(Verb::valid($op), "Testing $op");
            $this->assertTrue(Verb::valid(strtolower($op), true), "Testing $op (lowercase) case insensitive");
            $this->assertFalse(Verb::valid(strtolower($op)), "Testing $op (lowercase) case sensitive");
        }
    }

    public function testValidatingValidOptionsWithInvalidCaseReturnsFalse()
    {
        $this->assertFalse(Verb::valid('get'));
    }

    public function testValidatingValidOptionsWithInvalidCaseReturnsTrueWhenIgnoringCase()
    {
        $this->assertTrue(Verb::valid('get', true));
    }

    public function testValidatingInvalidOptionsReturnsFalse()
    {
        $this->assertFalse(Verb::valid('invalid'));
    }

    public function testOptionsMethodRequestAllMethods()
    {
        $this->assertEquals([
            Verb::GET,
            Verb::HEAD,
            Verb::POST,
            Verb::PUT,
            Verb::DEL,
            Verb::OPTIONS,
            Verb::PATCH,
        ], Verb::options());
    }
}
