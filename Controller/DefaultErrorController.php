<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;

/**
 * DefaultErrorController class
 *
 * @package    YapepBase
 * @subpackage Controller
 */
class DefaultErrorController extends AbstractErrorController {
    /**
     * Runs on page not found (404) errors
     *
     * @return \YapepBase\View\Template|string
     */
    protected function do404() {
        return '<h1>404 - Page not found</h1>';
    }

    /**
     * Runs on internal server error (500) erorrs
     *
     * @return \YapepBase\View\Template|string
     */
    protected function do500() {
        return '<h1>500 - Internal server error</h1>';
    }

}