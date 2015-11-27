<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper for the dependency injection container.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class ContainerHelper {

	/** The key to the environment parameter */
	const KEY_ENVIRONMENT = 'yapepBase.environment';

	/**
	 * The DI container.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $container The DI container.
	 */
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	 * Sets the environment in the DI contianer
	 *
	 * @param string $environment The environment to set.
	 *
	 * @return void
	 */
	public function setEnvironment($environment) {
		$this->container->setParameter(self::KEY_ENVIRONMENT, $environment);
	}

	/**
	 * Returns the environment set in the DI container or NULL if it's not set.
	 *
	 * @return string
	 */
	public function getEnvironment() {
		return $this->container->hasParameter(self::KEY_ENVIRONMENT)
			? $this->container->getParameter(self::KEY_ENVIRONMENT)
			: null;
	}

}
