<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Controller;


use YapepBase\Application;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\ControllerException;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\HttpResponse;

use YapepBaseTest\Mock\Router\RouterMock;
use YapepBaseTest\Mock\Response\OutputMock;
use YapepBaseTest\Mock\Controller\HttpMockController;
use YapepBaseTest\Mock\Response\ResponseMock;
use YapepBaseTest\Mock\Request\RequestMock;

/**
 * Test class for HttpController.
 */
class HttpControllerTest extends \YapepBaseTest\BaseTest {

	protected $originalObLevel;

	protected function setUp() {
		parent::setUp();
		$this->originalObLevel = ob_get_level();
	}

	protected function tearDown() {
		parent::tearDown();
		while (ob_get_level() > $this->originalObLevel) {
			ob_end_flush();
		}
	}

	protected function resetObToLevel($level) {
		while (ob_get_level() > $level) {
			ob_end_flush();
		}
	}

	function testConstructor() {
		$previousLevel = ob_get_level();


		try {
			$request = new RequestMock('');
			$response = new HttpResponse();
			$o = new HttpMockController($request, $response);
			$this->resetObToLevel($previousLevel);
			$this->fail('Passing a non-HTTP request to the HttpController should result in a ControllerException');
		} catch (ControllerException $e) {
			$this->resetObToLevel($previousLevel);
			$this->assertEquals(ControllerException::ERR_INCOMPATIBLE_REQUEST, $e->getCode());
		}

		try {
			$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
			$response = new ResponseMock();
			$o = new HttpMockController($request, $response);
			$this->resetObToLevel($previousLevel);
			$this->fail('Passing a non-HTTP request to the HttpController should result in a ControllerException');
		} catch (ControllerException $e) {
			$this->resetObToLevel($previousLevel);
			$this->assertEquals(ControllerException::ERR_INCOMPATIBLE_RESPONSE, $e->getCode());
		}

		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$response = new HttpResponse(new OutputMock());
		$o = new HttpMockController($request, $response);
		$this->resetObToLevel($previousLevel);
	}

	function testRedirect() {
		$previousLevel = ob_get_level();

		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$out = new OutputMock();
		$response = new HttpResponse($out);
		$o = new HttpMockController($request, $response);

		try {
			$o->testRedirect();
			$this->resetObToLevel($previousLevel);
			$this->fail('Redirect test should result in a RedirectException');
		} catch (RedirectException $e) {
			$this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
		}

		$response->send();
		$this->assertEquals(301, $out->responseCode);
		$this->assertEquals(array('http://www.example.com/'), $out->headers['Location']);

		$this->resetObToLevel($previousLevel);
	}

	function testRedirectToRoute() {
		$previousLevel = ob_get_level();

		$router = new RouterMock();
		Application::getInstance()->setRouter($router);
		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$out = new OutputMock();
		$response = new HttpResponse($out);
		$o = new HttpMockController($request, $response);

		try {
			$o->testRedirectToRoute();
			$this->resetObToLevel($previousLevel);
			$this->fail('RedirectToRoute test should result in a RedirectException');
		} catch (RedirectException $e) {
			$this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
		}

		$response->send();
		$this->assertEquals(303, $out->responseCode);
		$this->assertEquals(array('/?test=test&test2%5B0%5D=test1&test2%5B1%5D=test2#test'), $out->headers['Location']);

		$this->resetObToLevel($previousLevel);
	}
}
