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
        $aRoutes = [
            'admin(/(.+))?' => 'admin/adminRouter/index$1',
        ];

        $oHandbookModel = Factory::model('Handbook', 'nails/module-admin');
        $aHandbookPages = $oHandbookModel->getAll();
        foreach ($aHandbookPages as $oPage) {
            $aRoutes[$oHandbookModel->generateUrl($oPage)] = 'admin/handbookFrontEnd/render/' . $oPage->id;
        }

        return $aRoutes;
    }
}
