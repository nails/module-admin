<?php

namespace Nails\Admin\Admin\Permission\Utilities\Routes;

use Nails\Admin\Interfaces\Permission;

class Rewrite implements Permission
{
    public function label(): string
    {
        return 'Can rewrite application routes';
    }

    public function group(): string
    {
        return 'Utilities';
    }
}
