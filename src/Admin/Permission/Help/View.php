<?php

namespace Nails\Admin\Admin\Permission\Help;

use Nails\Admin\Interfaces\Permission;

class View implements Permission
{
    public function label(): string
    {
        return 'Can view help videos';
    }

    public function group(): string
    {
        return 'Help';
    }
}
