<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\RuleBook;
use Efficio\Http\Request;
use Efficio\Http\Verb;
use Efficio\Http\Error\DuplicateRuleException;
use PHPUnit_Framework_TestCase;
use Exception;

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
        $this->rulebook->add(Rule::create('/one/', [ 'test' => true ]));
        $this->rulebook->add(Rule::create('/somestring/', [ 'test' => true ]));
        $this->rulebook->add(Rule::create('/two/', [ 'test' => true ]));
        $info = $this->rulebook->matching('somestring');
        $this->assertTrue(is_array($info));
    }

    public function testBaseInformationIsReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create('/somestring/', [ 'test' => true ]));
        $info = $this->rulebook->matching('somestring');
        $this->assertArrayHasKey('test', $info);
        $this->assertTrue($info['test']);
    }

    public function testPatternGroupsAreReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'test' => true ]));
        $info = $this->rulebook->matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }

    public function testPatternGroupsOverwriteBaseInfoAreReturnedOnMatch()
    {
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]));
        $info = $this->rulebook->matching('api/users');
        $this->assertArrayHasKey('model', $info);
        $this->assertEquals('users', $info['model']);
    }

    /**
     * @expectedException Efficio\Http\Error\DuplicateRuleException
     */
    public function testAddingDuplciateRulesTriggersException()
    {
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]));
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]));
    }

    /**
     * @expectedException Efficio\Http\Error\DuplicateRuleException
     */
    public function testAddingDuplicateRulesTriggersAnExceptionWithMultiplePatterns()
    {
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]));
        $this->rulebook->add(Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]));
    }

    public function testAddingDuplicateRulesTrggersADuplicateRuleException()
    {
        try {
            $this->rulebook->add(Rule::create(Rule::transpile('/api/{model}/{id}'), [ 'model' => '...' ]));
            $this->rulebook->add(Rule::create(Rule::transpile('/api/{model}/{id}'), [ 'model' => '...' ]));
            $this->fail();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof DuplicateRuleException);
        }
    }

    public function testDuplicateRuleExceptionsIncludeRuleObjectThatTriggeredError()
    {
        $one = Rule::create(Rule::transpile('/api/{model}/{id}'), [ 'model' => '...' ]);
        $two = Rule::create('/api\/(?P<model>[A-Za-z]+)/', [ 'model' => '...' ]);

        try {
            $this->rulebook->add($one);
            $this->rulebook->add($two);
        } catch (DuplicateRuleException $e) {
            $this->assertEquals($two, $e->getRule());
        } catch (Exception $e) {
            $this->fail();
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRuleBookOnlyLoadsArraysOrObjectsOfRoutes()
    {
        $this->rulebook->load('hi');
    }

    /**
     * @expectedException Efficio\Http\Error\DuplicateRuleException
     */
    public function testLoadingDuplicatesTriggersError()
    {
        $this->rulebook->load([ '/users' => [] ]);
        $this->rulebook->load([ '/users' => [] ]);
    }

    public function testRuleInformationCanBeMergedIntoRequestObject()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');
        $rand = mt_rand();

        $rule = Rule::create('/mypage', [ 'random' => $rand ], true);
        $this->rulebook->add($rule);

        $this->rulebook->matching($req, true);
        $this->assertEquals($rand, $req->random);
    }

    public function testRuleIsSavedToRequest()
    {
        $req = new Request;
        $req->setMethod(Verb::GET);
        $req->setUri('/mypage');
        $rand = mt_rand();

        $rule = Rule::create('/mypage', [ 'random' => $rand ], true);
        $this->rulebook->add($rule);

        $this->rulebook->matching($req, true);
        $this->assertEquals($rule, $req->getRule());
    }
}
