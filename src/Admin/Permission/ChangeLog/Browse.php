<?php

namespace Nails\Admin\Admin\Permission\ChangeLog;

use Nails\Admin\Interfaces\Permission;

class Browse implements Permission
{
    public function label(): string
    {
        return 'Can browse Change Log items';
    }

    public function group(): string
    {
        return 'Change Log';
    }
}
