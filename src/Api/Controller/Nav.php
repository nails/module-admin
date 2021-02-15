<?php

/**
 * Admin API end points: Nav
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Api;
use Nails\Factory;

class Nav extends BaseApi
{
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Determines whether the user is authenticated or not
     *
     * @param string $sHttpMethod The HTTP Method protocol being used
     * @param string $sMethod     The controller method being executed
     *
     * @return bool
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        return parent::isAuthenticated($sHttpMethod, $sMethod) && isAdmin();
    }

    // --------------------------------------------------------------------------

    /**
     * Saves the user's admin nav preferences
     * @return Api\Factory\ApiResponse
     */
    public function postSave()
    {
        $oInput   = Factory::service('Input');
        $aPrefRaw = array_filter((array) $oInput->post('preferences'));
        $oPref    = new \stdClass();

        foreach ($aPrefRaw as $sModule => $aOptions) {
            $oPref->{$sModule}       = new \stdClass();
            $oPref->{$sModule}->open = stringToBoolean($aOptions['open']);
        }

        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);
        $oAdminModel->setAdminData('nav_state', $oPref);
        return Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    /**
     * Resets a user's admin nav preferences
     * @return Api\Factory\ApiResponse
     */
    public function postReset()
    {
        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);
        $oAdminModel->unsetAdminData('nav_state');
        return Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
    }
}
