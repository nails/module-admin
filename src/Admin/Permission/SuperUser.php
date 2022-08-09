<?php

namespace Nails\Admin\Admin\Permission;

use Nails\Admin\Interfaces\Permission;

class SuperUser implements Permission
{
    public function label(): string
    {
        return 'Is a super user';
    }

    public function group(): string
    {
        return '';
    }
}
