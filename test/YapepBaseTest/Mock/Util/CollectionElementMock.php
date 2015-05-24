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
 * Mock class for CollectionElement
 *
 * @codeCoverageIgnore
 */
class CollectionElementMock {
	protected $id;
	function __construct() {
		$this->id = uniqid('', true);
	}
}