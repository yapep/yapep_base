<?php

namespace YapepBase\Storage;

use YapepBase\Storage\MemcacheStorage;
use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Mock\Storage\MemcacheMock;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\StorageException;

/**
 * Test class for MemcacheStorage.
 * Generated by PHPUnit on 2011-12-23 at 01:33:29.
 */
class MemcacheStorageTest extends \YapepBase\BaseTest {

	/**
	 * @var \YapepBase\Mock\Storage\MemcacheMock
	 */
	protected $memcacheMock;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();
		$container = new SystemContainer();
		$container[SystemContainer::KEY_MEMCACHE] = $container->share(function($container) {
			return new MemcacheMock();
		});
		Application::getInstance()->setDiContainer($container);
		$this->memcacheMock = Application::getInstance()->getDiContainer()->getMemcache();
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
		$storage = new MemcacheStorage('test');
		$this->assertEquals('localhost', $this->memcacheMock->host, 'Host mismatch');
		$this->assertEquals(11211, $this->memcacheMock->port, 'Default port does not match');

		$storage = new MemcacheStorage('test2');
		$this->assertEquals(11222, $this->memcacheMock->port, 'Non-default port does not match');
	}

	public function testFunctionality() {
		$storage = new MemcacheStorage('test');
		$this->assertEmpty($this->memcacheMock->data, 'Data is not empty after connecting');

		$this->assertFalse($storage->get('test'), 'Not set value does not return false');

		$storage->set('test', 'testValue', 100);
		$this->assertSame('testValue', $storage->get('test'), 'Stored value does not match');
		$this->assertEquals(100, $this->memcacheMock->data['test']['ttl'], 'Expiration does not match');

		$storage->delete('test');

		$this->assertFalse($storage->get('test'), 'Deletion failed');
	}

	public function testPrefixedStorage() {
		$storage = new MemcacheStorage('test2');
		$storage->set('test', 'testValue');
		$this->assertTrue(isset($this->memcacheMock->data['test.test.test']), 'Prefixed storage fails');
	}

	public function testHashedStorage() {
		$storage = new MemcacheStorage('test3');
		$storage->set('test', 'testValue');
		$this->assertTrue(isset($this->memcacheMock->data[md5('test.test')]), 'Hashed storage fails');
	}

	public function testStorageSettings() {
		$storage = new MemcacheStorage('test');
		$this->assertTrue($storage->isTtlSupported(), 'TTL should be supported');
		$this->assertFalse($storage->isPersistent(), 'Memcache should not be persistent');
	}

	public function testErrorHandling() {
		try {
			new MemcacheStorage('nonexistent');
			$this->fail('No ConfigException thrown for nonexistent config option');
		} catch (ConfigException $exception) {}

		try {
			new MemcacheStorage('test4');
			$this->fail('No ConfigException thrown for config without a host');
		} catch (ConfigException $exception) {}

		try {
			$this->memcacheMock->connectionSuccessful = false;
			new MemcacheStorage('test');
			$this->fail('No StorageException thrown for failed connection');
		} catch (StorageException $exception) {}
	}

	/**
	 * Tests the read only setting for the storage
	 *
	 * @return void
	 */
	public function testReadOnly() {
		$defaultStorage = new MemcacheStorage('test2');
		$readWriteStorage = new MemcacheStorage('test');
		$readOnlyStorage = new MemcacheStorage('test5');

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
