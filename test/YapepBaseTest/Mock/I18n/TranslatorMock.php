<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\I18n
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\I18n;


use YapepBase\I18n\ITranslator;

/**
 * Mock class for the ITranslator interface
 *
 * @package    YapepBaseTest
 * @subpackage Mock\I18n
 */
class TranslatorMock implements ITranslator {

	/**
	 * The translation method.
	 *
	 * The method will be passed the same parameters, in the same order, that the translate method receives.
	 *
	 * @var \Closure
	 */
	public $translationMethod;

	/**
	 * The default language.
	 *
	 * @var string
	 */
	public $defaultLanguage;

	/**
	 * Constructor. Sets the translation method.
	 *
	 * @param \Closure $translationMethod   The translation method. If not set, it defaults to returning the same string
	 */
	function __construct(\Closure $translationMethod = null) {
		if (is_null($translationMethod)) {
			$translationMethod  = function($string, $params) {
				return $string;
			};
		}
		$this->translationMethod = $translationMethod;
	}

	/**
	 * Translates the string.
	 *
	 * @param string $messageId   The string to translate.
	 * @param array  $params      Associative array with parameters for the translation. The key is the param name.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\I18n\TranslationNotFoundException   If the error mode is set to exception.
	 * @throws \YapepBase\Exception\I18n\ParameterException             If there are problems with the parameters.
	 */
	public function translate($messageId, array $params = array()) {
		$translationMethod = $this->translationMethod;
		return $translationMethod($messageId, $params);
	}
}