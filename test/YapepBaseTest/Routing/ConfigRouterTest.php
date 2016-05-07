<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Routing
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBaseTest\Routing;


use YapepBase\Router\ConfigRouter;
use YapepBase\Config;
use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;

use YapepBaseTest\Mock\Request\RequestMock;


/**
 * ConfigRouterTest class
 *
 * @package    YapepBaseTest
 * @subpackage subpackage
 */
class ConfigRouterTest extends ArrayRouterTest {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
		Config::getInstance()->set('resource.routing.routes', $this->routes);
		Config::getInstance()->set('resource.routing.badRoutes', 'test');
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		Config::getInstance()->clear();
		parent::tearDown();
	}

	/**
	 * Returns a router object, with the request set up to the provided target and method.
	 *
	 * @param string      $target
	 * @param string      $method
	 * @param RequestMock $request   The request instance for the router. (Outgoing parameter)
	 *
	 * @return \YapepBase\Router\ConfigRouter
	 */
	protected function getRouter(
		$target = '', $method = IRequest::METHOD_HTTP_GET, &$request = null, $configName = 'routes'
	) {
		$request = new RequestMock(preg_replace('/^\s*\[[^]]*\]/', '', $target), $method);
		return new ConfigRouter($request, $configName);
	}

	/**
	 * Tests whether the correct error is thrown for a bad config
	 */
	public function testNonExistingError() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_ROUTE_CONFIG);

		$request = null;
		$this->getRouter('', IRequest::METHOD_HTTP_GET, $request, 'badRoutes');
	}
 }
