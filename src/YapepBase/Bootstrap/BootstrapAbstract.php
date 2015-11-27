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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use YapepBase\Application;
use YapepBase\Autoloader\AutoloaderRegistry;
use YapepBase\Autoloader\SimpleAutoloader;
use YapepBase\DependencyInjection\ArgumentAutoResolverCompilerPass;
use YapepBase\DependencyInjection\ControllerCompilerPass;
use YapepBase\Exception\Exception;

/**
 * Base class for bootstrapping.
 *
 * Contains helper methods that do common bootstrapping tasks.
 *
 * @package    YapepBase
 * @subpackage Bootstrap
 */
abstract class BootstrapAbstract {

	/**
	 * The autoloader instance.
	 *
	 * @var \YapepBase\Autoloader\IAutoloader
	 */
	protected $autoloader;

	/**
	 * The full path to the root directory for the project.
	 *
	 * @var string
	 */
	protected $projectBaseDir;

	/**
	 * The full path to the vendor directory.
	 *
	 * @var string
	 */
	protected $vendorDir;

	/**
	 * The full path to the application's base directory.
	 *
	 * @var string
	 */
	protected $applicationBaseDir;

	/**
	 * The DI container instance.
	 *
	 * @var ContainerInterface
	 */
	protected $diContainer;

	/**
	 * The full path to the framework's base dir.
	 *
	 * @var string
	 */
	protected $yapepBaseDir;

	/**
	 * The subdirectory containing the configs relative to it's base directory.
	 *
	 * @var string
	 */
	protected $configSubDir = 'config';

	/**
	 * The subdirectory containing the classes relative to it's base directory.
	 *
	 * @var string
	 */
	protected $classSubDir = 'class';

	/**
	 * Constructor.
	 *
	 * @param string $projectBaseDir Full path to the project's base directory.
	 * @param string $vendorDir      Full path to the vendor directory.
	 */
	public function __construct($projectBaseDir, $vendorDir) {
		$this->projectBaseDir = $this->normalizePath($projectBaseDir);
		$this->vendorDir      = $this->normalizePath($vendorDir);
		$this->yapepBaseDir   = realpath(dirname(dirname(dirname(__DIR__))));
	}

	/**
	 * The subdirectory containing the classes relative to it's base directory.
	 *
	 * @param string $classSubDir The subdirectory.
	 *
	 * @return $this
	 */
	public function setClassSubDir($classSubDir) {
		$this->classSubDir = $this->normalizePath($classSubDir, false);
		return $this;
	}

	/**
	 * Sets the subdirectory containing the configs relative to it's base directory.
	 *
	 * @param string $configSubDir The subdirectory
	 *
	 * @return $this
	 */
	public function setConfigSubDir($configSubDir) {
		$this->configSubDir = $this->normalizePath($configSubDir, false);
		return $this;
	}

	/**
	 * Sets the full path to the application base directory.
	 *
	 * @param string $applicationBaseDir The application base directory
	 *
	 * @return $this
	 */
	public function setApplicationDir($applicationBaseDir) {
		$this->applicationBaseDir = $this->normalizePath($applicationBaseDir);
		return $this;
	}

	/**
	 * Does the bootstrap for basic usage.
	 *
	 * @return void
	 */
	public function start() {
		$this->initialise();
		$this->defineEnvironmentConstants();
		$this->verifyEnvironment();

		$this->setupAutoloader();
		$this->setupDependencyInjection();
		$this->loadConfig();
		$this->setupErrorHandling();
		$this->setupLogging();
		$this->initialiseApplication();
	}

	/**
	 * Initialises the encodings and error reporting.
	 *
	 * @return void
	 */
	protected function initialise() {
		error_reporting(-1);
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');
	}

	/**
	 * Defines the environment constants.
	 *
	 * @return void
	 */
	protected function defineEnvironmentConstants() {
		foreach ($this->getValidEnvironments() as $name => $value) {
			define($name, $value);
		}
	}

	/**
	 * Returns the valid environment constants and their values.
	 *
	 * The format is constant name => value.
	 *
	 * @return array
	 */
	protected function getValidEnvironments() {
		return [
			'ENVIRONMENT_DEV'        => 'dev',
			'ENVIRONMENT_TEST'       => 'test',
			'ENVIRONMENT_STAGING'    => 'staging',
			'ENVIRONMENT_PRODUCTION' => 'production',
		];
	}

	/**
	 * Validates the environment constants.
	 *
	 * @return void
	 *
	 * @throws \Exception   If the environment is not set up correctly.
	 */
	protected function verifyEnvironment() {
		// Do nothing in the initial implementation
	}

