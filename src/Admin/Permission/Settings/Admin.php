<?php

namespace Nails\Admin\Admin\Permission\Settings;

use Nails\Admin\Interfaces\Permission;

class Admin implements Permission
{
    public function label(): string
    {
        return 'Can manage Admin settings';
    }

    public function group(): string
    {
        return 'Settings';
    }
}
