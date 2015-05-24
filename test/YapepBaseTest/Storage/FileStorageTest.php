<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Storage
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Storage;


use Mockery;

use YapepBase\Config;
use YapepBase\Exception\File\Exception;
use YapepBase\Exception\File\NotFoundException;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\StorageException;
use YapepBase\Exception\ParameterException;
use YapepBase\File\FileHandlerPhp;

use YapepBaseTest\Mock\Storage\FileStorageMock;


/**
 * Test class for FileStorage.
 * Generated by PHPUnit on 2011-12-23 at 13:41:33.
 */
class FileStorageTest extends \YapepBaseTest\TestAbstract {

	/**
	 * TearDown
	 *
	 * return void
	 */
	protected function tearDown() {
		Config::getInstance()->clear();
		parent::tearDown();
	}

	/**
	 * Returns a FileHandlerPhp Mock object which can be used for the FileStorage instantiation.
	 *
	 * @param string $path   The path of the FileStorage.
	 *
	 * @return Mockery\MockInterface
	 */
	protected function getFileHandlerMock($path) {
		return Mockery::mock('\YapepBase\File\FileHandlerPhp')
			->shouldReceive('checkIsPathExists')->once()->with($path . '/')->andReturn(true)->getMock()
			->shouldReceive('checkIsDirectory')->once()->with($path)->andReturn(true)->getMock()
			->shouldReceive('checkIsWritable')->once()->with($path . '/')->andReturn(true)->getMock();
	}

	/**
	 * Sets the given storage connection to the config after clearing it.
	 *
	 * @param string $configName         Name of the config.
	 * @param string $path               The path of the Storage files.
	 * @param bool   $storePlainText     If TRUE the data will be stored as plain text.
	 * @param bool   $hashKey            If TRUE the keys wil be hashed.
	 * @param bool   $readOnly           If TRUE the Storage will be read only.
	 * @param string $filePrefix         Prefix of the file.
	 * @param string $fileSuffix         Suffix of the file.
	 * @param int    $fileMode           File handling mode (usually given in octal)
	 * @param bool   $debuggerDisabled   TRUE if the debugger should be disabled.
	 *
	 * @return void
	 */
	protected function setConfig($configName, $path, $storePlainText = null, $hashKey = null, $readOnly = null,
		$filePrefix = null, $fileSuffix = null, $fileMode = null, $debuggerDisabled = null
	) {
		$config = Config::getInstance();
		$config->clear();

		$config->set(array(
			'resource.storage.' . $configName . '.path'             => $path,
			'resource.storage.' . $configName . '.storePlainText'   => $storePlainText,
			'resource.storage.' . $configName . '.filePrefix'       => $filePrefix,
			'resource.storage.' . $configName . '.fileSuffix'       => $fileSuffix,
			'resource.storage.' . $configName . '.fileMode'         => $fileMode,
			'resource.storage.' . $configName . '.hashKey'          => $hashKey,
			'resource.storage.' . $configName . '.readOnly'         => $readOnly,
			'resource.storage.' . $configName . '.debuggerDisabled' => $debuggerDisabled,
		));
	}

