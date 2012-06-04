<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Exception;

/**
 * RouterException class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class RouterException extends Exception {

	/** The specified route was not found */
	const ERR_NO_ROUTE_FOUND = 101;
	/** Syntax error in the param */
	const ERR_SYNTAX_PARAM = 201;
	/** A required param is missing */
	const ERR_MISSING_PARAM = 301;
	/** Error with the route config */
	const ERR_ROUTE_CONFIG = 401;
}