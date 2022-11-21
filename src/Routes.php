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

use Nails\Common\Interfaces\RouteGenerator;
use Nails\Config;

class Routes implements RouteGenerator
{
    /**
     * Returns an array of routes for this module
     *
     * @return array
     */
    public static function generate(): array
    {
        return [
            Config::get('ADMIN_URL', Constants::MODULE_URL) . '(/(.+))?' => 'admin/adminRouter/index$1',
        ];
    }
}
