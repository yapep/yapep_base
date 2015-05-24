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


use YapepBase\Request\HttpRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Exception\ControllerException;

use YapepBaseTest\Mock\Response\OutputMock;

/**
 * Test for the default error controller
 *
 * @package    YapepBaseTest
 * @subpackage Test\Controller
 */
class ErrorControllerTest extends \YapepBaseTest\BaseTest {

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

	public function testErrors() {
		/** @var HttpResponse $response */
		$response = null;

		// 404 handling
		$controller = $this->getController($response);
		$controller->run(404);

		$this->assertContains('Page not found', $response->getRenderedBody(), 'Invalid output from action');
		$this->assertSame(404, $response->getStatusCode(), 'Invalid status code after action has run');

		// 500 handling
		$controller = $this->getController($response);
		$controller->run(500);

		$this->assertContains('Internal server error', $response->getRenderedBody(), 'Invalid output from action');
		$this->assertSame(500, $response->getStatusCode(), 'Invalid status code after action has run');

		// Not existing action test
		$controller = $this->getController($response);
		// Missing action will trigger an error, silence it
		@$controller->run(403);

		// Missing error code action should produce a 500 error
		$this->assertContains('Internal server error', $response->getRenderedBody(), 'Invalid output from action');
		$this->assertSame(500, $response->getStatusCode(), 'Invalid status code after action has run');

		$controller = $this->getController($response, '\YapepBaseTest\Mock\Controller\ErrorControllerMock');
		$controller->actionClosure = function () {
			return array('test');
		};

		try {
			$controller->run(500);
			$this->fail('The run method should thow a ControllerException');
		} catch (ControllerException $e) {
		}
	}

	/**
	 * Instantiates a new controller
	 *
	 * @param \YapepBase\Response\HttpResponse $response              The response object. (outgoing param)
	 * @param string                           $controllerClassName   The name of the instantiated class
	 *
	 * @return \YapepBase\Controller\DefaultErrorController
	 */
	public function getController(
		&$response = null, $controllerClassName = '\YapepBase\Controller\DefaultErrorController'
	) {
		$request    = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array(), false);
		$output     = new OutputMock();
		$response   = new HttpResponse($output);
		return new $controllerClassName($request, $response);
	}
}