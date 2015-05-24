<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBaseTest
 * @subpackage Helper
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Helper;


use YapepBase\Helper\TextHelper;


class TextHelperTest extends \YapepBaseTest\BaseTest {

	/**
	 * Tests the stripSlashes() method.
	 *
	 * @return void
	 */
	public function testStripSlashes() {
		$source = array(
			'var1' => 'test\\\"var',
			'var2' => array(
				'var2-1' => 'test\\\\var',
				'var2-2' => '\\\'',
			),
		);
		$target = array(
			'var1' => 'test\"var',
			'var2' => array(
				'var2-1' => 'test\\var',
				'var2-2' => '\'',
			),
		);
		$this->assertEquals($target, TextHelper::stripSlashes($source));
	}
}