<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\Error\DuplicateRule;
use PHPUnit_Framework_TestCase;
use Exception;

class DuplicateRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DuplicateRule
     */
    public $dup;

    public function setUp()
    {
        $this->dup = new DuplicateRule;
    }

    public function testItsActuallyAnException()
    {
        $this->assertTrue($this->dup instanceof Exception);
    }

    public function testInstanceOf()
    {
        $this->assertTrue($this->dup instanceof DuplicateRule);
    }

    public function testRuleSetterAndGetter()
    {
        $rule = new Rule;
        $this->dup->setRule($rule);
        $this->assertEquals($rule, $this->dup->getRule());
    }
}
