<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Log
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Log;


use YapepBase\Config;
use YapepBase\Log\Message\ErrorMessage;
use YapepBase\Log\SyslogLogger;

use YapepBaseTest\Mock\Syslog\SyslogConnectionMock;

/**
 * Test class for Syslog.
 */
class SyslogTest extends \YapepBaseTest\TestAbstract {

	public function setUp() {
		parent::setUp();
		Config::getInstance()->set(array(
			'resource.log.default.applicationIdent' => 'testApp',
			'resource.log.default.facility'         => \YapepBase\Syslog\Syslog::LOG_USER,

			'resource.log.pidSapi.applicationIdent' => 'testApp',
			'resource.log.pidSapi.facility'         => \YapepBase\Syslog\Syslog::LOG_USER,
			'resource.log.pidSapi.includeSapiName'  => true,
			'resource.log.pidSapi.addPid'           => true,

			'resource.log.noIdent.facility'         => LOG_USER,

			'resource.log.noFacility.applicationIdent' => 'testApp',

			'system.application.name' => 'testApp'

		));
	}

	public function tearDown() {
		Config::getInstance()->clear();
	}

	public function testLog() {
		$mock = new SyslogConnectionMock();
		$this->assertFalse($mock->isOpen);
		$syslog = new SyslogLogger('default', $mock);
		$this->assertTrue($mock->isOpen);

		$msg = new ErrorMessage();
		$msg->set('Test message', 'testType', 'testId', \YapepBase\Syslog\Syslog::LOG_NOTICE);
		$syslog->log($msg);

		$this->assertEquals(array(
			array(
				'priority' => \YapepBase\Syslog\Syslog::LOG_NOTICE + \YapepBase\Syslog\Syslog::LOG_USER,
				'message' => '[error]|error_id=testId|type=testType|app=testApp|message=Test message',
				'ident' => 'testApp',
				'date' => null
			)
		), $mock->messages);
	}

	public function testLogInstantiation() {
		$mock = new SyslogConnectionMock();
		new SyslogLogger('default', $mock);
	}

	public function testLogWithPidAndSapi() {
		$mock = new SyslogConnectionMock();
		$syslog = new SyslogLogger('pidSapi', $mock);
		$msg = new ErrorMessage();
		$msg->set('Test message', 'testType', 'testId', \YapepBase\Syslog\Syslog::LOG_NOTICE);
		$syslog->log($msg);

		$this->assertEquals(array(
			array(
				'priority' => \YapepBase\Syslog\Syslog::LOG_NOTICE + \YapepBase\Syslog\Syslog::LOG_USER,
				'message' => '[error]|error_id=testId|type=testType|app=testApp|message=Test message',
				'ident' => 'testApp-' . PHP_SAPI . '[pid]',
				'date' => null
			)
		), $mock->messages);
	}

	public function testInvalidConfiguration() {
		$mock = new SyslogConnectionMock();

		try {
			new SyslogLogger('noIdent', $mock);
			$this->fail('Calling syslog class without applicationIdent config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}

		try {
			new SyslogLogger('noFacility', $mock);
			$this->fail('Calling syslog class without facility config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}

		try {
			new SyslogLogger('nonexistent', $mock);
			$this->fail('Calling syslog class without config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}
	}
}
