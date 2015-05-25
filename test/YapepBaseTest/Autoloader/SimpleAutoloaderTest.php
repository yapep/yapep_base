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


use YapepBaseTest\Mock\Autoloader\SimpleAutoloaderMock;

/**
 * Test class for SimpleAutoloader.
 *
 * @package    YapepBaseTest
 * @subpackage Autoloader
 */
class SimpleAutoloaderTest extends \YapepBaseTest\TestAbstract {

	/**
	 * @var SimpleAutoloaderMock
	 */
	protected $simpleAutoloader;


	protected function setUp() {
		parent::setUp();

		$this->simpleAutoloader = new SimpleAutoloaderMock();
	}

	/**
	 * @covers SimpleAutoloader::addClassPath()
	 */
	public function testAddClassPathWithSingleValue_shouldBeRegistered() {
		$classPath = '/test';
		$this->simpleAutoloader->addClassPath($classPath);

		$this->assertEquals($classPath, $this->simpleAutoloader->classPaths[0]);
	}

	/**
	 * @covers SimpleAutoloader::addClassPath()
	 */
	public function testAddClassPathWithMultipleValues_shouldRegisterAll() {
		$classPaths = array('/test1', '/test2');
		$this->simpleAutoloader->addClassPath($classPaths);

		$this->assertEquals($classPaths[0], $this->simpleAutoloader->classPaths[0]);
		$this->assertEquals($classPaths[1], $this->simpleAutoloader->classPaths[1]);
	}

	/**
	 * @covers SimpleAutoloader::addClassPath()
	 */
	public function testAddClassPathWithForcedNamespace_shouldRegisterToGivenNamespace() {
		$classPath = '/test1';
		$this->simpleAutoloader->addClassPath($classPath, '\\test');

		$this->assertEquals($classPath, $this->simpleAutoloader->classPathsWithNamespace['test']);
	}

	/**
	 * @covers SimpleAutoloader::getPaths()
	 */
	public function testGetPathsWhenNamespaceNotForced_shouldReturnEveryPossibleClassPath() {
		$classPath1 = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path1';
		$classPath2 = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path2' . DIRECTORY_SEPARATOR;

		$this->simpleAutoloader->addClassPath($classPath1);
		$this->simpleAutoloader->addClassPath($classPath2);

		$filePaths = $this->simpleAutoloader->getPaths('Test\TestClass');
		$expectedResult = array(
			$classPath1 . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'TestClass.php',
			$classPath2 . 'Test' . DIRECTORY_SEPARATOR . 'TestClass.php',
		);
		sort($filePaths);
		sort($expectedResult);

		$this->assertEquals($expectedResult, $filePaths);
	}

	/**
	 * @covers SimpleAutoloader::getPaths()
	 */
	public function testGetPathsWhenNamespaceForced_shouldReturnOnlyForcedPath() {
		$classPath = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path1';
		$forcedClassPath = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path2';

		$this->simpleAutoloader->addClassPath($classPath);
		$this->simpleAutoloader->addClassPath($forcedClassPath, '\Test\NamespaceForced');

		$filePaths = $this->simpleAutoloader->getPaths('Test\NamespaceForced\TestClass');
		$expectedResult = array(
			$forcedClassPath . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'NamespaceForced'
				. DIRECTORY_SEPARATOR . 'TestClass.php',
		);

		$this->assertSame($expectedResult, $filePaths,
			'In case of the NamespaceForced loading only files from the given directory should be loaded');
	}

	/**
	 * @covers SimpleAutoloader::getPaths()
	 */
	public function testLoadClassWithNonExistentFile_shouldReturnFalse() {
		$className = '\\YapepBaseTest\\NonExistent';
		$this->assertFalse(class_exists($className, false));
		$result = $this->simpleAutoloader->loadClass('/nonExistent', $className);
		$this->assertFalse($result);
	}

	/**
	 * @covers SimpleAutoloader::getPaths()
	 */
	public function testLoadClassWithExistentClass_shouldReturnTrueAndLoadClass() {
		$className = '\\YapepBaseTest\\TestData\\Autoloader\\Test\\AutoloaderTestClass';
		$this->assertFalse(class_exists($className, false));

		$result = $this->simpleAutoloader->loadClass($this->getTestClassPath('AutoloaderTestClass'), $className);

		$this->assertTrue(class_exists($className, false));
		$this->assertTrue($result);
	}

	/**
	 * @covers SimpleAutoloader::getPaths()
	 */
	public function testLoadClassWithExistentInterface_shouldReturnTrueAndLoadInterface() {
		$interfaceName = '\\YapepBaseTest\\TestData\\Autoloader\\Test\\IAutoloaderTestInterface';
		$this->assertFalse(class_exists($interfaceName, false));

		$result = $this->simpleAutoloader->loadClass($this->getTestClassPath('IAutoloaderTestInterface'), $interfaceName);

		$this->assertTrue(interface_exists($interfaceName, false));
		$this->assertTrue($result);
	}


	protected function getTestClassPath($className) {
		return TEST_DIR . DIRECTORY_SEPARATOR
			. 'YapepBaseTest' . DIRECTORY_SEPARATOR
			. 'TestData' . DIRECTORY_SEPARATOR
			. 'Autoloader' . DIRECTORY_SEPARATOR
			. 'Test' . DIRECTORY_SEPARATOR
			. $className . '.php';
	}
}