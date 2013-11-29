<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\Request;
use Efficio\Http\Verb;
use PHPUnit_Framework_TestCase;

class RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rule
     */
    public $rule;

    public function setUp()
    {
        $this->rule = new Rule;
    }

    public function testInformationGetterAndSetter()
    {
        $info = [ 'controller' => 'MyController' ];
        $this->rule->setInformation($info);
        $this->assertEquals($info, $this->rule->getInformation());
    }

    public function testExpressionAdder()
    {
        $this->rule->setExpression('1');
        $this->rule->setExpression('2');
        $this->assertEquals('2', $this->rule->getExpression());
    }

    public function testNonMatchingStrings()
    {
        $this->rule->setExpression('/string/');
        list($match, $matches) = $this->rule->matches('somethingelse');
        $this->assertFalse($match);
    }

    public function testMatchingStrings()
    {
        $this->rule->setExpression('/string/');
        list($match, $matches) = $this->rule->matches('string');
        $this->assertTrue($match);
    }

    public function testMatchingExpressionIsReturned()
    {
        $this->rule->setExpression('/one/');
        $this->rule->setExpression('/three/');
        $this->rule->setExpression('/two/');
        list($match, $matches) = $this->rule->matches('two');
        $this->assertTrue($match);
    }

    public function testMatchesAreReturned()
    {
        $this->rule->setExpression('/catch(22)/');
        list($match, $matches) = $this->rule->matches('catch22');
        $this->assertTrue($match);
        $this->assertEquals(['catch22', '22'], $matches);
    }

    public function testCreateHelperMethod()
    {
        $expression = '1';
        $information = ['controller' => 'MyController'];

        $rule = Rule::create($expression, $information);
        $this->assertEquals($expression, $rule->getExpression(), 'checking expressions');
        $this->assertEquals($information, $rule->getInformation(), 'checking information');
    }

    public function testCreateHelperMethodCanWorkWithTemplates()
    {
        $template = '/list/{country}/{state}';
        $rule = Rule::create($template, [], true);
        $this->assertEquals($template, $rule->getTemplate());
        $this->assertEquals(Rule::transpile($template), $rule->getExpression());
    }

    public function testTranspileMethodConvertsRegularStringsIntoRegularExpressionString()
    {
        $this->assertEquals('/^string$/', Rule::transpile('string'));
    }

    public function testTranspileMethodConvertsGroups()
    {
        $this->assertEquals('/^(?P<string>[A-Za-z0-9]+)$/', Rule::transpile('{string}'));
    }

    public function testTranspileMethodConvertsAsteriskIntoAnyMatcher()
    {
        $this->assertEquals('/^(?P<string>.+)$/', Rule::transpile('{string*}'));
    }

    public function testTranspileMethodConvertsOptionalGroups()
    {
        $this->assertEquals('/^(?P<string>[A-Za-z0-9]+)?$/', Rule::transpile('{string?}'));
    }

    public function testTranspileMethodConvertsCustomTypes()
    {
        $this->assertEquals('/^(?P<string>\d+)$/', Rule::transpile('{string:\d+}'));
    }

    public function testTranspileMethodConvertsOptionalGroupsWithCustomTypes()
    {
        $this->assertEquals('/^(?P<string>\d+)?$/', Rule::transpile('{string:\d+?}'));
    }

    public function testTranspileMethodConvertsMultipleGroups()
    {
        $this->assertEquals(
            '/^(?P<one>[A-Za-z0-9]+) (?P<two>[A-Za-z0-9]+) (?P<three>[A-Za-z0-9]+) (?P<one>[A-Za-z0-9]+)$/',
            Rule::transpile('{one} {two} {three} {one}'));
    }

    public function testTranspileMethodConvertsGroupsAndIgnoresRegularText()
    {
        $this->assertEquals(
            '/^(?P<one>[A-Za-z0-9]+) one two (?P<two>[A-Za-z0-9]+)$/',
            Rule::transpile('{one} one two {two}'));
    }

    public function testBasicGroups()
    {
        $regex = Rule::transpile('{one} one two {two}');
        preg_match($regex, 'firstgroup one two secondgroup', $matches);
        $this->assertEquals('firstgroup', $matches['one']);
        $this->assertEquals('secondgroup', $matches['two']);
    }

    public function testSlashesAreEscaped()
    {
        $this->assertEquals(
            '/^one\/two$/',
            Rule::transpile('one/two', true));
    }

    public function testPeriodsAreEscaped()
    {
        $this->assertEquals(
            '/^one\.two$/',
            Rule::transpile('one.two', true));
    }

    public function testARestfulUrlWithABaseAndAModel()
    {
        preg_match(Rule::transpile('/api/{model}'), '/api/users', $matches);
        $this->assertEquals('users', $matches['model']);
    }

    public function testARestfulUrlWithABaseAModelAndASlash()
    {
        preg_match(Rule::transpile('/api/{model}/'), '/api/users/', $matches);
        $this->assertEquals('users', $matches['model']);
    }

    public function testARestfulUrlWithABaseAModelASlashAndAnId()
    {
        preg_match(Rule::transpile('/api/{model}/{id}'), '/api/users/324', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertEquals('324', $matches['id']);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdAndMoreStringAtTheEndDoesNotMatch()
    {
        preg_match(Rule::transpile('/api/{model}/{id}'), '/api/users/324/fdsafsd', $matches);
        $this->assertArrayNotHasKey('model', $matches);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelAndAnOptionalId()
    {
        preg_match(Rule::transpile('/api/{model}/?{id?}'), '/api/users', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelASlashAndAnOptionalId()
    {
        preg_match(Rule::transpile('/api/{model}/?{id?}'), '/api/users/', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertArrayNotHasKey('id', $matches);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdThatsOptional()
    {
        preg_match(Rule::transpile('/api/{model}/{id?}'), '/api/users/324', $matches);
        $this->assertEquals('users', $matches['model']);
        $this->assertEquals('324', $matches['id']);
    }

    public function testARestfulUrlWithABaseAModelASlashAnIdThatsOptionalAndMoreStringAtTheEnd()
    {
        preg_match(Rule::transpile('/api/{model}/{id?}'), '/api/users/324/fdsafsd', $matches);
        $this->assertArrayNotHasKey('id', $matches);
        $this->assertArrayNotHasKey('model', $matches);
    }

    public function testCustomTypes()
    {
        preg_match(Rule::transpile('/{page}.{format:xml|json}'), '/users.xml', $matches);
        $this->assertArrayHasKey('page', $matches);
        $this->assertArrayHasKey('format', $matches);
        $this->assertEquals('users', $matches['page']);
        $this->assertEquals('xml', $matches['format']);
    }

    public function testCustomTypesFilterOutStringsNotMatchingGroup()
    {
        preg_match(Rule::transpile('/{page}.{format:xml|json}'), '/users', $matches);
        $this->assertArrayNotHasKey('page', $matches);
        $this->assertArrayNotHasKey('format', $matches);
    }

    public function testCustomTypesFilterOutStringsNotMatchingType()
    {
        preg_match(Rule::transpile('/{page}.{format:xml|json}'), '/users.html', $matches);
        $this->assertArrayNotHasKey('page', $matches);
        $this->assertArrayNotHasKey('format', $matches);
    }

    public function testCustomTypesUsingDigits()
    {
        preg_match(Rule::transpile('/users/{pagenum:\d+}'), '/users/one', $matches);
        $this->assertArrayNotHasKey('page', $matches);
        $this->assertArrayNotHasKey('format', $matches);
    }

    public function testCustomTypesUsingDigitThatMatches()
    {
        preg_match(Rule::transpile('/users/{pagenum:\d}'), '/users/4', $matches);
        $this->assertArrayHasKey('pagenum', $matches);
        $this->assertEquals('4', $matches['pagenum']);
    }

    public function testCustomTypesUsingDigitsThatMatch()
    {
        preg_match(Rule::transpile('/users/{pagenum:\d\d\d}'), '/users/234', $matches);
        $this->assertArrayHasKey('pagenum', $matches);
        $this->assertEquals('234', $matches['pagenum']);
    }

    public function testCanMatchToARequestObject()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');

        $this->assertTrue(Rule::create(Rule::transpile('/{page}'))
            ->matches($req)[0]);
    }

    public function testRequestMethodIsCheckedIfIncludedInRuleInfo()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');

        $this->assertFalse(Rule::create(Rule::transpile('/{page}'),
            [ 'method' => Verb::PUT ])
            ->matches($req)[0]);
    }

    public function testRequestMethodIsProperlyCompared()
    {
        $req = new Request;
        $req->setMethod(Verb::PUT);
        $req->setUri('/mypage');

        $this->assertTrue(Rule::create(Rule::transpile('/{page}'),
            [ 'method' => Verb::PUT ])
            ->matches($req)[0]);
    }

    public function testTemplateSetterTranspilesToExpression()
    {
        $template = '/list/{country}/{state}';
        $this->rule->setTemplate($template);
        $this->assertEquals($template, $this->rule->getTemplate());
        $this->assertEquals(Rule::transpile($template), $this->rule->getExpression());
    }

    public function testHttpMethodsCanBePassedInWithTemplates()
    {
        $this->rule->setTemplate('GET /users');
        $info = $this->rule->getInformation();
        $this->assertTrue(isset($info['method']));
        $this->assertEquals('GET', $info['method']);
    }

    public function testHttpMethodsCanBePassedInWithTemplatesAndBeSeparatedByMultipleSpaces()
    {
        $this->rule->setTemplate('GET                  /users');
        $info = $this->rule->getInformation();
        $this->assertTrue(isset($info['method']));
        $this->assertEquals('GET', $info['method']);
    }
}
