<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Response;
use Efficio\Http\Status;
use PHPUnit_Framework_TestCase;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    public $res;

    public function setUp()
    {
        $this->res = new Response;
    }

    public function testHeadersCanBeAdded()
    {
        $this->res->header['X-Test'] = true;
        $headers = $this->res->getHeaders();
        $this->assertTrue(isset($headers['X-Test']));
        $this->assertTrue($headers['X-Test']);
    }

    public function testHeadersCanBeRemoved()
    {
        $this->res->header['X-Test'] = true;
        unset($this->res->header['X-Test']);
        $headers = $this->res->getHeaders();
        $this->assertFalse(isset($headers['X-Test']));
    }

    public function testStandardHeadersAreSet()
    {
        $this->res->setContentType(Response::TEXT);
        $headers = $this->res->getHeaders();
        $this->assertEquals('text/plain', $headers['Content-Type']);
    }

    public function testStandardHeadersCanBeOverwritten()
    {
        $this->res->header['Content-Type'] = 'someother/type';
        $headers = $this->res->getHeaders();
        $this->assertEquals('someother/type', $headers['Content-Type']);
    }

    public function testContentCanBeSetAndRetrieved()
    {
        $this->res->setContent('hi');
        $this->assertEquals('hi', $this->res->getContent());
    }

    public function testHtmlIsTheDefaultContentType()
    {
        $this->assertEquals(Response::HTML, $this->res->getContentType());
    }

    public function testContentTypeCanBeSetAndRetrieved()
    {
        $this->res->setContentType(Response::JSON);
        $this->assertEquals(Response::JSON, $this->res->getContentType());
    }

    public function testTextOutputIsSentAsPlainText()
    {
        $this->expectOutputString('hi');
        $this->res->setContentType(Response::TEXT);
        $this->res->setContent('hi');
        $this->res->sendContent();
    }

    public function testHtmlOutputIsSentAsPlainText()
    {
        $this->expectOutputString('hi');
        $this->res->setContentType(Response::HTML);
        $this->res->setContent('hi');
        $this->res->sendContent();
    }

    public function testJsonOutputIsJsonEncoded()
    {
        $content = [ 'one' => true ];
        $this->expectOutputString(json_encode($content));
        $this->res->setContentType(Response::JSON);
        $this->res->setContent($content);
        $this->res->sendContent();
    }

    public function testStatusCodeGetternAndSetter()
    {
        $this->res->setStatusCode(Status::NOT_FOUND);
        $this->assertEquals(Status::NOT_FOUND, $this->res->getStatusCode());
    }
}

