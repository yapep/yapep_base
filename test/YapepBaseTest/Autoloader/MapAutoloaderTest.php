<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Autoloader;


use YapepBase\Autoloader\MapAutoloader;

/**
 * Test class for MapAutoloader.
 *
 * @package    YapepBaseTest
 * @subpackage Autoloader
 */
class MapAutoloaderTest extends \YapepBaseTest\TestAbstract {

	/**
	 * @covers MapAutoloader::load()
	 */
	public function testLoadWhenClassGiven_shouldLoadItBasedOnTheGivenMap() {
		$className = 'MapAutoloaderTestClass';
		$classMap = array($className => $this->getPath($className));

		$autoloader = new MapAutoloader($classMap);

		$this->assertFalse(class_exists('MapAutoloaderTestClass', false));
		$autoloader->load('MapAutoloaderTestClass');

		$this->assertTrue(class_exists('MapAutoloaderTestClass', false));
	}

	/**
	 * @covers MapAutoloader::load()
	 */
	public function testLoadWhenInterfaceGiven_shouldLoadItBasedOnTheGivenMap() {
		$interfaceName = 'IMapAutoloaderTestInterface';
		$classMap = array($interfaceName => $this->getPath($interfaceName));

		$autoloader = new MapAutoloader($classMap);

		// Check in Interface
		$this->assertFalse(interface_exists('IMapAutoloaderTestInterface', false));
		$autoloader->load('IMapAutoloaderTestInterface');
		$this->assertTrue(interface_exists('IMapAutoloaderTestInterface', false));
	}


	protected function getPath($className) {
		return TEST_DIR . DIRECTORY_SEPARATOR
			. 'YapepBaseTest' . DIRECTORY_SEPARATOR
			. 'TestData' . DIRECTORY_SEPARATOR
			. 'Autoloader' . DIRECTORY_SEPARATOR
			. 'Test' . DIRECTORY_SEPARATOR
			. $className . '.php';
	}
}