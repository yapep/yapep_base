<?php

namespace YapepBase\Controller;
use YapepBase\Exception\ControllerException;

use YapepBase\Exception\ViewException;

use YapepBase\Test\Mock\Controller\RestMockController;

use YapepBase\Response\HttpResponse;

use YapepBase\Test\Mock\Response\OutputMock;

use YapepBase\Request\HttpRequest;

/**
 * Test class for RestController.
 * Generated by PHPUnit on 2011-12-15 at 10:41:00.
 */
class RestControllerTest extends \PHPUnit_Framework_TestCase {

    /**
     * Returns an HttpRequest instance
     *
     * @param string $uri
     * @param string $method
     *
     * @return \YapepBase\Request\HttpRequest
     */
    protected function getRequest($method = 'GET', $uri = '/') {
        $server = array(
            'REQUEST_URI'    => $uri,
            'REQUEST_METHOD' => $method,
        );
        return new HttpRequest(array(), array(), array(), $server, array(), array());
    }

    function testXml() {
        $request = $this->getRequest();
        $out = new OutputMock();
        $response = new HttpResponse($out);
        $c = new RestMockController($request, $response);
        $c->run('xml');
        $response->send();
        $this->assertEquals(array('application/xml; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('<rest><test1>test</test1></rest>', $out->out);
    }

    function testJson() {
        $request = $this->getRequest();
        $out = new OutputMock();
        $response = new HttpResponse($out);
        $c = new RestMockController($request, $response);
        $c->run('json');
        $response->send();
        $this->assertEquals(array('application/json; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('{"test1":"test"}', $out->out);
    }

    function testString() {
        $request = $this->getRequest();
        $out = new OutputMock();
        $response = new HttpResponse($out);
        $c = new RestMockController($request, $response);
        $c->run('string');
        $response->send();
        $this->assertEquals(array('text/plain; charset=UTF-8'), $out->headers['Content-Type']);
        $this->assertEquals('test', $out->out);
    }

    function testUnknown() {
        $request = $this->getRequest();
        $out = new OutputMock();
        $response = new HttpResponse($out);
        $c = new RestMockController($request, $response);
        $c->run('unknown');
        try {
            $response->send();
            $this->fail('Rendering with unsupported content type should result in a ViewException');
        } catch (ViewException $e) { }
    }

    function testInvalid() {
        $request = $this->getRequest();
        $out = new OutputMock();
        $response = new HttpResponse($out);
        $c = new RestMockController($request, $response);
        try {
            $c->run('invalid');
            $this->fail('Running with non-view return value type should result in a ControllerException');
        } catch (ControllerException $e) {
            $this->assertEquals(ControllerException::ERR_INVALID_ACTION_RETURN_VALUE, $e->getCode());
        }
    }
}