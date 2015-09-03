<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage BusinessObject
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\BusinessObject;


use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Database\DbConnectionAbstract;
use YapepBase\Exception\ParameterException;

/**
 * BoAbstract class which should be extended by every Bo classes.
 *
 * Must have global config options affecting this class:
 * <ul>
 *    <li>system.project.name: The unique name of the project. </li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage BusinessObject
 */
abstract class BoAbstract {

	/** Cache key prefix for storing keys. */
	const CACHE_KEY_FOR_KEYS_SUFFIX = 'bo.keys';

	/** TTL for storing keys in secs. (1 day in secs) */
	const CACHE_KEY_FOR_KEYS_TTL = 86400;

	/**
	 * Prefix for the cache keys.
	 *
	 * @var string
	 */
	private $keyPrefix;

	/**
	 * Constructor.
	 *
	 * @param string $keyPrefix   The prefix for the cache keys of the bo instance. If not set, one will be generated
	 *                            based on the configuration and the class-name.
	 */
	public function __construct($keyPrefix = null) {
		if (empty($keyPrefix)) {
			$keyPrefix = Config::getInstance()->get('system.project.name') . '.' . get_class($this);
		}
		$this->keyPrefix = $keyPrefix;
	}

	/**
	 * Returns the prefix of the key which should be used for caching in the BO.
	 *
	 * @return string
	 */
	protected function getKeyPrefix() {
		return $this->keyPrefix . '.';
	}

	/**
	 * Returns the key which should be used for storing the keys used by the BO.
	 *
	 * @return string
	 */
	protected function getKeyForKeys() {
		return $this->getKeyPrefix() . self::CACHE_KEY_FOR_KEYS_SUFFIX;
	}

	/**
	 * Returns the storage handler which should be used for caching.
	 *
	 * @return bool|\YapepBase\Storage\IStorage
	 */
	protected function getStorage() {
		return Application::getInstance()->getDiContainer()->getMiddlewareStorage();
	}

	/**
	 * Adds the given key to the stored list.
	 *
	 * @param string $key   The key.
	 * @param int    $ttl   The expiration time in seconds.
	 *
	 * @return void
	 */
	private function addKey($key, $ttl) {
		$expire = $ttl > 0 ? time() + $ttl : 0;
		$keys = $this->getStorage()->get($this->getKeyForKeys());
		$keys[$key] = $expire;
		$this->getStorage()->set($this->getKeyForKeys(), $keys, self::CACHE_KEY_FOR_KEYS_TTL);
	}

	/**
	 * Returns the stored data for the given key.
	 *
	 * @param string $key   The suffix of the key
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\ParameterException   On empty key.
	 * @throws \YapepBase\Exception\StorageException     On storage error.
	 */
	protected function getFromStorage($key) {
		if (empty($key)) {
			throw new ParameterException();
		}

		$key = $this->getKeyPrefix() . $key;

		return $this->getStorage()->get($key);
	}

	/**
	 * Stores data under the specified key.
	 *
	 * @param string $key                 The key to be used to store the data.
	 * @param mixed  $data                The data to store. By default the empty values won't be stored
	 *                                    (NULL, 0, '', '0', false).
	 * @param int    $ttl                 The expiration time of the data in seconds if supported by the backend.
	 * @param bool   $forceEmptyStorage   If  TRUE, the method will store empty values as well (except for FALSE).
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   On empty key.
	 * @throws \YapepBase\Exception\StorageException     On error.
	 * @throws \YapepBase\Exception\ParameterException   If TTL is set and not supported by the backend.
	 */
	protected function setToStorage($key, $data, $ttl = 0, $forceEmptyStorage = false) {
		if (empty($key)) {
			throw new ParameterException();
		}

		if (
			// If we're not forced to store empty values, and the given values is empty
			(!$forceEmptyStorage && empty($data))
			// If we're forced to store empty values, we still wont store FALSE
			|| ($forceEmptyStorage && $data === false)
		) {
			return;
		}

		$storageKey = $this->getKeyPrefix() . $key;

		$this->getStorage()->set($storageKey, $data, $ttl);

		$this->addKey($key, $ttl);
	}

	/**
	 * Deletes the given keys from the storage.
	 *
	 * @param string $key   The name of the key to look for. The key can be the exact key,
	 *                      or can end in a '.*' for wildcard lookup. If its empty all the keys will be purged.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\StorageException   On error.
	 */
	protected function deleteFromStorage($key = '') {
		// Get the stored keys
		$keysStored = $this->getStorage()->get($this->getKeyForKeys());

		$keysToPurge = array();

		// If the given key is empty we have to purge everything
		if (empty($key)) {
			// In case we have not stored any data yet, we do not need to delete anything
			if (empty($keysStored)) {
				return;
			}
			$keyPrefix = $this->getKeyPrefix();
			foreach ($keysStored as $storedKey => $expire) {
				$keysToPurge[] = $keyPrefix . $storedKey;
			}
			$keysStored = array();
		}
		// If it ends with an asterix, purge all the keys beginning with the name
		elseif ('.*' == substr($key, -2, 2)) {
			// In case we have not stored any data yet, we do not need to delete anything
			if (empty($keysStored)) {
				return;
			}

			$keyPrefix = substr($key, 0, -1);

			foreach ($keysStored as $storedKey => $expire) {
				// We've found a key with the given prefix
				if (0 === strpos($storedKey, $keyPrefix)) {
					$keysToPurge[] = $this->getKeyPrefix() . $storedKey;
					unset($keysStored[$storedKey]);
				}
			}
		}
		// The given key is an exact key
		else {
			$keysToPurge[] = $this->getKeyPrefix() . $key;
			if (isset($keysStored[$key])) {
				unset($keysStored[$key]);
			}
		}

		// Removing the data from the found keys
		foreach ($keysToPurge as $keyToPurge) {
			$this->getStorage()->delete($keyToPurge);
		}

		// Writing back the remaining keys
		$this->getStorage()->set($this->getKeyForKeys(), $keysStored, self::CACHE_KEY_FOR_KEYS_TTL);
	}


	/**
	 * Returns a common helper by it's name
	 *
	 * @param string $name   The name of the Helper class to return.
	 *                       (Without the namespace and Helper suffix)
	 *
	 * @return \YapepBase\Helper\HelperAbstract
	 */
	protected function getHelper($helperName) {
		return Application::getInstance()->getDiContainer()->getHelper($helperName);
	}
}