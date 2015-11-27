<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage File
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\File;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory for returning a file handler instance.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerFactory {

	/**
	 * The file handler instance.
	 *
	 * @var IFileHandler
	 */
	protected $fileHandler;

	/**
	 *
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $diContainer The container.
	 */
	public function __construct(ContainerInterface $diContainer)
	{
		$this->container = $diContainer;
	}

	/**
	 * Returns the file handler instance.
	 *
	 * @return IFileHandler
	 */
	public function getFileHandler()
	{
		if (!$this->fileHandler) {
			$this->fileHandler = $this->container->get('yapepBase.fileHandlerPhp');
		}
		return $this->fileHandler;
	}

	/**
	 * Sets the file handler to be used.
	 *
	 * @param IFileHandler $fileHandler The file handler instance.
	 *
	 * @return void
	 */
	public function setFileHandler(IFileHandler $fileHandler)
	{
		$this->fileHandler = $fileHandler;
	}

}
