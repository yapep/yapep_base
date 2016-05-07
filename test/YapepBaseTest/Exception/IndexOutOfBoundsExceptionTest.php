<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Exception
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHP\Lang;



class IndexOutOfBoundsExceptionTest extends \YapepBaseTest\TestAbstract {
	/**
	 * Tests throwing the exception with an offset.
	 */
	public function testThrowWithOffset() {
		try {
			$offset = 12;
			throw new \YapepBase\Exception\IndexOutOfBoundsException($offset);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), (string)$offset),
					'Offset ' . $offset . ' is not in message! Message was: ' . $e->getMessage());
		}
	}

	/**
	 * Tests throwing the exception without an offset
	 */
	public function testThrowWithoutOffset() {
		try {
			throw new \YapepBase\Exception\IndexOutOfBoundsException();
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) {
			$this->assertNotEquals('', $e->getMessage(), 'Message is empty!');
		}
	}
}
