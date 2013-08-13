<?php

namespace Efficio\Tests\Http;

use Efficio\Http\Response;
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
        $this->res->setHeader('X-Test', true);
        $headers = $this->res->getHeaders();
        $this->assertTrue(isset($headers['X-Test']));
        $this->assertTrue($headers['X-Test']);
    }

    public function testHeadersCanBeRemoved()
    {
        $this->res->setHeader('X-Test', true);
        $this->res->unsetHeader('X-Test');
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
        $this->res->setHeader('Content-Type', 'someother/type');
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
        $this->res->setContentType(Response::TEXT);
        $this->res->setContent('hi');
        $this->expectOutputString('hi');
        print $this->res;
    }

    public function testHtmlOutputIsSentAsPlainText()
    {
        $this->res->setContentType(Response::HTML);
        $this->res->setContent('hi');
        $this->expectOutputString('hi');
        print $this->res;
    }

    public function testJsonOutputIsJsonEncoded()
    {
        $content = [ 'one' => true ];
        $this->res->setContentType(Response::JSON);
        $this->res->setContent($content);
        $this->expectOutputString(json_encode($content));
        print $this->res;
    }
}

