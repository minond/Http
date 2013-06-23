<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Tests\Mocks\Http\PublicRule;
use PHPUnit_Framework_TestCase;

require_once './tests/mocks/PublicRule.php';

class RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PublicRule
     */
    public $rule;

    public function setUp()
    {
        $this->rule = new PublicRule;
    }

    public function tearDown()
    {
        PublicRule::flushPool();
    }

    public function testNewRulesAddThemSelvesToTheRulesPool()
    {
        PublicRule::flushPool();
        $rules = [
            new PublicRule,
            new PublicRule,
            new PublicRule,
            new PublicRule,
        ];

        $this->assertEquals($rules, PublicRule::getPool());
    }

    public function testInformationGetterAndSetter()
    {
        $info = [ 'controller' => 'MyController' ];
        $this->rule->setInformation($info);
        $this->assertEquals($info, $this->rule->getInformation());
    }

    public function testExpressionAdder()
    {
        $this->rule->addExpression('1');
        $this->rule->addExpression('2');
        $this->assertEquals(['1', '2'], $this->rule->getExpressions());
    }

    public function testNonMatchingStrings()
    {
        $this->rule->addExpression('/string/');
        list($match, $matches, $expression) = $this->rule->matches('somethingelse');
        $this->assertFalse($match);
    }

    public function testMatchingStrings()
    {
        $this->rule->addExpression('/string/');
        list($match, $matches, $expression) = $this->rule->matches('string');
        $this->assertTrue($match);
    }

    public function testMatchingExpressionIsReturned()
    {
        $this->rule->addExpression('/one/');
        $this->rule->addExpression('/two/');
        $this->rule->addExpression('/three/');
        list($match, $matches, $expression) = $this->rule->matches('two');
        $this->assertTrue($match);
        $this->assertEquals('/two/', $expression);
    }

    public function testMatchesAreReturned()
    {
        $this->rule->addExpression('/catch(22)/');
        list($match, $matches, $expression) = $this->rule->matches('catch22');
        $this->assertTrue($match);
        $this->assertEquals(['catch22', '22'], $matches);
    }

    public function testCreateHelperMethod()
    {
        $expressions = ['1', '2'];
        $information = ['controller' => 'MyController'];

        $rule = PublicRule::create($expressions, $information);
        $this->assertEquals($expressions, $rule->getExpressions(), 'checking expressions');
        $this->assertEquals($information, $rule->getInformation(), 'checking information');
    }

    public function testNonMatchingRulesAreNotFound()
    {
        $this->assertNull(Rule::matching('somestring'));
    }

    public function testMatchingRulesAreFound()
    {
        Rule::create([ '/somestring/' ], [ 'test' => true ]);
        $info = Rule::matching('somestring');
        $this->assertTrue(is_array($info));
    }

    public function testBaseInformationIsReturnedOnMatch()
    {
        Rule::create([ '/somestring/' ], [ 'test' => true ]);
        $info = Rule::matching('somestring');
        $this->assertArrayHasKey('test', $info);
        $this->assertTrue($info['test']);
    }

    public function testPatternGroupsAreReturnedOnMatch()
    {
        Rule::create([ '/api\/(?P<model>[A-Za-z]+)/' ]);
        $info = Rule::matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }

    public function testPatternGroupsOverwriteBaseInfoAreReturnedOnMatch()
    {
        Rule::create([ '/api\/(?P<model>[A-Za-z]+)/' ], [ 'model' => '...' ]);
        $info = Rule::matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }

    public function testTranspileMethodConvertsRegularStringsIntoRegularExpressionString()
    {
        $this->assertEquals('/string/', Rule::transpile('string'));
    }

    public function testTranspileMethodConvertsSimpleGroups()
    {
        $this->assertEquals('/(?P<string>[A-Za-z0-9]+)/', Rule::transpile('{string}'));
    }

    public function testTranspileMethodConvertsMultipleSimpleGroups()
    {
        $this->assertEquals(
            '/(?P<one>[A-Za-z0-9]+) (?P<two>[A-Za-z0-9]+) (?P<three>[A-Za-z0-9]+) (?P<one>[A-Za-z0-9]+)/',
            Rule::transpile('{one} {two} {three} {one}'));
    }

    public function testTranspileMethodConvertsSimpleGroupsAndIgnoresRegularText()
    {
        $this->assertEquals(
            '/(?P<one>[A-Za-z0-9]+) one two (?P<two>[A-Za-z0-9]+)/',
            Rule::transpile('{one} one two {two}'));
    }

    public function testBasicGroups()
    {
        $regex = Rule::transpile('{one} one two {two}');
        preg_match($regex, 'firstgroup one two secondgroup', $matches);
        $this->assertEquals('firstgroup', $matches['one']);
        $this->assertEquals('secondgroup', $matches['two']);
    }
}
