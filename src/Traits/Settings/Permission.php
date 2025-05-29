<?php

namespace Nails\Admin\Traits\Settings;

/**
 * Trait Permission
 *
 * @package Nails\Admin\Traits\Settings
 */
trait Permission
{
    /**
     * Returns whether a user has permission or not
     *
     * @param string|null $sPermission The additional permission to check
     *
     * @return bool
     */
    protected function userHasPermission(?string $sPermission = null): bool
    {
        return userHasPermission(
            $sPermission
                ? 'admin:admin:settings:' . md5(static::class) . ':' . $sPermission
                : 'admin:admin:settings:' . md5(static::class)
        );
    }
}
