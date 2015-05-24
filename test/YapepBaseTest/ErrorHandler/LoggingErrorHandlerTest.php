<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage ErrorHandler
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\ErrorHandler;


use YapepBase\ErrorHandler\LoggingErrorHandler;
use YapepBase\Exception\Exception;
use YapepBase\ErrorHandler\ErrorHandlerHelper;

use YapepBaseTest\Mock\Log\LoggerMock;

/**
 * Test class for LoggingErrorHandler.
 */
class LoggingErrorHandlerTest extends \YapepBaseTest\BaseTest {

	/**
	 * @var LoggerMock
	 */
	protected $logger;

	/**
	 * @var LoggingErrorHandler
	 */
	protected $errorHandler;


	public function setUp() {
		parent::setUp();
		$this->logger = new LoggerMock();
		$this->errorHandler = new LoggingErrorHandler($this->logger);
	}


	public function testHandleError() {
		$this->errorHandler->handleError(E_NOTICE, 'test', 'test', 1, array('testVar' => 'testValue'), '2', array());
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_NOTICE_DESCRIPTION, $fields['type'],
			'The log message type does not match');
		$this->assertSame('2', $fields['error_id'], 'The log ID does not match');
		$this->assertSame(LOG_NOTICE, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}


	public function testHandleException() {
		$exception = new Exception('test', 1);
		$this->errorHandler->handleException($exception, '2');
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION, $fields['type'],
			'The log message type does not match');
		$this->assertSame('2', $fields['error_id'], 'The log ID does not match');
		$this->assertSame(LOG_ERR, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}


	public function testHandleShutdown() {
		$this->errorHandler->handleShutdown(E_ERROR, 'test', 'test', 1, '2');
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_ERROR_DESCRIPTION, $fields['type'],
			'The log message type does not match');
		$this->assertSame('2', $fields['error_id'], 'The log ID does not match');
		$this->assertSame(LOG_ERR, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}
}
