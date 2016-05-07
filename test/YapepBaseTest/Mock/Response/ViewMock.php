<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Response
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Response;


use YapepBase\View\ViewAbstract;

/**
 * @codeCoverageIgnore
 */
class ViewMock extends ViewAbstract {
	protected $content = '';

	public function set($content = '') {
		$this->content = $content;
	}

	protected function renderContent() {
		echo $this->content;
	}

	public function render() {
		echo $this->content;
	}

	function __toString() {
		return $this->content;
	}

}