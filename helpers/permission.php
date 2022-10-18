<?php

use Nails\Admin\Constants;
use Nails\Auth\Resource\User;
use Nails\Auth\Resource\User\Group;

if (!function_exists('userHasPermission')) {
    function userHasPermission($mPermission, User $oUser = null): bool
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        return $oPermission->userHasPermission($mPermission, $oUser);
    }
}

if (!function_exists('userHasAnyPermission')) {
    function userHasAnyPermission(array $aPermissions, User $oUser = null): bool
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        foreach ($aPermissions as $mPermission) {
            if ($oPermission->userHasPermission($mPermission, $oUser)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('groupHasPermission')) {
    function groupHasPermission($mPermission, Group $oGroup = null, bool $bIgnoreSuperUser = false): bool
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        return $oPermission->groupHasPermission($mPermission, $oGroup, $bIgnoreSuperUser);
    }
}

if (!function_exists('isSuperUser')) {
    function isSuperUser(User $oUser = null)
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        return $oPermission->isSuperUser($oUser);
    }
}

if (!function_exists('isGroupSuperUser')) {
    function isGroupSuperUser(Group $oGroup = null)
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        return $oPermission->isGroupSuperUser($oGroup);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(User $oUser = null): bool
    {
        /** @var \Nails\Admin\Service\Permission $oPermission */
        $oPermission = \Nails\Factory::service('Permission', Constants::MODULE_SLUG);
        return $oPermission->isAdmin($oUser);
    }
}
