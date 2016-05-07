<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package   YapepBaseTest
 * @copyright 2011 The YAPEP Project All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest;


use YapepBase\Config;
use YapepBase\Exception\ConfigException;

/**
 * Config test case.
 *
 * @todo add checks for exception handling when we start throwing exceptions for not-set config values
 */
class ConfigTest extends \YapepBaseTest\TestAbstract
{
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		$this->config = Config::getInstance();
	}

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();
	}
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		$this->config->clear();
		parent::tearDown();
	}

	/**
	 * Tests setting a single value, clearing and returning all items
	 */
	public function testBasics() {
		$config = Config::getInstance();
		$this->assertInstanceOf('\YapepBase\Config', $config, 'Retrieved object is not a Config instance');

		$result = $this->config->get('*');
		$this->assertInternalType('array', $result, 'Result is not an array');
		$this->assertTrue(empty($result), 'Result is not empty');

		$this->config->set('test', 'value');

		$result = $this->config->get('*');
		$this->assertInternalType('array', $result, 'Result is not an array');
		$this->assertFalse(empty($result), 'Result is empty after setting value');

		$this->config->clear();

		$result = $this->config->get('*');
		$this->assertInternalType('array', $result, 'Result is not an array');
		$this->assertTrue(empty($result), 'Result is not empty after clearing');

		$this->config->set('test', 'value');

		$result = $this->config->get('test');
		$this->assertSame('value', $result, 'Result not the previously set value');

		$this->config->delete('test');

		$result = $this->config->get('test', false);
		$this->assertFalse($result, 'Result is not empty after deleting value');

	}

	/**
	 * Tests default handling
	 */
	public function testDefault() {
		$this->assertSame('test', $this->config->get('nonexistent', 'test'), 'Specified default does not match');

		try {
			$this->config->get('');
			$this->fail('Empty request does not throw an exception');
		} catch (ConfigException $exception) {}
	}

	/**
	 * Tests setting simple values
	 */
	public function testSimpleValues () {
		$this->config->set('test1', '123');
		$this->assertSame('123', $this->config->get('test1'), 'Setting simple value failed');

		$this->config->set(array('test2' => '234'));
		$this->assertSame('234', $this->config->get('test2'), 'Setting array value failed');
		$this->assertSame('123', $this->config->get('test1'), 'Setting array interferes with previously set values');

		$this->config->set(array('test1' => '345'));
		$this->assertSame('345', $this->config->get('test1'), 'overriding with array value failed');
		$this->assertSame('234', $this->config->get('test2'),
			'Overriding with array interferes with previously set values');

		$this->config->set('test2', '456');
		$this->assertSame('456', $this->config->get('test2'), 'overriding with singe value failed');
		$this->assertSame('345', $this->config->get('test1'),
			'Overriding with single value interferes with previously set values');

		$this->config->set(array('test3' => '567', 'test4' => 678));
		$this->assertSame('567', $this->config->get('test3'), 'Setting multiple values failed');
		$this->assertSame(678, $this->config->get('test4'), 'Setting multiple values failed');

		$this->assertEquals(4, count($this->config->get('*')), 'Invalid value count');
	}

	/**
	 * Test default value handling
	 */
	public function testDefaults() {
		$this->assertSame('123', $this->config->get('test', '123'));
	}

	/**
	 * Test configuration section handling
	 */
	public function testSections () {
		$testData = array(
			'test.first' => 1,
			'test.second' => 2,
			'test.secondLevel.first' => 'first',
			'test.secondLevel.second' => 'second',
			'test2' => 'test',
		);

		$this->config->set($testData);

		$this->assertSame(false, $this->config->get('test*', false), 'Returning invalid wildcard returns the default');

		$result = $this->config->get('test.*');
		$this->assertInternalType('array', $result);
		$this->assertEquals(4, count($result));
		$this->assertArrayHasKey('first', $result);
		$this->assertSame(1, $result['first']);

		$result = $this->config->get('test.*', null, true);
		$this->assertInternalType('array', $result);
		$this->assertEquals(4, count($result));
		$this->assertArrayHasKey('test.first', $result);
		$this->assertSame(1, $result['test.first']);
	}
}

