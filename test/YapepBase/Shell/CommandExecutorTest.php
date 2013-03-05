<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Shell
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Shell;


/**
 * Test for the CommandExecutor class
 *
 * @package    YapepBase
 * @subpackage Shell
 */
class CommandExecutorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Tests the full command creation method.
	 */
	public function testGetCommand() {
		$expectedCommand = '/test';
		$expectedParams = array(
			'-v' => null,
			'-t' => 'testValue',
			null => 'testArgument',
		);

		// Test generating the command with the default separator
		$command = new CommandExecutor($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t ' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test generating the command with an empty separator
		$command = new CommandExecutor($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$command->setSwitchValueSeparator('');

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test generating the command with '=' as the separator
		$command = new CommandExecutor($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$command->setSwitchValueSeparator('=');

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t=' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test with multiple arguments
		$command = new CommandExecutor($expectedCommand);

		$command->addParam(null, 'testArgument1');
		$command->addParam(null, 'testArgument 2');

		$expectedOutput = escapeshellarg($expectedCommand) .' ' . escapeshellarg('testArgument1') . ' '
			. escapeshellarg('testArgument 2');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test with multiple instances of the same switch
		$command = new CommandExecutor($expectedCommand);

		$command->addParam('-v');
		$command->addParam('-v');
		$command->addParam('-v');

		$expectedOutput = escapeshellarg($expectedCommand) .' -v -v -v';

		$this->assertSame($expectedOutput, $command->getCommand());
	}
}