	/**
	 * Sets up the autoloader.
	 *
	 * @return void
	 */
	protected function setupAutoloader() {
		require_once $this->vendorDir . DIRECTORY_SEPARATOR . 'autoload.php';

		// Autoloader setup
		$this->autoloader = new SimpleAutoloader();

		if (!empty($this->applicationBaseDir)) {
			$this->autoloader->addClassPath($this->applicationBaseDir . DIRECTORY_SEPARATOR . $this->classSubDir);
		}

		$this->autoloader->addClassPath($this->projectBaseDir . DIRECTORY_SEPARATOR . $this->classSubDir);

		// FIXME check this, it may make sense to get this from the DI
		AutoloaderRegistry::getInstance()->addAutoloader($this->autoloader);
	}

	/**
	 * Sets up the dependency injection container.
	 *
	 * @return void
	 */
	protected function setupDependencyInjection() {
		if ($this->isContainerCompilationNeeded()) {
			$container = new ContainerBuilder();

			$container->addCompilerPass(new ArgumentAutoResolverCompilerPass());
			$container->addCompilerPass(new ControllerCompilerPass(), PassConfig::TYPE_OPTIMIZE);

			$this->addConfigsToDependencyInjection($container, $this->yapepBaseDir . DIRECTORY_SEPARATOR . 'config');

			$this->addConfigsToDependencyInjection($container,
					$this->projectBaseDir . DIRECTORY_SEPARATOR . $this->configSubDir);

			if (!empty($this->applicationBaseDir)) {
				$this->addConfigsToDependencyInjection($container,
						$this->applicationBaseDir . DIRECTORY_SEPARATOR . $this->configSubDir);
			}

			$this->setupEnvironmentInDi($container);

			$container->compile();

			$this->saveCompiledContainer($container);

		} else {
			$container = $this->getCachedContainer();
		}
		// FIXME add proper caching to the container

		$this->diContainer = $container;
	}

	/**
	 * Returns TRUE if the container needs to be compiled.
	 *
	 * @return bool
	 */
	protected function isContainerCompilationNeeded() {
		// The default implementation means that the container always needs to be recompiled.
		return true;
	}

	/**
	 * Saves the compiled container.
	 *
	 * @param ContainerBuilder $container The container to save.
	 *
	 * @return void
	 */
	protected function saveCompiledContainer(ContainerBuilder $container) {
		// The default implementation does not save the container
	}

	/**
	 * Returns the cached container instance.
	 *
	 * @return ContainerInterface
	 *
	 * @throws Exception If the container can not be loaded
	 */
	protected function getCachedContainer() {
		throw new Exception('The default bootstrap does not support loading cached containers');
	}

	/**
	 * Adds a configuration for the DI container.
	 *
	 * @param ContainerBuilder $container The container builder instance.
	 * @param string           $configDir The configuration directory.
	 *
	 * @return void
	 */
	protected function addConfigsToDependencyInjection(ContainerBuilder $container, $configDir) {
		if (file_exists($this->normalizePath($configDir . DIRECTORY_SEPARATOR . 'constants.xml'))) {
			$xmlLoader = new XmlFileLoader($container, new FileLocator($configDir));
			$xmlLoader->load('constants.xml');
		}

		if (file_exists($this->normalizePath($configDir . DIRECTORY_SEPARATOR . 'dependency_injection.yml'))) {
			$yamlLoader = new YamlFileLoader($container, new FileLocator($configDir));
			$yamlLoader->load('dependency_injection.yml');
		}
	}

	/**
	 * Sets up the evnironment in the DI container.
	 *
	 * @return void
	 */
	protected function setupEnvironmentInDi(ContainerInterface $container) {
		// The default implementation does nothing
	}

	/**
	 * Loads the global config.
	 *
	 * @return void
	 */
	protected function loadConfig() {
		// TODO it may make sense to use Symfony's config here
		require_once $this->projectBaseDir . DIRECTORY_SEPARATOR . 'config.php';
	}

	/**
	 * Sets up the logging and debug data creator error handlers
	 *
	 * @return void
	 */
	protected function setupErrorHandling() {
		// The default implementation doesn't do anything
	}

	/**
	 * Sets up application logging.
	 *
	 * @return void
	 */
	protected function setupLogging() {
		// The default implementation doesn't do anything
	}

	/**
	 * Initialises the application with the DI container.
	 *
	 * @return void
	 */
	protected function initialiseApplication() {
		$this->diContainer->get('yapepBase.application');
	}

	/**
	 * Normalizes a path, by removing any trailing slashes.
	 *
	 * @param string $path    The path to normalize.
	 * @param bool   $resolve If TRUE, the path will be resolved via realpath.
	 *
	 * @return string
	 */
	protected function normalizePath($path, $resolve = true) {
		$path = $resolve ? realpath($path) : $path;

		return rtrim($path, '/\\');
	}
}
