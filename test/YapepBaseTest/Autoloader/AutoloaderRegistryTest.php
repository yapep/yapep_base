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


use Mockery;

use YapepBaseTest\Mock\Autoloader\AutoloaderRegistryMock;


/**
 * Test class for AutoloaderRegistry.
 *
 * @package    YapepBaseTest
 * @subpackage Autoloader
 */
class AutoloaderRegistryTest extends \YapepBaseTest\TestAbstract {

	/**
	 * @var AutoloaderRegistryMock
	 */
	protected $autoloaderRegistry;


	public function setUp() {
		parent::setUp();

		$this->autoloaderRegistry = new AutoloaderRegistryMock();
	}

	/**
	 * @covers AutoloaderRegistry::addAutoloader()
	 */
	public function testAddAutoloader_theGivenAutoloaderShouldBeRegisteredAtTheEnd() {
		$autoloader = $this->getAutoloader();
		$autoloader->isFirst = true;

		$this->autoloaderRegistry->addAutoloader($autoloader);
		$this->autoloaderRegistry->addAutoloader($this->getAutoloader());

		$this->assertObjectHasAttribute('isFirst', $this->autoloaderRegistry->registeredAutoloaders[0]);
		$this->assertObjectNotHasAttribute('isFirst', $this->autoloaderRegistry->registeredAutoloaders[1]);
	}

	/**
	 * @covers AutoloaderRegistry::prependAutoloader()
	 */
	public function testPrependAutoloader_shouldRegisterTheAutoloaderAtTheVeryBeginning() {
		$autoloader = $this->getAutoloader();
		$autoloader->isFirst = true;

		$this->autoloaderRegistry->addAutoloader($this->getAutoloader());
		$this->autoloaderRegistry->prependAutoloader($autoloader);

		$this->assertObjectHasAttribute('isFirst', $this->autoloaderRegistry->registeredAutoloaders[0]);
		$this->assertObjectNotHasAttribute('isFirst', $this->autoloaderRegistry->registeredAutoloaders[1]);
	}

	/**
	 * @covers AutoloaderRegistry::clear()
	 */
	public function testClear_shouldRemoveRegisteredAutoloaders() {
		$this->autoloaderRegistry->addAutoloader($this->getAutoloader());

		$this->autoloaderRegistry->clear();

		$this->assertEmpty($this->autoloaderRegistry->registeredAutoloaders);
	}

	/**
	 * @covers AutoloaderRegistry::clear()
	 */
	public function testLoad_shouldFallbackToNextLoaderIfFirstFails() {
		$className = 'testClass';

		$this->autoloaderRegistry->addAutoloader($this->getAutoloaderAndExpectLoad($className, false));
		$this->autoloaderRegistry->addAutoloader($this->getAutoloaderAndExpectLoad($className, true));

		$result = $this->autoloaderRegistry->load($className);

		$this->assertTrue($result);
	}

	/**
	 * @return \YapepBase\Autoloader\IAutoloader
	 */
	protected function getAutoloader() {
		return Mockery::mock('\YapepBase\Autoloader\IAutoloader');
	}

	/**
	 * @return \Mockery\MockInterface
	 */
	protected function getAutoloaderAndExpectLoad($className, $expectedResult) {
		return Mockery::mock('\YapepBase\Autoloader\IAutoloader')
			->shouldReceive('load')
				->once()
				->with($className)
				->andReturn($expectedResult)
				->getMock();
	}
}