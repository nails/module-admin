<?php

namespace Nails\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Service\Permission;
use Nails\Api\Controller\Base;
use Nails\Factory;

abstract class BaseApi extends Base
{
    /**
     * Require the user be authenticated to use any endpoint
     */
    const REQUIRE_AUTH = true;

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user is authenticated to access these methods
     *
     * @param string $sHttpMethod The HTTP Method being used
     * @param string $sMethod     The method being called
     *
     * @return bool
     * @throws \Nails\Common\Exception\FactoryException
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        /** @var Permission $oPermissionService */
        $oPermissionService = Factory::service('Permission', Constants::MODULE_SLUG);

        return parent::isAuthenticated($sHttpMethod, $sMethod)
            && $oPermissionService->isIpAllowed()
            && isAdmin();
    }
}
