<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Controller;

/**
 * @codeCoverageIgnore
 */
class HttpMockController extends \YapepBase\Controller\HttpControllerAbstract {
	public function testRedirect() {
		$this->redirectToUrl('http://www.example.com/', 301);
	}

	public function testRedirectToRoute() {
		$this->redirectToRoute('Test', 'test', array(), array('test' => 'test', 'test2' => array('test1', 'test2')), 'test');
	}
}