<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Bootstrap
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Bootstrap;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use YapepBase\DependencyInjection\ContainerHelper;
use YapepBase\Exception\Exception;

/**
 * Basic full implementation for bootsrapping.
 *
 * Sets up application logging, error handling, basic environments, etc.
 *
 * @package    YapepBase
 * @subpackage Bootstrap
 */
class BasicBootstrap extends BootstrapAbstract {

	/**
	 * The environment to use.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * The subdirectory containing the caches relative to it's base directory.
	 *
	 * @var string
	 */
	protected $cacheSubDir = 'cache';


	/**
	 * Constructor.
	 *
	 * @param string $environment    The environment to use. {@uses ENVIRONMENT_*}
	 * @param string $projectBaseDir Full path to the project's base directory.
	 * @param string $vendorDir      Full path to the vendor directory.
	 */
	public function __construct($environment, $projectBaseDir, $vendorDir) {
		parent::__construct($projectBaseDir, $vendorDir);

		$this->environment = $environment;
	}

	/**
	 * Sets the subdirectory containing the caches relative to it's base directory.
	 *
	 * @param string $cacheSubDir The subdirectory
	 *
	 * @return $this
	 */
	public function setCacheSubDir($cacheSubDir) {
		$this->cacheSubDir = $this->normalizePath($cacheSubDir, false);
		return $this;
	}

	/**
	 * Validates the environment constants.
	 *
	 * @return void
	 *
	 * @throws \Exception   If the environment is not set up correctly.
	 */
	protected function verifyEnvironment() {
		parent::verifyEnvironment();
		// Check if the current environment is set as the ENVIRONMENT constant, die if it isn't.

		if (!in_array($this->environment, $this->getValidEnvironments())) {
			throw new \Exception(sprintf('Invalid environment: "%s"', $this->environment));
		}
	}

	/**
	 * Sets up the evnironment in the DI container.
	 *
	 * @return void
	 */
	protected function setupEnvironmentInDi(ContainerInterface $container) {
		parent::setupEnvironmentInDi($container);
		$helper = new ContainerHelper($container);

		$helper->setEnvironment($this->environment);
	}

	/**
	 * Returns TRUE if the container needs to be compiled.
	 *
	 * @return bool
	 */
	protected function isContainerCompilationNeeded() {
		// This is a very basic implementation of the compile check,
		// and it assumes you do a manual compilation on non dev environments

		if ($this->environment == constant('ENVIRONMENT_DEV')) {
			// Always recompile the container in DEV
			return true;
		}

		// If the cache exists, then don't recompile
		return !file_exists($this->getCachedContainerFilePath());
	}

	/**
	 * Saves the compiled container.
	 *
	 * @param ContainerBuilder $container The container to save.
	 *
	 * @return void
	 */
	protected function saveCompiledContainer(ContainerBuilder $container) {
		$cacheFile = $this->getCachedContainerFilePath();

		if (!file_exists(dirname($cacheFile))) {
			// If the cache directory doesn't exist, create it
			mkdir(dirname($cacheFile), 0777, true);

			var_dump($cacheFile, dirname($cacheFile));
		}

		file_put_contents($cacheFile, (new PhpDumper($container))->dump());
		chmod($cacheFile, 0777);
	}

	/**
	 * Returns the cached container instance.
	 *
	 * @return ContainerInterface
	 *
	 * @throws Exception If the container can not be loaded
	 */
	protected function getCachedContainer() {
		require $this->getCachedContainerFilePath();

		return new \ProjectServiceContainer();
	}

	/**
	 * Returns the full path to the cached container file.
	 *
	 * @return string
	 */
	protected function getCachedContainerFilePath() {
		$basePath = $this->applicationBaseDir ?: $this->projectBaseDir;
		return $basePath . DIRECTORY_SEPARATOR . $this->cacheSubDir . DIRECTORY_SEPARATOR . 'dependency_injection'
			. DIRECTORY_SEPARATOR . 'cachedContainer.php';
	}

}
