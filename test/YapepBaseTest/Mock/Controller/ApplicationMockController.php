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
 * This is the mock controller class for the ApplicationTest
 * @codeCoverageIgnore
 */
class ApplicationMockController extends \YapepBase\Controller\ControllerAbstract {
	public function doTest() {
		return 'test';
	}
}