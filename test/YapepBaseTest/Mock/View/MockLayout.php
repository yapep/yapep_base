<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\View;


/**
 * Mock for a Layout.
 * @codeCoverageIgnore
 */
class MockLayout extends \YapepBase\View\LayoutAbstract {

	/**
	 * Render the fake content
	 */
	protected function renderContent() {
		echo 'Layout: ' . $this->renderInnerContent();
	}

	/**
	 * Displays the given block
	 *
	 * @param \YapepBase\View\BlockAbstract $block   The block.
	 *
	 * @return void
	 */
	public function renderBlock(\YapepBase\View\BlockAbstract $block) {
		parent::renderBlock($block);
	}
}
