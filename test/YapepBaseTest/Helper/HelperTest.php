<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Helper
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Helper;


use YapepBase\Application;

use YapepBaseTest\Mock\Helper\HelperMock;
use YapepBaseTest\Mock\I18n\TranslatorMock;

/**
 * Test class for helpers
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class HelperTest extends \YapepBaseTest\TestAbstract {

	protected function setUp() {
		parent::setUp();
		Application::getInstance()->setI18nTranslator(new TranslatorMock(
			function($string, $params) {
				return json_encode(array(
					'string' => $string,
					'params' => $params,
				));
			}
		));
	}

	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->clearI18nTranslator();
	}

	public function testTranslation() {
		$helper = new HelperMock();
		$expectedResult = array(
			'string' => 'test',
			'params' => array('testParam' => 'testValue'),
		);
		$this->assertSame($expectedResult, json_decode($helper->_('test', array('testParam' => 'testValue')),
			true), 'The translator method does not return the expected result');
	}

}