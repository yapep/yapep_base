<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\ErrorHandler;


use YapepBase\Application;
use YapepBase\Storage\IStorage;

/**
 * Error handler that dumps debugging information to a specified storage.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
class DebugDataCreator implements IErrorHandler  {

	/**
	 * The storage instance
	 *
	 * @var \YapepBase\Storage\IStorage
	 */
	protected $storage;

	/**
	 * If TRUE, exception traces are not going to be dumped.
	 *
	 * @var bool
	 */
	protected $isTestMode = false;

	/**
	 * Constructor.
	 *
	 * @param \YapepBase\Storage\IStorage $storage      The storage backend to use for the debug data.
	 * @param bool                        $isTestMode   If TRUE, exception traces are not going to be dumped.
	 */
	public function __construct(IStorage $storage, $isTestMode = false) {
		$this->storage = $storage;
		$this->isTestMode = $isTestMode;
	}

	/**
	 * Handles a PHP error
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occured.
	 * @param int    $line         The line in the file where the error occured.
	 * @param array  $context      The context of the error. (All variables that exist in the scope the error occured)
	 * @param string $errorId      The internal ID of the error.
	 * @param array  $backTrace    The debug backtrace of the error.
	 *
	 * @return void
	 */
	public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array()) {
		if (false !== $this->storage->get($errorId)) {
			// Only save the debug info, if it's not already saved
			return;
		}

		$helper = new ErrorHandlerHelper();
		$errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

		$errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
			. ' in ' . $file;

		$this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage, $backTrace, (array)$context));
	}

	/**
	 * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
	 *
	 * @param \Exception $exception   The exception to handle.
	 * @param string     $errorId     The internal ID of the error.
	 *
	 * @return void
	 */
	public function handleException(\Exception $exception, $errorId) {
		if (false !== $this->storage->get($errorId)) {
			// Only save the debug info, if it's not already saved
			return;
		}

		$errorMessage = '[' . ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION . ']: Unhandled ' . get_class($exception)
			. ': ' . $exception->getMessage() . '(' . $exception->getCode() . ') on line ' . $exception->getLine()
			. ' in ' . $exception->getFile();

		$this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage,
			($this->isTestMode ? array() : $exception->getTrace()),
			($this->isTestMode ? array() : array('exception' => $exception))));
	}

	/**
	 * Called at script shutdown if the shutdown is because of a fatal error.
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occured.
	 * @param int    $line         The line in the file where the error occured.
	 * @param string $errorId      The internal ID of the error.
	 *
	 * @return void
	 */
	public function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
		if (false !== $this->storage->get($errorId)) {
			// Only save the debug info, if it's not already saved
			return;
		}

		$helper = new ErrorHandlerHelper();
		$errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

		$errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
			. ' in ' . $file;

		$this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage));
	}

	/**
	 * Returns the debug data as string for the given error.
	 *
	 * @param string $errorId        The internal ID of the error.
	 * @param string $errorMessage   The error message.
	 * @param array  $backTrace      The debug backtrace of the error.
	 * @param array  $context        The context of the error. (All variables that exist in the scope the error occured)
	 *
	 * @return string
	 */
	protected function getDebugData($errorId, $errorMessage, array $backTrace = array(), array $context = array()) {
		$debugData = $errorId . ' ' . $errorMessage . "\n\n";
		$sessionData = Application::getInstance()->getDiContainer()->getSessionRegistry()->getAllData();

		$debugData .= $this->getRenderedDebugDataBlock('Debug backtrace', $backTrace);
		$debugData .= $this->getRenderedDebugDataBlock('Context', $context);
		$debugData .= $this->getRenderedDebugDataBlock('Server', $_SERVER);
		$debugData .= $this->getRenderedDebugDataBlock('Get', $_GET);
		$debugData .= $this->getRenderedDebugDataBlock('Post', $_POST);
		$debugData .= $this->getRenderedDebugDataBlock('Cookie', $_COOKIE);
		$debugData .= $this->getRenderedDebugDataBlock('Session', $sessionData);
		$debugData .= $this->getRenderedDebugDataBlock('Env', $_ENV);

		return $debugData;
	}

	/**
	 * Renders a debug data block id the given data is not empty and returns it.
	 *
	 * @param string $blockName   Name of the block.
	 * @param mixed  $data        Date to render.
	 *
	 * @return string
	 */
	protected function getRenderedDebugDataBlock($blockName, $data) {
		if (!empty($data)) {
			return "----- " . $blockName . " -----\n\n" . print_r($data, true) . "\n\n";
		}

		return '';
	}
}