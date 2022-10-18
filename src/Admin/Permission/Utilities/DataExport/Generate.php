<?php

namespace Nails\Admin\Admin\Permission\DataExport;

use Nails\Admin\Interfaces\Permission;

class Generate implements Permission
{
    public function label(): string
    {
        return 'Can generate data exports';
    }

    public function group(): string
    {
        return 'Utilities';
    }
}
