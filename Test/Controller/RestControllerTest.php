<?php

namespace YapepBase\Controller;

/**
 * Test class for RestController.
 * Generated by PHPUnit on 2011-12-15 at 10:41:00.
 */
class RestControllerTest extends \PHPUnit_Framework_TestCase {
    function testXml() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \YapepBase\Request\HttpRequest();
        $out = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($out);
        $c = new \YapepBase\Test\Mock\Controller\RestMockController($request, $response);
        $c->run('xml');
        $response->send();
        $this->assertEquals(array('application/xml; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('<rest><test1>test</test1></rest>', $out->out);
    }

    function testJson() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \YapepBase\Request\HttpRequest();
        $out = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($out);
        $c = new \YapepBase\Test\Mock\Controller\RestMockController($request, $response);
        $c->run('json');
        $response->send();
        $this->assertEquals(array('application/json; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('{"test1":"test"}', $out->out);
    }

    function testString() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \YapepBase\Request\HttpRequest();
        $out = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($out);
        $c = new \YapepBase\Test\Mock\Controller\RestMockController($request, $response);
        $c->run('string');
        $response->send();
        $this->assertEquals(array('text/plain; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('test', $out->out);
    }

    function testUnknown() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \YapepBase\Request\HttpRequest();
        $out = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($out);
        $c = new \YapepBase\Test\Mock\Controller\RestMockController($request, $response);
        $c->run('unknown');
        try {
            $response->send();
            $this->fail('Rendering with unsupported content type should result in a ViewException');
        } catch (\YapepBase\Exception\ViewException $e) { }
    }

    function testInvalid() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \YapepBase\Request\HttpRequest();
        $out = new \YapepBase\Test\Mock\Response\OutputMock();
        $response = new \YapepBase\Response\HttpResponse($out);
        $c = new \YapepBase\Test\Mock\Controller\RestMockController($request, $response);
        try {
            $c->run('invalid');
            $this->fail('Running with non-view return value type should result in a ControllerException');
        } catch (\YapepBase\Exception\ControllerException $e) {
            $this->assertEquals(\YapepBase\Exception\ControllerException::ERR_INVALID_ACTION_RETURN_VALUE, $e->getCode());
        }
    }
}
