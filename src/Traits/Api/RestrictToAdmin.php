<?php

namespace Nails\Admin\Traits\Api;

use Nails\Admin\Constants;
use Nails\Admin\Service\Permission;
use Nails\Factory;

trait RestrictToAdmin
{
    /**
     * Determines whether the user is authenticated or not
     *
     * @param string $sHttpMethod The HTTP Method protocol being used
     * @param string $sMethod     The controller method being executed
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
            && isAdmin()
            && (!static::requirePermission() || userHasPermission(static::requirePermission()));
    }

    // --------------------------------------------------------------------------

    /**
     * If, in addition to being an admin, a particular permission is required
     */
    public static function requirePermission(): ?string
    {
        return null;
    }
}
