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


use YapepBase\Exception\ParameterException;

/**
 * Number related helper functions.
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class NumberHelper extends HelperAbstract {

	/**
	 * Returns a random number between in the given range.
	 *
	 * @param int $minimum   The possible lowest value to return.
	 * @param int $maximum   The possible highest value to return.
	 *
	 * @throws \YapepBase\Exception\ParameterException
	 *
	 * @return int
	 */
	public function getRandom($minimum, $maximum) {
		if ($minimum > $maximum) {
			throw new ParameterException('Minimum (' . $minimum . ') is bigger than the maximum (' . $maximum .')');
		}

		return mt_rand($minimum, $maximum);
	}
}