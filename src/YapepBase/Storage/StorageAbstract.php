<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Storage
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Storage;


use YapepBase\Debugger\DebuggerRegistry;
use YapepBase\Debugger\Item\StorageItem;
use YapepBase\Exception\ConfigException;
use YapepBase\Config;

/**
 * Base class for the storage implementations.
 *
 * Configuration settings for the storage should be set in the format:
 * <b>resource.storage.&lt;configName&gt;.&lt;optionName&gt;
 *
 * @package    YapepBase
 * @subpackage Storage
 */
abstract class StorageAbstract implements IStorage {

	/**
	 * Holds the name of the currently used configuration.
	 *
	 * @var string
	 */
	protected $currentConfigurationName;

	/**
	 * If TRUE, no debug items are created by this storage.
	 *
	 * @var bool
	 */
	protected $debuggerDisabled;

	/**
	 * The debugger registry instance.
	 *
	 * @var DebuggerRegistry
	 */
	protected $debuggerRegistry;

	/**
	 * Constructor.
	 *
	 * @param DebuggerRegistry $deubberRegistry The debugger registry.
	 * @param string           $configName      The name of the configuration to use.
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	public function __construct(DebuggerRegistry $debuggerRegistry, $configName) {
		$this->debuggerRegistry = $debuggerRegistry;
		$this->currentConfigurationName = $configName;

		$properties = $this->getConfigProperties();
		$configData = array();
		foreach ($properties as $property) {
			try {
				$configData[$property] =
					Config::getInstance()->get('resource.storage.' . $configName . '.' . $property);

			}
			catch (ConfigException $e) {
				// We just swallow this because we don't know what properties do we need in advance
			}
		}

		$this->setupConfig($configData);
	}


	/**
	 * Sets up the backend.
	 *
	 * @param array $config   The configuration data for the backend.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	protected function setupConfig(array $config) {
		$this->debuggerDisabled = isset($config['debuggerDisabled']) ? (bool)$config['debuggerDisabled'] : false;
	}

	protected function addDebugItem($backendType, $query, $params, $executionTime)
	{
		if (!$this->debuggerDisabled && $this->debuggerRegistry->hasRenderers()) {
			$this->debuggerRegistry->addItem(new StorageItem(
					$backendType,
					$backendType . '.' . $this->currentConfigurationName,
					$query,
					$params,
					$executionTime
			));
		}

	}

	/**
	 * Returns the config properties(last part of the key) used by the class.
	 *
	 * @return array
	 */
	abstract protected function getConfigProperties();
}
