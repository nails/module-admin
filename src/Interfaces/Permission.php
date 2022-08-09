<?php

namespace Nails\Admin\Interfaces;

interface Permission
{
    /**
     * The human friendly name given to the permission
     *
     * @return string
     */
    public function label(): string;

    /**
     * Specifies which group the permission belongs to, used primarily for rendering
     * the permissions when editing a group
     *
     * @return string
     */
    public function group(): string;
}
