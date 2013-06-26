<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\RuleBook;
use Efficio\Http\Request;
use Efficio\Http\Verb;
use PHPUnit_Framework_TestCase;

class RuleBookTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RuleBook
     */
    public $rulebook;

    public function setUp()
    {
        $this->rulebook = new RuleBook;
    }

    public function testRuleBooksStartOutWithNotRules()
    {
        $this->assertEquals(0, count($this->rulebook->all()));
    }

    public function testRulesCanBeAddedToBook()
    {
        $this->rulebook->add(new Rule);
        $this->assertEquals(1, count($this->rulebook->all()));
    }

    public function testNonMatchingRulesAreNotFound()
    {
        $this->assertNull($this->rulebook->matching('somestring'));
    }

    public function testMatchingRulesAreFound()
    {
        $this->rulebook->add(Rule::create([ '/one/' ], [ 'test' => true ]));
        $this->rulebook->add(Rule::create([ '/somestring/' ], [ 'test' => true ]));
        $this->rulebook->add(Rule::create([ '/two/' ], [ 'test' => true ]));
        $info = $this->rulebook->matching('somestring');
        $this->assertTrue(is_array($info));
    }

    public function testBaseInformationIsReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create([ '/somestring/' ], [ 'test' => true ]));
        $info = $this->rulebook->matching('somestring');
        $this->assertArrayHasKey('test', $info);
        $this->assertTrue($info['test']);
    }

    public function testPatternGroupsAreReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create([ '/api\/(?P<model>[A-Za-z]+)/' ], [ 'test' => true ]));
        $info = $this->rulebook->matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }

    public function testPatternGroupsOverwriteBaseInfoAreReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create([ '/api\/(?P<model>[A-Za-z]+)/' ], [ 'model' => '...' ]));
        $info = $this->rulebook->matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }
}
