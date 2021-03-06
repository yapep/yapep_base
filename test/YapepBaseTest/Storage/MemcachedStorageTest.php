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


use YapepBase\Storage\MemcachedStorage;
use YapepBase\Application;
use YapepBase\Config;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\StorageException;

use YapepBaseTest\Mock\Storage\MemcachedMock;

/**
 * Test class for MemcachedStorage.
 * Generated by PHPUnit on 2011-12-23 at 01:33:29.
 */
class MemcachedStorageTest extends \YapepBaseTest\TestAbstract {

	/**
	 * @var MemcachedMock
	 */
	protected $memcacheMock;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();
		if (!class_exists('Memcached')) {
			$this->markTestSkipped();
		}
		$container = new SystemContainer();
		$container[SystemContainer::KEY_MEMCACHED] = $container->share(function($container) {
			return new MemcachedMock();
		});
		Application::getInstance()->setDiContainer($container);
		$this->memcacheMock = Application::getInstance()->getDiContainer()->getMemcached();
		Config::getInstance()->set(array(
			'resource.storage.test.host' => 'localhost',
			'resource.storage.test.readOnly' => false,

			'resource.storage.test2.host' => 'localhost',
			'resource.storage.test2.port' => 11222,
			'resource.storage.test2.keyPrefix' => 'test.',
			'resource.storage.test2.keySuffix' => '.test',

			'resource.storage.test3.host' => 'localhost',
			'resource.storage.test3.hashKey' => true,
			'resource.storage.test3.keyPrefix' => 'test.',

			'resource.storage.test4.port' => 11211,

			'resource.storage.test5.host' => 'localhost',
			'resource.storage.test5.readOnly' => true,
		));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		Config::getInstance()->clear();
		Application::getInstance()->setDiContainer(new SystemContainer());
	}

	public function testConnection() {
		$storage = new MemcachedStorage('test');

		$this->assertEquals(1, count($this->memcacheMock->serverList), 'The server count is not 1');
		$this->assertEquals('localhost', $this->memcacheMock->serverList[0]['host'], 'Host mismatch');
		$this->assertEquals(11211, $this->memcacheMock->serverList[0]['port'], 'Default port does not match');
		$this->memcacheMock->serverList = array();

		$storage = new MemcachedStorage('test2');
		$this->assertEquals(1, count($this->memcacheMock->serverList), 'The server count is not 1');
		$this->assertEquals(11222, $this->memcacheMock->serverList[0]['port'], 'Non-default port does not match');
	}

	public function testFunctionality() {
		$storage = new MemcachedStorage('test');
		$this->assertEmpty($this->memcacheMock->data, 'Data is not empty after connecting');

		$this->assertFalse($storage->get('test'), 'Not set value does not return false');

		$storage->set('test', 'testValue', 100);
		$this->assertSame('testValue', $storage->get('test'), 'Stored value does not match');
		$this->assertEquals(100, $this->memcacheMock->data['test']['ttl'], 'Expiration does not match');

		$storage->delete('test');

		$this->assertFalse($storage->get('test'), 'Deletion failed');
	}

	public function testPrefixedStorage() {
		$storage = new MemcachedStorage('test2');
		$storage->set('test', 'testValue');
		$this->assertTrue(isset($this->memcacheMock->data['test.test.test']), 'Prefixed storage fails');
	}

	public function testHashedStorage() {
		$storage = new MemcachedStorage('test3');
		$storage->set('test', 'testValue');
		$this->assertTrue(isset($this->memcacheMock->data[md5('test.test')]), 'Hashed storage fails');
	}

	public function testStorageSettings() {
		$storage = new MemcachedStorage('test');
		$this->assertTrue($storage->isTtlSupported(), 'TTL should be supported');
		$this->assertFalse($storage->isPersistent(), 'Memcache should not be persistent');
	}

	public function testErrorHandling() {
		try {
			new MemcachedStorage('nonexistent');
			$this->fail('No ConfigException thrown for nonexistent config option');
		} catch (ConfigException $exception) {}

		try {
			new MemcachedStorage('test4');
			$this->fail('No ConfigException thrown for config without a host');
		} catch (ConfigException $exception) {}
	}

	/**
	 * Tests the read only setting for the storage
	 *
	 * @return void
	 */
	public function testReadOnly() {
		$defaultStorage = new MemcachedStorage('test2');
		$readWriteStorage = new MemcachedStorage('test');
		$readOnlyStorage = new MemcachedStorage('test5');

		$this->assertFalse($defaultStorage->isReadOnly(),
			'A storage should report it is not read only if no read only setting is defined');
		$this->assertFalse($readWriteStorage->isReadOnly(), 'The read-write storage should report it is not read only');
		$this->assertTrue($readOnlyStorage->isReadOnly(), 'The read only storage should report it is read only');
		$this->memcacheMock->data['test'] = array('value' => 'test', 'ttl' => time() + 600);

		$data = $readOnlyStorage->get('test');
		$this->assertNotEmpty($data, 'The retrieved data should not be empty');
		try {
			$readOnlyStorage->set('test', 'test2');
			$this->fail('No StorageException thrown for trying to write to a read only storage');
		} catch (StorageException $exception) {
			$this->assertContains('read only storage', $exception->getMessage());
		}
		$this->assertSame($data, $readOnlyStorage->get('test'), 'The data should not be changed in the storage');
	}
}
