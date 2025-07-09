<?php

namespace Nails\Admin\Traits\Api;

trait RestrictToAdmin
{
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
        return parent::isAuthenticated($sHttpMethod, $sMethod)
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