	/**
	 * Tests the setupConfig() method.
	 *
	 * @return void
	 */
	public function testSetupConfig() {
		// Test without path given
		try {
			new FileStorageMock('test', new FileHandlerPhp());
			$this->fail('FileStorage should throw an Exception in case of no path given in the config!');
		}
		catch (ConfigException $e) {
		}

		// Test with valid configuration
		$path = 'test';
		$storePlainText = true;
		$filePrefix = 'prefix_';
		$fileSuffix = '_suffix';
		$fileMode = 0666;
		$hashKey = true;
		$readOnly = false;
		$debuggerDisabled = false;

		$this->setConfig('test', $path, $storePlainText, $hashKey, $readOnly, $filePrefix, $fileSuffix,
			$fileMode, $debuggerDisabled);

		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$this->assertEquals($path . '/', $fileStorage->getPath());
		$this->assertEquals($storePlainText, $fileStorage->getStorePlainText());
		$this->assertEquals($filePrefix, $fileStorage->getFilePrefix());
		$this->assertEquals($fileSuffix, $fileStorage->getFileSuffix());
		$this->assertEquals($fileMode, $fileStorage->getFileMode());
		$this->assertEquals($hashKey, $fileStorage->getHashKey());
		$this->assertEquals($readOnly, $fileStorage->getReadOnly());
		$this->assertEquals($debuggerDisabled, $fileStorage->getDebuggerDisabled());

		// The directory of the storage is not created yet and the storage can not create it
		$fileHandler = Mockery::mock('\YapepBase\File\FileHandlerPhp')
			->shouldReceive('checkIsPathExists')->once()->with($path . '/')->andReturn(false)->getMock()
			->shouldReceive('makeDirectory')->once()->with($path . '/', ($fileMode | 0111), true)
				->andThrow(new Exception('Directory creation failed!'))->getMock();

		try {
			new FileStorageMock('test', $fileHandler);
			$this->fail('FileStorage should throw an Exception in case it can not create the given directory!');
		}
		catch (StorageException $e) {
		}

		// The directory of the storage is not created yet, and the storage can create it
		$fileHandler = Mockery::mock('\YapepBase\File\FileHandlerPhp')
			->shouldReceive('checkIsPathExists')->once()->with($path . '/')->andReturn(false)->getMock()
			->shouldReceive('makeDirectory')->once()->with($path . '/', ($fileMode | 0111), true)->getMock()
			->shouldReceive('checkIsWritable')->once()->with($path . '/')->andReturn(true)->getMock();

		new FileStorageMock('test', $fileHandler);

		// The given path is not a directory
		$fileHandler = Mockery::mock('\YapepBase\File\FileHandlerPhp')
			->shouldReceive('checkIsPathExists')->once()->with($path . '/')->andReturn(true)->getMock()
			->shouldReceive('checkIsDirectory')->once()->with($path)->andReturn(false)->getMock();

		try {
			new FileStorageMock('test', $fileHandler);
			$this->fail('FileStorage should throw an Exception in case the given path is not a directory!');
		}
		catch (StorageException $e) {
		}

		// The given path is not writable
		$fileHandler = Mockery::mock('\YapepBase\File\FileHandlerPhp')
			->shouldReceive('checkIsPathExists')->once()->with($path . '/')->andReturn(true)->getMock()
			->shouldReceive('checkIsDirectory')->once()->with($path)->andReturn(true)->getMock()
			->shouldReceive('checkIsWritable')->once()->with($path . '/')->andReturn(false)->getMock();

		try {
			new FileStorageMock('test', $fileHandler);
			$this->fail('FileStorage should throw an Exception in case the given path is not writable!');
		}
		catch (StorageException $e) {
		}
	}

	/**
	 * Tests the makeFullPath() method.
	 *
	 * @return void
	 */
	public function testMakeFullPath() {
		$path = 'test';
		$filePrefix = 'prefix_';
		$fileSuffix = '_suffix';

		// Test with a valid request
		$this->setConfig('test', $path, null, true, null, $filePrefix, $fileSuffix);

		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$fullPath = $fileStorage->makeFullPath('test');
		$expectedFullPath = $path . '/' . md5($filePrefix . 'test' . $fileSuffix);
		$this->assertEquals($expectedFullPath, $fullPath);

		// Test with an invalid filename given
		$this->setConfig('test', $path, null, false, null, $filePrefix, $fileSuffix);

		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		try {
			$fileStorage->makeFullPath('test"');
			$this->fail('Storage should throw an exception if given filename is not valid!');
		}
		catch (StorageException $e) {
		}
	}

