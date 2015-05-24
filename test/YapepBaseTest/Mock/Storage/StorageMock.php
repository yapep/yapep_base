<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Storage
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Storage;


use YapepBase\Exception\ParameterException;
use YapepBase\Storage\IStorage;
use YapepBase\Storage\StorageFactory;

/**
 * StorageMock class
 *
 * @package    YapepBaseTest
 * @subpackage Test\Mock\Storage
 * @codeCoverageIgnore
 */
class StorageMock implements IStorage {

	protected $ttlSupport;
	protected $persistent;
	protected $readOnly;
	public $data;

	public function __construct($ttlSupport, $persistent, array $data = array(), $readOnly = false) {
		$this->ttlSupport = $ttlSupport;
		$this->persistent = $persistent;
		$this->readOnly = $readOnly;
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

	public function set($key, $data, $ttl = 0) {
		if ($ttl != 0 && !$this->ttlSupport) {
			throw new ParameterException('TTL option is set, when we have no TTL support.');
		}

		$this->data[$key] = $data;
	}

	public function delete($key) {
		if (isset($this->data[$key])) {
			unset($this->data[$key]);
		}
	}

	/**
	 * Deletes every data in the storage.
	 *
	 * @return mixed
	 */
	public function clear() {
		$this->data = array();
	}

	public function get($key) {
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}
		return false;
	}

	public function isPersistent() {
		return $this->persistent;
	}

	public function isTtlSupported() {
		return $this->ttlSupport;
	}

	/**
	 * Returns TRUE if the storage backend is read only, FALSE otherwise.
	 *
	 * @return bool
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}

	/**
	 * Returns the configuration data for the storage backend. This includes the storage type as used by
	 * the storage factory.
	 *
	 * @return array
	 */
	public function getConfigData() {
		return array(
			// Since this is a mock storage, we'll use the dummy storage type.
			'storageType'      => StorageFactory::TYPE_DUMMY,
			'ttlSupport'       => $this->ttlSupport,
			'persistent'       => $this->persistent,
			'readOnly'         => $this->readOnly,
			'debuggerDisabled' => true,
		);
	}
}