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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extension for automatically resolving di container constructor argument dependencies.
 *
 * If a dependency configuration doesn't have any arguments specified, but the actual class requires them, this
 * compiler pass will attempt to resolve those. Only classes configured in the DI container are used as candidates for
 * the auto resolution. During the resolution process it will try to find a class implementing the specified interface,
 * or extending the specified class. If the constructor specifies class A as the dependency, and the DI configuration
 * contains both class A and one of it's subclasses, the subclass is used. If there are multiple candidates for a
 * dependency, the last candidate encountered during the compilation process will be used.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class ArgumentAutoResolverCompilerPass implements CompilerPassInterface {

	/**
	 * You can modify the container here before it is dumped to PHP code.
	 *
	 * @param ContainerBuilder $container
	 *
	 * @api
	 */
	public function process(ContainerBuilder $container) {
		$definitions = $container->getDefinitions();

		$missingArguments = [];
		$implementations  = [];

		foreach ($definitions as $definitionName => $definition) {
			$arguments = $definition->getArguments();

			$class           = $definition->getClass();
			$reflectionClass = new \ReflectionClass($class);

			if (!$definition->hasTag('yapepBase.hideFromAutoResolver')) {
				if (!isset($implementations[$class])) {
					$implementations[$class] = $definitionName;
				}

				$implementations = array_merge(
					$implementations,
					$this->getImplementedAndExtended($reflectionClass, $definitionName)
				);
			}

			if ($definition->hasTag('yapepBase.skipAutoResolver')) {
				continue;
			}

			if (($constructor = $reflectionClass->getConstructor()) && 0 == count($arguments)) {
				$missingArguments[] = [
					'reflectionClass' => $reflectionClass,
					'definition'      => $definition,
				];
			}
		}

		foreach ($missingArguments as $incomplete) {
			$this->processMissingArgumentsInDefinition(
				$incomplete['definition'],
				$incomplete['reflectionClass'],
				$implementations
			);
		}
	}

	/**
	 * Processes the missing arguments in the specified definition.
	 *
	 * @param Definition       $definition        The definition.
	 * @param \ReflectionClass $reflectionClass   The reflection class.
	 * @param array            $implementations   The implementations.
	 *
	 * @return void
	 */
	protected function processMissingArgumentsInDefinition(
		Definition $definition, \ReflectionClass $reflectionClass, array $implementations
	) {
		foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
			$hintedType = $parameter->getClass();

			if (!$hintedType) {
				// This definition has non-type hinted parameters, stop any processing of the definition
				return;
			}

			$typeName = $hintedType->getName();

			if (!isset($implementations[$typeName])) {
				// This definition is not known, stop any further processing of the definition
				return;
			}

			$definition->addArgument(new Reference($implementations[$typeName]));
		}
	}

	/**
	 * Returns all implemented interfaces and extended classes for the specified reflection class.
	 *
	 * @param \ReflectionClass $reflectionClass
	 * @param string           $definitionName
	 *
	 * @return array
	 */
	protected function getImplementedAndExtended(\ReflectionClass $reflectionClass, $definitionName) {
		$implementations = [];

		foreach ($reflectionClass->getInterfaceNames() as $interface) {
			$implementations[$interface] = $definitionName;
		}

		$currentClass = $reflectionClass;

		do {
			/** @var \ReflectionClass $parentClass */
			$parentClass = $currentClass->getParentClass();

			if ($parentClass) {
				$currentClass                             = $parentClass;
				$implementations[$parentClass->getName()] = $definitionName;
			}
		} while (!empty($parentClass));

		return $implementations;
	}

}
