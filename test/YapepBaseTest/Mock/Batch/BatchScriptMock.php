<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Batch;

use YapepBase\Batch\BatchScriptAbstract;

/**
 * Mock class for testing the BatchScriptAbstract class
 *
 * @package    YapepBaseTest
 * @subpackage Test\Mock\Batch
 */
class BatchScriptMock extends BatchScriptAbstract {

	/**
	 * Data storage for the closure.
	 *
	 * @var mixed
	 */
	public $closureData;

	/**
	 * The closure that's called from the execute method.
	 *
	 * @var \Closure
	 */
	public $executeClosure;

	/**
	 * Executes the script.
	 *
	 * @return void
	 */
	protected function execute() {
		$closure = $this->executeClosure;
		$closure();
	}

	public function setToView($nameOrData, $value = null) {
		parent::setToView($nameOrData, $value);
	}

	/**
	 * Returns the script's description.
	 *
	 * This method should return a the description for the script. It will be used as the script description in the
	 * help.
	 *
	 * @return string
	 */
	protected function getScriptDescription() {
		return 'Mock script';
	}

	/**
	 * This function is called, if the process receives an interrupt, term signal, etc. It can be used to clean up
	 * stuff. Note, that this function is not guaranteed to run or it may run after execution.
	 *
	 * @return void
	 */
	protected function abort() {

	}

}