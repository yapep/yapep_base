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


class ValueExceptionTest extends \YapepBaseTest\BaseTest {
	/**
	 * Test an exception throw
	 */
	public function testThrow() {
		try {
			$value = -1;
			$expected = 'positive integer';
			throw new \YapepBase\Exception\ValueException($value, $expected);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\ValueException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), (string)$value),
					'Value ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
			$this->assertNotEquals(false, \strpos($e->getMessage(), $expected),
					'Expected string ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
		}
	}
}