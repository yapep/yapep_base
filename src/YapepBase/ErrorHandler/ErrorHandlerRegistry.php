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
use YapepBase\Config;
use YapepBase\Exception\Exception;

/**
 * Registry class holding the registered error handlers.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
class ErrorHandlerRegistry {

	/** The default timeout for the error ID in seconds. */
	const ERROR_HANDLING_DEFAULT_ID_TIMEOUT = 600;   // 10 minutes

	/** Error type. */
	const TYPE_ERROR = 'error';

	/** Shutdown type. */
	const TYPE_SHUTDOWN = 'shutdown';

	/** Exception type. */
	const TYPE_EXCEPTION = 'exception';

	/**
	 * Array containing the assigned error handlers.
	 *
	 * @var array
	 */
	protected $errorHandlers = array();

	/**
	 * Set to TRUE if we are registered as the system error handler.
	 *
	 * @var bool
	 */
	protected $isRegistered = false;

	/**
	 * Stores the data of the error that's being handled at the moment.
	 *
	 * @var array
	 */
	protected $currentlyHandledError = array();

	/**
	 * The terminator instance.
	 *
	 * @var ITerminatable
	 */
	protected $terminator;

	/**
	 * Indicates if the Application has been started.
	 *
	 * @var bool
	 */
	protected $isApplicationStarted = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->unregister();
	}

	/**
	 * Registers the error handler container as the system error handler.
	 *
	 * @return void
	 */
	public function register() {
		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));
		register_shutdown_function(array($this, 'handleShutdown'));
		$this->isRegistered = true;
	}

	/**
	 * Unregisters as the system error handler container.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function unregister() {
		if ($this->isRegistered) {
			restore_error_handler();
			restore_exception_handler();
			$this->isRegistered = false;
		}
	}

	/**
	 * The object can be informed about the Application run via this method.
	 *
	 * @return void
	 */
	public function reportApplicationRun() {
		$this->isApplicationStarted = true;
	}

	/**
	 * Adds an error handler to the container.
	 *
	 * @param IErrorHandler $errorHandler   The error handler instance.
	 *
	 * @return void
	 */
	public function addErrorHandler(IErrorHandler $errorHandler) {
		$this->errorHandlers[] = $errorHandler;
	}

	/**
	 * Removes an error handler from the container.
	 *
	 * @param IErrorHandler $errorHandler   The error handler instance.
	 *
	 * @return bool   TRUE if the error handler was removed successfully, FALSE otherwise.
	 */
	public function removeErrorHandler(IErrorHandler $errorHandler) {
		$index = array_search($errorHandler, $this->errorHandlers);
		if (false === $index) {
			return false;
		}
		unset($this->errorHandlers[$index]);
		return true;
	}

	/**
	 * Returns the error handlers assigned to the container.
	 *
	 * @return array:
	 */
	public function getErrorHandlers() {
		return $this->errorHandlers;
	}

	/**
	 * Handles an error.
	 *
	 * Should not be called manually, only by PHP.
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occured.
	 * @param int    $line         The line in the file where the error occured.
	 * @param array  $context      The context of the error. (All variables that exist in the scope the error occured)
	 *
	 * @return bool   TRUE if we were able to handle the error, FALSE otherwise.
	 */
	public function handleError($errorLevel, $message, $file, $line, $context) {
		$errorReporting = error_reporting();

		if (!($errorLevel & $errorReporting)) {
			// The error should not be reported
			return false;
		}

		if (empty($this->errorHandlers)) {
			// We have no error handlers, let the standard PHP error handler handle it
			return false;
		}

		$errorId = $this->generateErrorId($message, $file, $line);

		$backTrace = debug_backtrace();
		// We are the first element, remove it from the trace
		array_shift($backTrace);

		$this->currentlyHandledError = array(
			'type'    => self::TYPE_ERROR,
			'level'   => $errorLevel,
			'message' => $message,
			'file'    => $file,
			'line'    => $line,
			'context' => $context,
			'trace'   => $backTrace,
			'errorId' => $errorId,
		);

		foreach ($this->errorHandlers as $errorHandler) {
			/** @var IErrorHandler $errorHandler */
			$errorHandler->handleError($errorLevel, $message, $file, $line, $context, $errorId, $backTrace);
		}

		$this->currentlyHandledError = array();

		// @codeCoverageIgnoreStart
		if ($this->isErrorFatal($errorLevel)) {
			// We encountered a fatal error, so we output an error and exit
			$this->unregister();
			Application::getInstance()->outputError();
			exit;
		}
		// @codeCoverageIgnoreEnd

		return true;
	}

	/**
	 * Handles an unhandled exception
	 *
	 * Should not be called manually, only by PHP.
	 *
	 * @param \Exception $exception   The exception to handle.
	 *
	 * @return void
	 */
	public function handleException($exception) {
		// @codeCoverageIgnoreStart
		if (!($exception instanceof \Exception) && !($exception instanceof \Error)) {
			// The error handlers can only handle exceptions that are descendants of the Exception built in class
			trigger_error('Unable to handle exception of type: ' . get_class($exception), E_USER_ERROR);
			return;
		}

		if (empty($this->errorHandlers)) {
			// We have no error handlers, trigger an error and let the default error handler handle it
			$this->unregister();
			trigger_error('Unhandled exception: ' . $exception->getMessage(), E_USER_ERROR);
			return;
		}
		// @codeCoverageIgnoreEnd

		$errorId = $this->generateErrorId($exception->getMessage(), $exception->getFile(), $exception->getLine());

		$this->currentlyHandledError = array(
			'type'      => self::TYPE_ERROR,
			'exception' => $exception,
			'errorId'   => $errorId,
		);

		foreach ($this->errorHandlers as $errorHandler) {
			/** @var IErrorHandler $errorHandler */
			$errorHandler->handleException($exception, $errorId);
		}

		$this->currentlyHandledError = array();
	}

	/**
	 * Sets the terminator object.
	 *
	 * @param ITerminatable $terminator   The terminator instance.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If trying to add a terminator when a terminator is already set.
	 */
	public function setTerminator(ITerminatable $terminator) {
		if (!empty($this->terminator)) {
			throw new Exception('Trying to set terminator when there is already one set');
		}

		$this->terminator = $terminator;
	}

	/**
	 * Run when the registered error handler terminates the current execution.
	 *
	 * @param bool $isFatalError   TRUE if the termination is because of a fatal error.
	 *
	 * @return void
	 */
	protected function terminate($isFatalError) {
		if (php_sapi_name() != 'cli' && !$this->isApplicationStarted) {
			header('HTTP/1.1 500');
		}

		if (!empty($this->terminator)) {
			$this->terminator->terminate($isFatalError);
		}

		exit;
	}

	/**
	 * Handles script shutdown.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function handleShutdown() {
		$error = error_get_last();

		if (!$error || !$this->isRegistered || !$this->isErrorFatal($error['type'])) {
			// Normal shutdown or we are not the system handler
			if ($this->isRegistered) {
				// We are only running the termination function if we are the registered error handler.
				$this->terminate(false);
			}
			return;
		}

		// We are shutting down because of a fatal error, if any more errors occur, they should be handled by
		// the default error handler.
		$this->unregister();

		// Shutdown because of a fatal error
		if (empty($this->errorHandlers)) {
			// We have no error handlers defined, send the fatal error to the SAPI's logger.
			error_log('No errorhandlers are defined and a fatal error occured: ' . $error['message']
				. '. File: ' . $error['file'] . ', line: ' . $error['line'] . '. Type: ' . $error['type'], 4);
			$this->terminate(true);
			return;
		}

		$errorId = $this->generateErrorId($error['message'], $error['file'], $error['line']);

		foreach ($this->errorHandlers as $errorHandler) {
			/** @var IErrorHandler $errorHandler */
			$errorHandler->handleShutdown($error['type'], $error['message'], $error['file'], $error['line'], $errorId);
		}

		// If we have a previously unhandled error, handle it. This can be caused by errors in one of the registered
		// error handlers, or by a php bug, that is triggered by a strict error that is triggered while autoloading.
		// {@see https://bugs.php.net/bug.php?id=54054}
		if (!empty($this->currentlyHandledError)) {
			$message = 'A fatal error occured while handling the error with the id: '
				. $this->currentlyHandledError['errorId'] . '. Fatal error ID: ' . $errorId;

			// If we have a previously unhandled error, handle it. This can be caused by errors in one of the registered
			foreach ($this->errorHandlers as $errorHandler) {
				$errorHandler->handleShutdown($error['type'], $message, $error['file'], $error['line'],
					$this->generateErrorId($message, $error['file'], $error['line']));
			}

			switch ($this->currentlyHandledError['type']) {
				case self::TYPE_ERROR:
					foreach ($this->errorHandlers as $errorHandler) {
						$errorHandler->handleError($this->currentlyHandledError['level'],
							$this->currentlyHandledError['message'], $this->currentlyHandledError['file'],
							$this->currentlyHandledError['line'], $this->currentlyHandledError['context'],
							$this->currentlyHandledError['errorId'], $this->currentlyHandledError['trace']);
					}
					break;

				case self::TYPE_EXCEPTION:
					foreach ($this->errorHandlers as $errorHandler) {
						$errorHandler->handleException($this->currentlyHandledError['exception'],
							$this->currentlyHandledError['errorId']);
					}
					break;
			}
		}
		$this->terminate(true);
	}

	/**
	 * Returns an error ID based on the message, file, line, hostname and current time.
	 *
	 * Generates the same error ID if the same error occurs within the timeframe set in the
	 * 'system.errorHandling.defaultIdTimeout' configuration option. If that option is not set, it will default to the
	 * value of the self::ERROR_HANDLING_DEFAULT_ID_TIMEOUT constant. The value should be set as seconds.
	 *
	 * @param string $message   The message of the error.
	 * @param string $file      The file where the error occured.
	 * @param int    $line      The line where the error occured.
	 *
	 * @return string
	 */
	protected function generateErrorId($message, $file, $line) {
		$idTimeout = Config::getInstance()->get('system.errorHandling.defaultIdTimeout',
			self::ERROR_HANDLING_DEFAULT_ID_TIMEOUT);

		if (0 == $idTimeout) {
			return md5($message . $file . (string)$line . php_uname('n'));
		} elseif ($idTimeout < 0) {
			return md5($message . $file . (string)$line . php_uname('n') . uniqid(''));
		} else {
			// @codeCoverageIgnoreStart
			return md5($message . $file . (string)$line . php_uname('n') . floor(time() / $idTimeout));
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Returns if the error should be considered fatal by the error level.
	 *
	 * @param int $errorLevel   The error level to check.
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore
	 */
	protected function isErrorFatal($errorLevel) {
		switch ($errorLevel) {
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_RECOVERABLE_ERROR:
				return true;
				break;
		}

		return false;
	}
}
