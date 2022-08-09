<?php

namespace Nails\Admin\Factory\Permission;

use Nails\Admin\Interfaces\Permission;
use Nails\Common\Factory\Component;

/**
 * Class Group
 *
 * @package Nails\Admin\Factory\PErmission
 */
class Group
{
    protected Component $oComponent;
    protected array     $aPermissions = [];

    // --------------------------------------------------------------------------

    public function __construct(Component $oComponent, array $aPermissions = [])
    {
        $this->oComponent = $oComponent;
        foreach ($aPermissions as $oPermission) {
            $this->add($oPermission);
        }
    }

    // --------------------------------------------------------------------------

    public function add(Permission $oPermission): self
    {
        $this->aPermissions[] = $oPermission;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getComponent(): Component
    {
        return $this->oComponent;
    }

    // --------------------------------------------------------------------------

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->aPermissions;
    }

    // --------------------------------------------------------------------------

    public function sort(\Closure $cSortFunction = null): self
    {

        $cSortFunction =
            $cSortFunction
            ?? function (Permission $a, Permission $b) {
                return $a->group() <=> $b->group();
            };

        usort($this->aPermissions, $cSortFunction);
        return $this;
    }
}
