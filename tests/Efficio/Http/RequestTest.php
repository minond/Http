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

    public function testHeaderGetterAndSetter()
    {
        $headers = new PublicObject([ 'testing' => 'true' ]);
        $this->req->setHeaders($headers);
        $this->assertEquals($headers, $this->req->getHeaders());
    }

    public function testParameterGetterAndSetter()
    {
        $params = new PublicObject([ 'testing' => 'true' ]);
        $this->req->setParameters($params);
        $this->assertEquals($params, $this->req->getParameters());
    }

    public function testParameterCanBeAccessedLikeProperties()
    {
        $params = new PublicObject([ 'testing' => true ]);
        $this->req->setParameters($params);
        $this->assertTrue($this->req->param->testing);
    }

    public function testParameterCanBeUpdates()
    {
        $params = new PublicObject([ 'testing' => true ]);
        $this->req->setParameters($params);
        $this->assertTrue($this->req->param->testing);
        $this->req->param->testing = false;
        $this->assertFalse($this->req->param->testing);
    }

    public function testParameterCanBeFound()
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

    /**
     * @backupGlobals enabled
     */
    public function testStaticCreateGetsHeaders()
    {
        // for headers
        $_SERVER = array_merge($_SERVER, [
            'HTTP_HOST' => 'localhost',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_COOKIE' => 'PHPSESSID=?',
        ]);

        $args = $_REQUEST = [ 'testing' => 'true' ];
        $params = new PublicObject($args);
        $_SERVER['REQUEST_URI'] = '/index?test';
        $uri = '/index';
        $port = $_SERVER['SERVER_PORT'] = '8080';
        $method = $_SERVER['REQUEST_METHOD'] = Verb::POST;

        $req = Request::create();
        $this->assertFalse(isset($req->header->{ 'Accept-Language?' }));
        $this->assertTrue(isset($req->header->Host));
        $this->assertTrue(isset($req->header->Connection));
        $this->assertTrue(isset($req->header->{ 'Cache-Control' }));
        $this->assertTrue(isset($req->header->Accept));
        $this->assertTrue(isset($req->header->{ 'User-Agent' }));
        $this->assertTrue(isset($req->header->{ 'Accept-Encoding' }));
        $this->assertTrue(isset($req->header->{ 'Accept-Language' }));
        $this->assertTrue(isset($req->header->Cookie));
        $this->assertEquals('PHPSESSID=?', $req->header->Cookie);
    }

    /**
     * @backupGlobals enabled
     */
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
