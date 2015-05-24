<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Exception
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Exception;

use \YapepBase\Exception\RedirectException;


class RedirectExceptionTest extends \YapepBaseTest\TestAbstract {

	/**
	 * @covers \YapepBase\Exception\RedirectException
	 */
	public function testTarget() {
		$e = new RedirectException('http://www.example.com/', RedirectException::TYPE_EXTERNAL);
		$this->assertEquals('http://www.example.com/', $e->getTarget());
		$this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
	}
}