	/**
	 * Tests the set() method.
	 *
	 * @return void
	 */
	public function testSet() {
		$path = 'test';
		$testData = 'test';

		// Test with read only mode
		$this->setConfig('test', $path, null, null, true);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		try {
			$fileStorage->set('test', $testData);
			$this->fail('Storage should throw an Exception if you try to set in read only mode!');
		}
		catch (StorageException $e) {
		}

		// Test with planText and TTL set
		$this->setConfig('test', $path, true, false);

		$fileHandler = $this->getFileHandlerMock($path);

		$fileStorage = new FileStorageMock('test', $fileHandler);

		try {
			$fileStorage->set('test', $testData, 12);
			$this->fail('Storage set() should throw an Exception when used with TTL in plainText mode!');
		}
		catch (ParameterException $e) {
		}

		// Test with valid parameters but failed file write
		$this->setConfig('test', $path);

		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('write')->once()->andThrow(new Exception('Write failed'))->getMock();
		$fileStorage = new FileStorageMock('test', $fileHandler);

		try {
			$fileStorage->set('test', $testData);
			$this->fail('Storage set() should throw an Exception when the file write failed!');
		}
		catch (StorageException $e) {
		}

		// Test with valid parameters
		$this->setConfig('test', $path);
		$preparedTestData = $fileStorage->prepareData('test', $testData);

		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('write')->once()->with($path . '/test', $preparedTestData)->getMock()
			->shouldReceive('changeMode')->getMock();
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$fileStorage->set('test', $testData);
	}

	/**
	 * Tests the prepareData() method.
	 *
	 * @return void
	 */
	public function testPrepareData() {
		$path = 'test';

		// Test with plainText mode on and TTL given
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		try {
			$fileStorage->prepareData('test', 'test', 10);
			$this->fail('Storage should throw an exception in case of TTL being used with plaintext mode!');
		}
		catch (ParameterException $e) {
		}

		// Test with plainText mode on
		$preparedData = $fileStorage->prepareData('test', 1);
		$this->assertEquals('1', $preparedData);

		// Test with plainText mode off
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$data = array(
			'test' => 'test',
			'test1' => array(
				'test2' => 'test'
			)
		);
		$ttl = 10;
		$preparedData = $fileStorage->prepareData('test', $data, $ttl);

		$storedData = unserialize($preparedData);
		$this->assertNotEmpty($storedData['createdAt']);
		$this->assertEquals($storedData['createdAt'] + $ttl, $storedData['expiresAt']);
		$this->assertEquals($data, $storedData['data']);
		$this->assertEquals('test', $storedData['key']);
	}

	/**
	 * Tests the readData() method.
	 *
	 * @return void
	 */
	public function testReadData() {
		$path = 'test';

		// Test with plainText mode on
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$data = 'test';
		$this->assertEquals($data, $fileStorage->readData($data));


		// Test with plainText mode off and no expiresAt stored
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$data = serialize(array('data' => 'test'));
		try {
			$fileStorage->readData($data);
			$this->fail('Storage should throw an Exception in case of expiresAt not found in the stored data');
		}
		catch (StorageException $e) {
		}


		// Test with plainText mode off and no data stored
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$data = serialize(array('expiresAt' => time()));
		try {
			$fileStorage->readData($data);
			$this->fail('Storage should throw an Exception in case of data not found in the stored data');
		}
		catch (StorageException $e) {
		}


		// Test with plainText mode off and expired expiresAt given
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$data = serialize(array('expiresAt' => time() - 10, 'data' => 'test'));
		$result = $fileStorage->readData($data);
		$this->assertFalse($result);


		// Test with plainText mode off and not expired expiresAt given
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$storedData = array(
			'test' => 'test',
			'test1' => array(
				'test2' => 'test'
			)
		);
		$data = serialize(array('expiresAt' => time() + 10, 'data' => $storedData));
		$result = $fileStorage->readData($data);
		$this->assertEquals($storedData, $result);


		// Test with plainText mode off and empty expiresAt given
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$storedData = array(
			'test' => 'test',
			'test1' => array(
				'test2' => 'test'
			)
		);
		$data = serialize(array('expiresAt' => 0, 'data' => $storedData));
		$result = $fileStorage->readData($data);
		$this->assertEquals($storedData, $result);
	}

