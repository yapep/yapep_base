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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use YapepBase\Controller\IController;
use YapepBase\Exception\DiException;

/**
 * Compiler pass to find all of the controllers.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class ControllerCompilerPass implements CompilerPassInterface {

	/**
	 * You can modify the container here before it is dumped to PHP code.
	 *
	 * @param ContainerBuilder $container
	 *
	 * @api
	 */
	public function process(ContainerBuilder $container) {
		$registeredNames = [];

		foreach ($container->findTaggedServiceIds('controller') as $id => $tags) {
			$reflectionClass = new \ReflectionClass($container->getDefinition($id)->getClass());
			if (!$reflectionClass->implementsInterface(IController::class)) {
				throw new DiException(sprintf(
					'The class "%s" does not implement the "%s" interface, so can not be tagged as a controller',
					$reflectionClass->getName(),
					IController::class
				));
			}

			$name = preg_replace('/Controller$/', '', $reflectionClass->getShortName());

			foreach ($tags as $attributes) {
				if (isset($attributes['alias'])) {
					$name = $attributes['alias'];
					break;
				}
			}

			if (isset($registeredNames[$name])) {
				throw new DiException(sprintf(
					'Can not register controller "%s" under name "%s" that name is already used by "%s"',
					$id,
					$name,
					$registeredNames[$name]
				));
			}

			$registeredNames[$name] = $id;

			$container->setAlias('yapepBase.controller.' . $name, $id);
		}
	}
}
