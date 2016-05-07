<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   I18n
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\I18n;


/**
 * Interface for internationalization translators.
 *
 * @package    YapepBase
 * @subpackage I18n
 */
interface ITranslator {

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
	public function translate($messageId, array $params = array());
}