<?php

namespace Nails\Admin\Service;

use Nails\Admin\Admin\Controller\Dashboard;
use Nails\Admin\Admin\Permission\SuperUser;
use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Controller\DefaultController;
use Nails\Admin\Exception\AdminException;
use Nails\Admin\Exception\PermissionException;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Model\Admin;
use Nails\Auth\Resource\User;
use Nails\Auth\Resource\User\Group;
use Nails\Common\Factory\Component;
use Nails\Common\Helper\ArrayHelper;
use Nails\Components;
use Nails\Factory;

class Permission
{
    /**
     * The required namespace for permissions
     */
    const SRC_PATH = 'Admin\\Permission';

    // --------------------------------------------------------------------------

    /**
     * Discovered Permissions
     *
     * @var \Nails\Admin\Factory\Permission\Group[]
     */
    protected array $aPermissions = [];

    /**
     * Discovered Permissions, grouped by component
     *
     * @var \Nails\Admin\Factory\Permission\Group[]
     */
    protected array $aPermissionsGrouped = [];

    // --------------------------------------------------------------------------

    /**
     * Construct Permission
     *
     * @throws \Nails\Common\Exception\NailsException
     */
    public function __construct()
    {
        $this->discover();
    }

    // --------------------------------------------------------------------------

    /**
     * Discover available permissions
     *
     * @return void
     * @throws \Nails\Common\Exception\NailsException
     */
    protected function discover(): void
    {
        foreach (Components::available() as $oComponent) {

            $oClasses = $oComponent
                ->findClasses(static::SRC_PATH)
                ->whichImplement(\Nails\Admin\Interfaces\Permission::class);

            if (count($oClasses)) {

                /** @var \Nails\Admin\Factory\Permission\Group $oGroup */
                $oGroup = Factory::factory('PermissionGroup', Constants::MODULE_SLUG, $oComponent);

                foreach ($oClasses as $sClass) {

                    $oPermission          = new $sClass();
                    $this->aPermissions[] = $oPermission;

                    $oGroup->add($oPermission);
                }

                $this->aPermissionsGrouped[] = $oGroup;
            }
        }
    }

    // --------------------------------------------------------------------------

    public function get(): array
    {
        return $this->aPermissions;
    }

    // --------------------------------------------------------------------------

    public function getGrouped(): array
    {
        return $this->aPermissionsGrouped;
    }

    // --------------------------------------------------------------------------

    public function userHasPermission($mPermission, User $oUser = null): bool
    {
        $oUser = $oUser ?? activeUser();
        return $oUser
            ? $this->groupHasPermission($mPermission, $oUser->group())
            : false;
    }

    // --------------------------------------------------------------------------

    public function isSuperUser(User $oUser = null): bool
    {
        $oUser = $oUser ?? activeUser();
        return $oUser
            ? $this->isGroupSuperUser($oUser->group())
            : false;
    }

    // --------------------------------------------------------------------------

    public function isGroupSuperUser(Group $oGroup = null): bool
    {
        $oGroup = $oGroup ?? (activeUser() ? activeUser()->group() : null);
        return $oGroup
            ? in_array(SuperUser::class, $oGroup->acl)
            : false;
    }

    // --------------------------------------------------------------------------

    public function isAdmin(User $oUser = null): bool
    {
        $oUser  = $oUser ?? activeUser();
        $oGroup = $oUser ? $oUser->group() : null;
        return !empty($oGroup->acl);
    }

    // --------------------------------------------------------------------------

    public function groupHasPermission($mPermission, Group $oGroup = null, bool $bIgnoreSuperUser = false): bool
    {
        $oGroup = $oGroup ?? (activeUser() ? activeUser()->group() : null);

        if (empty($oGroup)) {
            return false;
        }

        if (!$bIgnoreSuperUser && $this->isGroupSuperUser($oGroup)) {
            return true;

        } elseif ($mPermission instanceof \Nails\Admin\Interfaces\Permission) {
            $sPermission = get_class($mPermission);

        } elseif (is_string($mPermission) && class_exists($mPermission) && classImplements($mPermission, \Nails\Admin\Interfaces\Permission::class)) {
            $sPermission = $mPermission;

        } else {
            throw new PermissionException(sprintf(
                'Invalid permission \'%s\', must be a class which implements \'%s\'',
                $mPermission,
                \Nails\Admin\Interfaces\Permission::class
            ));
        }

        return in_array($sPermission, $oGroup->acl);
    }
}
