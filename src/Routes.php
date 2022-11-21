<?php

/**
 * Generates admin routes
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

use Nails\Admin\Service\Controller;
use Nails\Common\Interfaces\RouteGenerator;
use Nails\Config;
use Nails\Factory;

class Routes implements RouteGenerator
{
    /**
     * Returns an array of routes for this module
     *
     * @return array
     */
    public static function generate(): array
    {
        /** @var Controller $oController */
        $oController = Factory::service('Controller', Constants::MODULE_SLUG);

        return [
            $oController->getUrlPrefix() . '(/(.+))?' => 'admin/adminRouter/index$1',
        ];
    }
}
