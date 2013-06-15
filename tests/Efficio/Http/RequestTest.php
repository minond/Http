<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Verb;
use Efficio\Http\Request;
use Efficio\Test\Mocks\Http\RequestInputAccess;
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
        $args = [ 'testing' => 'true' ];
        $this->req->setArguments($args);
        $this->assertEquals($args, $this->req->getArguments());
    }

    public function testArgumentCanBeAccessedLikeProperties()
    {
        $args = [ 'testing' => true ];
        $this->req->setArguments($args);
        $this->assertTrue($this->req->testing);
    }

    public function testArgumentAccessedLikePropertiesThatDontExistsReturnNull()
    {
        $args = [ 'testing' => true ];
        $this->req->setArguments($args);
        $this->assertNull($this->req->testing123);
    }

    public function testArgumentCanBeUpdates()
    {
        $args = [ 'testing' => true ];
        $this->req->setArguments($args);
        $this->assertTrue($this->req->testing);
        $this->req->set('testing', false);
        $this->assertFalse($this->req->testing);
    }

    public function testArgumentCanBeFound()
    {
        $args = [ 'testing' => true ];
        $this->req->setArguments($args);
        $this->assertTrue($this->req->has('testing'));
        $this->assertFalse($this->req->has('testing123'));
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
        $uri = $_SERVER['SCRIPT_NAME'] = '/index';
        $port = $_SERVER['SERVER_PORT'] = '8080';
        $method = $_SERVER['REQUEST_METHOD'] = Verb::POST;

        $req = Request::create();

        $this->assertEquals($args, $req->getArguments());
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
}
