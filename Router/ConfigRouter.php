<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;
use YapepBase\Exception\RouterException;
use YapepBase\Config;
use YapepBase\Request\IRequest;

/**
 * ConfigRouter class.
 *
 * Routes a request based on an array stored in a config variable.
 * The config variable's structure should match the config for an ArrayRouter {@see \YapepBase\Router\ArrayRouter}.
 *
 * @package    YapepBase
 * @subpackage Router
 */
class ConfigRouter extends ArrayRouter {

    /**
     * Constructor.
     *
     * @param IRequest $request
     * @param string   $configName   The name of the configuration where the routes are stored
     *
     * @throws RouterException   On error
     */
    public function __construct(IRequest $request, $configName) {
        $routes = Config::getInstance()->get($configName, false);
        if (!is_array($routes)) {
            throw new RouterException('No route config found');
        }
        parent::__construct($request, $routes);
    }

}