	/**
	 * Tests the get() method.
	 *
	 * @return void
	 */
	public function testGet() {
		$path = 'test';
		$key = 'testKey';
		$fullPath = $path . '/' . $key;

		// Test with nonexistent file
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('checkIsPathExists')->with($fullPath)->andReturn(false)->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		$this->assertFalse($fileStorage->get($key));


		// Test with not readable file file
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('checkIsPathExists')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('checkIsReadable')->with($fullPath)->andReturn(false)->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->get($key);
			$this->fail('Storage should throw an Exception in case of not readable file encountered!');
		}
		catch (StorageException $e) {
		}


		// Test with failed read file
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('checkIsPathExists')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('checkIsReadable')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('getAsString')->with($fullPath)->andReturn(false)->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->get($key);
			$this->fail('Storage should throw an Exception in case of file read error!');
		}
		catch (StorageException $e) {
		}


		// Test with an outdated file
		$storedData = array(
			'expiresAt' => time() - 10,
			'data'      => array('test')
		);
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('checkIsPathExists')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('checkIsReadable')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('getAsString')->with($fullPath)->andReturn(serialize($storedData))->getMock()
			->shouldReceive('remove')->with($fullPath)->andThrow(new Exception('Remove failed'))->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->get($key);
			$this->fail('Storage should throw an Exception if it fails to remove an empty file');
		}
		catch (StorageException $e) {
		}


		// Test with everything OK
		$storedData = array(
			'expiresAt' => time() + 10,
			'data'      => array('test')
		);
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('checkIsPathExists')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('checkIsReadable')->with($fullPath)->andReturn(true)->getMock()
			->shouldReceive('getAsString')->with($fullPath)->andReturn(serialize($storedData))->getMock();;

		$fileStorage = new FileStorageMock('test', $fileHandler);
		$result = $fileStorage->get($key);
		$this->assertEquals($storedData['data'], $result);
	}

	/**
	 * Tests the delete() method.
	 *
	 * @return void
	 */
	public function testDelete() {
		$path = 'test';
		$key = 'testKey';
		$fullPath = $path . '/' . $key;

		// Test with a read only Storage
		$this->setConfig('test', $path, null, null, true);
		$fileHandler = $this->getFileHandlerMock($path);

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->delete($key);
			$this->fail('Storage should throw an Exception in case of deletion in read only mode!');
		}
		catch (StorageException $e) {
		}


		// Test when the file already deleted
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('remove')->with($fullPath)->andThrow(new NotFoundException('File is already deleted'))
				->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		$fileStorage->delete($key);


		// Test when the file remove fails
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('remove')->with($fullPath)->andThrow(new Exception('File deletion failed'))
				->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->delete($key);
			$this->fail('File deletion failed!');
		}
		catch (StorageException $e) {
		}


		// Test when everything fine
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('remove')->with($fullPath)->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		$fileStorage->delete($key);
	}

	/**
	 * Tests the clear() method.
	 *
	 * @return void
	 */
	public function testClear() {
		$path = 'test';

		// Test with a read only Storage
		$this->setConfig('test', $path, null, null, true);
		$fileHandler = $this->getFileHandlerMock($path);

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->clear();
			$this->fail('Storage should throw an Exception in case of clearing in read only mode!');
		}
		catch (StorageException $e) {
		}


		// Test when the directory remove fails
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('removeDirectory')->with($path . '/', true)
				->andThrow(new Exception('Directory deletion failed'))->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		try {
			$fileStorage->clear();
			$this->fail('Directory deletion failed!');
		}
		catch (StorageException $e) {
		}


		// Test when everything fine
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path)
			->shouldReceive('removeDirectory')->with($path . '/', true)->getMock();

		$fileStorage = new FileStorageMock('test', $fileHandler);
		$fileStorage->clear();
	}

	/**
	 * Tests the isTtlSupported() method.
	 *
	 * @return void
	 */
	public function testIsTtlSupported() {
		$path = 'test';

		// Test with plainTextMode on
		$this->setConfig('test', $path, true);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$this->assertFalse($fileStorage->isTtlSupported());


		// Test with plainTextMode off
		$this->setConfig('test', $path);
		$fileHandler = $this->getFileHandlerMock($path);
		$fileStorage = new FileStorageMock('test', $fileHandler);

		$this->assertTrue($fileStorage->isTtlSupported());
	}
}
