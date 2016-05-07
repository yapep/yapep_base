<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Util
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Util;

/**
 * Mock class for \YapepBase\Util\Collection
 *
 * @codeCoverageIgnore
 */
class CollectionMock extends \YapepBase\Util\Collection {
	function typeCheck($element) {
		if (!$element instanceof CollectionElementMock) {
			throw new \YapepBase\Exception\TypeException($element,
				'\\YapepBase\\Test\\Mock\\Util\\CollectionElementMock');
		}
	}
}