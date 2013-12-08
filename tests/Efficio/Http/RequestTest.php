<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Rule;
use Efficio\Http\Verb;
use Efficio\Http\Request;
use Efficio\Test\Mocks\Http\RequestInputAccess;
use Efficio\Utilitatis\PublicObject;
use PHPUnit_Framework_TestCase;

require_once './tests/mocks/RequestInputAccess.php';

class RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    public $req;

    /**
     * creates a new request
     */
    public function setUp()
    {
        $this->req = new Request;
    }

    public function testPortGetterAndSetter()
    {
        $port = '80';
        $this->req->setPort($port);
        $this->assertEquals($port, $this->req->getPort());
    }

    public function testUrlGetterAndSetter()
    {
        $uri = '/index';
        $this->req->setUri($uri);
        $this->assertEquals($uri, $this->req->getUri());
    }

    public function testMethodGetterAndSetter()
    {
        $method = Verb::GET;
        $this->req->setMethod($method);
        $this->assertEquals($method, $this->req->getMethod());
    }

    public function testMethodGetterAndSetterWithInvalidCase()
    {
        $method = strtolower(Verb::GET);
        $this->req->setMethod($method);
        $this->assertEquals(Verb::GET, $this->req->getMethod());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSettingInvalidMethodsTriggersError()
    {
        $this->req->setMethod('invalid');
    }

    public function testArgumentGetterAndSetter()
    {
        $params = new PublicObject([ 'testing' => 'true' ]);
        $this->req->setParameters($params);
        $this->assertEquals($params, $this->req->getParameters());
    }

    public function testArgumentCanBeAccessedLikeProperties()
    {
        $params = new PublicObject([ 'testing' => true ]);
        $this->req->setParameters($params);
        $this->assertTrue($this->req->param->testing);
    }

    public function testArgumentCanBeUpdates()
    {
        $params = new PublicObject([ 'testing' => true ]);
        $this->req->setParameters($params);
        $this->assertTrue($this->req->param->testing);
        $this->req->param->testing = false;
        $this->assertFalse($this->req->param->testing);
    }

    public function testArgumentCanBeFound()
    {
        $params = new PublicObject([ 'testing' => true ]);
        $this->req->setParameters($params);
        $this->assertTrue(isset($this->req->param->testing));
        $this->assertFalse(isset($this->req->param->testing123));
    }

    public function testInputGetterAndSetter()
    {
        $input = 'testing';
        $this->req->setInput($input);
        $this->assertEquals($input, $this->req->getInput());
    }

    public function testStaticCreateMethodReadsExpectedValues()
    {
        $args = $_REQUEST = [ 'testing' => 'true' ];
        $params = new PublicObject($args);
        $_SERVER['REQUEST_URI'] = '/index?test';
        $uri = '/index';
        $port = $_SERVER['SERVER_PORT'] = '8080';
        $method = $_SERVER['REQUEST_METHOD'] = Verb::POST;

        $req = Request::create();

        $this->assertEquals($params, $req->getParameters());
        $this->assertEquals($uri, $req->getUri());
        $this->assertEquals($port, $req->getPort());
        $this->assertEquals($method, $req->getMethod());
    }

    public function testReadingInputForFirstTimeReadsFromPHPsInput()
    {
        RequestInputAccess::setInputRead(false);
        $this->assertFalse(RequestInputAccess::getInputRead());
        $req = new RequestInputAccess;
        $req->getInput();
        $this->assertTrue(RequestInputAccess::getInputRead());
    }

    public function testRuleGetterAndSetter()
    {
        $rule = new Rule;
        $this->req->setRule($rule);
        $this->assertEquals($rule, $this->req->getRule());
    }
}
