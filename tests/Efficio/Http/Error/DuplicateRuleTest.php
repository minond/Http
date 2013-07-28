<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\Error\DuplicateRuleException;
use PHPUnit_Framework_TestCase;
use Exception;

class DuplicateRuleExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DuplicateRuleException
     */
    public $dup;

    public function setUp()
    {
        $this->dup = new DuplicateRuleException;
    }

    public function testItsActuallyAnException()
    {
        $this->assertTrue($this->dup instanceof Exception);
    }

    public function testInstanceOf()
    {
        $this->assertTrue($this->dup instanceof DuplicateRuleException);
    }

    public function testRuleSetterAndGetter()
    {
        $rule = new Rule;
        $this->dup->setRule($rule);
        $this->assertEquals($rule, $this->dup->getRule());
    }
}
