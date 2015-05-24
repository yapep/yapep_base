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


class HelperTest extends \YapepBaseTest\BaseTest {

	protected function setUp() {
		parent::setUp();
		Application::getInstance()->setI18nTranslator(new TranslatorMock(
			function($sourceClass, $string, $params, $language) {
				return json_encode(array(
					'class'    => $sourceClass,
					'string'   => $string,
					'params'   => $params,
					'language' => $language,
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
			'class' => 'YapepBaseTest\Mock\Helper\HelperMock',
			'string' => 'test',
			'params' => array('testParam' => 'testValue'),
			'language' => 'en',
		);
		$this->assertSame($expectedResult, json_decode($helper->_('test', array('testParam' => 'testValue'), 'en'),
			true), 'The translator method does not return the expected result');
	}

}