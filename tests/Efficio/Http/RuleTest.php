<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\Request;
use Efficio\Http\Verb;
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

    public function testTranspileMethodConvertsRegularStringsIntoRegularExpressionString()
    {
        $this->assertEquals('/^string$/', PublicRule::transpile('string'));
    }

    public function testTranspileMethodConvertsGroups()
    {
        $this->assertEquals('/^(?P<string>[A-Za-z0-9]+)$/', PublicRule::transpile('{string}'));
    }

    public function testTranspileMethodConvertsAsteriskIntoAnyMatcher()
    {
        $this->assertEquals('/^(?P<string>.+)$/', PublicRule::transpile('{string*}'));
    }

    public function testTranspileMethodConvertsOptionalGroups()
    {
        $this->assertEquals('/^(?P<string>[A-Za-z0-9]+)?$/', PublicRule::transpile('{string?}'));
    }

    public function testTranspileMethodConvertsMultipleGroups()
    {
        $this->assertEquals(
            '/^(?P<one>[A-Za-z0-9]+) (?P<two>[A-Za-z0-9]+) (?P<three>[A-Za-z0-9]+) (?P<one>[A-Za-z0-9]+)$/',
            PublicRule::transpile('{one} {two} {three} {one}'));
    }

    public function testTranspileMethodConvertsGroupsAndIgnoresRegularText()
    {
        $this->assertEquals(
            '/^(?P<one>[A-Za-z0-9]+) one two (?P<two>[A-Za-z0-9]+)$/',
            PublicRule::transpile('{one} one two {two}'));
    }

    public function testBasicGroups()
    {
        $regex = PublicRule::transpile('{one} one two {two}');
        preg_match($regex, 'firstgroup one two secondgroup', $matches);
        $this->assertEquals('firstgroup', $matches['one']);
        $this->assertEquals('secondgroup', $matches['two']);
    }

    public function testSlashesAreEscaped()
    {
        $this->assertEquals(
            '/^one\/?two$/',
            PublicRule::transpile('one/two', true));
    }

    public function testPeriodsAreEscaped()
    {
        $this->assertEquals(
            '/^one\.?two$/',
            PublicRule::transpile('one.two', true));
    }

    public function testARestfulUrlWithABaseAndAModel()
    {
        preg_match(PublicRule::transpile('/api/{model}'), '/api/users', $matches);
        $this->assertEquals('users', $matches['model']);
    }

    public function testARestfulUrlWithABaseAModelAndASlash()
    {
        preg_match(PublicRule::transpile('/api/{model}/'), '/api/users/', $matches);
        $this->assertEquals('users', $matches['model']);
    }

    public function testARestfulUrlWithABaseAModelASlashAndAnId()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id}'), '/api/users/324', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertEquals('324', $matches['id']);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdAndMoreStringAtTheEndDoesNotMatch()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id}'), '/api/users/324/fdsafsd', $matches);
        $this->assertArrayNotHasKey('model', $matches);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelAndAnOptionalId()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id?}'), '/api/users', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelASlashAndAnOptionalId()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id?}'), '/api/users/', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdThatsOptional()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id?}'), '/api/users/324', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertEquals('324', $matches['id']);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdThatsOptionalAndMoreStringAtTheEnd()
    {
        preg_match(PublicRule::transpile('/api/{model}/{id?}'), '/api/users/324/fdsafsd', $matches);
        $this->assertArrayNotHasKey('id', $matches);
        $this->assertArrayNotHasKey('model', $matches);
    }

    public function testCanMatchToARequestObject()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');

        $this->assertTrue(PublicRule::create([ Rule::transpile('/{page}') ])
            ->matches($req)[0]);
    }

    public function testRequestMethodIsCheckedIfIncludedInRuleInfo()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');

        $this->assertFalse(PublicRule::create([ Rule::transpile('/{page}') ],
            [ 'method' => Verb::PUT ])
            ->matches($req)[0]);
    }

    public function testRequestMethodIsProperlyCompared()
    {
        $req = new Request;
        $req->setMethod(Verb::PUT);
        $req->setUri('/mypage');

        $this->assertTrue(PublicRule::create([ Rule::transpile('/{page}') ],
            [ 'method' => Verb::PUT ])
            ->matches($req)[0]);
    }
}
