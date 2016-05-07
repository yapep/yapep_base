<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\BusinessObject
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\BusinessObject;


use YapepBase\BusinessObject\BoAbstract;

/**
 * Mock object for business objects
 *
 * @package    YapepBaseTest
 * @subpackage Mock\BusinessObject
 */
class MockBo extends BoAbstract {

	protected function getKeyForKeys() {
		return parent::getKeyForKeys();
	}

	public function getStorage() {
		return parent::getStorage();
	}

	public function getFromStorage($key) {
		return parent::getFromStorage($key);
	}

	public function setToStorage($key, $data, $ttl = 0, $forceEmptyStorage = false) {
		parent::setToStorage($key, $data, $ttl, $forceEmptyStorage);
	}

	public function deleteFromStorage($key = '') {
		parent::deleteFromStorage($key);
	}

}