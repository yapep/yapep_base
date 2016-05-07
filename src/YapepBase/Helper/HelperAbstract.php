<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Helper
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;


use YapepBase\Application;

/**
 * Abstract base class for helpers
 *
 * @package    YapepBase
 * @subpackage Helper
 */
abstract class HelperAbstract {

	/**
	 * Translates the specified string.
	 *
	 * @param string $messageId    The string.
	 * @param array  $parameters   The parameters for the translation.
	 *
	 * @return string
	 */
	protected function _($messageId, $parameters = array()) {
		return Application::getInstance()->getI18nTranslator()->translate($messageId, $parameters);
	}

}