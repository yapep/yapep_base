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

/**
 * @codeCoverageIgnore
 */
class LockingBatchScriptMock extends \YapepBase\Batch\LockingBatchScriptAbstract {

	public $hasWorked = false;

	public $description = 'Locking batch script mock';

	public $aborted = false;

	public function execute() {
		$this->hasWorked = true;
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
		return $this->description;
	}

	/**
	 * This function is called, if the process receives an interrupt, term signal, etc. It can be used to clean up
	 * stuff. Note, that this function is not guaranteed to run or it may run after execution.
	 *
	 * @return void
	 */
	protected function abort() {
		$this->aborted = true;
	}

	public function startFakeRun() {
		return $this->acquireLock();
	}

	public function stopFakeRun() {
		$this->releaseLock();
	}
}