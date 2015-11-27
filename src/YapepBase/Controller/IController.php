<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * Controller interface
 *
 * Configuration options:
 * <ul>
 *   <li>system.performStrictControllerActionNameValidation: If this option is TRUE, the action's name will be
 *                                                           validated in a case sensitive manner. This is recommended
 *                                                           for development, but not recommended for production as it
 *                                                           can cause errors, and will somewhat impact the performance.
 *                                                           Optional, defaults to FALSE.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Controller
 */
interface IController {

	/**
	 * Runs the specified action
	 *
	 * @param string $action   The name of the action (without the controller specific prefix)
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\RedirectException     On redirects. These should not be treated as errors!
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	public function run($action);
}
