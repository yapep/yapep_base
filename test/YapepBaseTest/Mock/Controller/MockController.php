<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Controller;

/**
 * @codeCoverageIgnore
 */
class MockController extends \YapepBase\Controller\ControllerAbstract {

	/**
	 * @var \YapepBase\Request\IRequest;
	 */
	public $request;

	/**
	 * @var \YapepBase\Response\IResponse
	 */
	public $response;


	/**
	 * Set to true on every action run.
	 *
	 * @var bool
	 */
	public $ran = false;

	/**
	 * Stores the action that will be executed
	 *
	 * @var \Closure
	 */
	public $action;

	protected function doTest() {
		$this->ran = true;
		// PHP 5.3 workaround
		$action = $this->action;
		return $action($this);
	}

	public function setAction(\Closure $action) {
		$this->ran = false;
		$this->action = $action;
	}

	public function internalRedirect($controllerName, $action) {
		parent::internalRedirect($controllerName, $action);
	}

	public function setToView($nameOrData, $value = null) {
		parent::setToView($nameOrData, $value);
	}

	public function _($string, $parameters = array(), $language = null) {
		return parent::_($string, $parameters, $language);
	}

	public function doRedirectTarget() {
		return 'redirect test';
	}
}