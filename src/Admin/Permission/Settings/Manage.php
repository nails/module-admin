<?php

namespace Nails\Admin\Admin\Permission\Settings;

use Nails\Admin\Interfaces\Permission;

class Manage implements Permission
{
    public function label(): string
    {
        return 'Can manage all application settings';
    }

    public function group(): string
    {
        return 'Settings';
    }
}
