<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Mock\Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Autoloader;


use YapepBase\Autoloader\AutoloaderRegistry;

/**
 * Mock class for the AutoloaderRegistry.
 *
 * @package    YapepBaseTest
 * @subpackage Mock\Autoloader
 */
class AutoloaderRegistryMock extends AutoloaderRegistry {

	/**
	 * Stores the registered autoloader objects.
	 *
	 * @var array
	 */
	public $registeredAutoloaders = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
	}
}