<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\ErrorHandler
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\ErrorHandler;


use YapepBase\ErrorHandler\IErrorHandler;

/**
 * LoggerMock class
 *
 * @package    YapepBaseTest
 * @subpackage Test\Mock\ErrorHandler
 * @codeCoverageIgnore
 */
class ErrorHandlerMock implements IErrorHandler {

	public $handledErrors = array();

	public $handledExceptions = array();

	public $handledShutdowns = array();

	public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array()) {
		$this->handledErrors[] = array(
			'errorLevel' => $errorLevel,
			'message'    => $message,
			'file'       => $file,
			'line'       => $line,
			'context'    => $context,
			'errorId'    => $errorId,
			'backTrace'  => $backTrace,
		);
	}

	public function handleException($exception, $errorId) {
		$this->handledExceptions[] = array(
			'exception' => $exception,
			'errorId'   => $errorId,
		);
	}

	public function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
		$this->handledShutdowns[] = array(
			'errorLevel' => $errorLevel,
			'message'    => $message,
			'file'       => $file,
			'line'       => $line,
			'errorId'    => $errorId,
		);
	}
}
