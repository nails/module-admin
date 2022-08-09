<?php

namespace Nails\Admin\Admin\Permission\ChangeLog;

use Nails\Admin\Interfaces\Permission;

class View implements Permission
{
    public function label(): string
    {
        return 'Can view Change Log item details';
    }

    public function group(): string
    {
        return 'Change Log';
    }
}
