<?php

/**
 * Migration:  12
 * Created:    09/08/2022
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Admin\Permission;
use Nails\Admin\Traits\Database\Migration\PermissionMap;
use Nails\Common\Traits;
use Nails\Common\Interfaces;

/**
 * Class Migration12
 *
 * Repeatable because `feature/pre-new-admin` has no equivalent migration, so an app
 * arriving from that branch resumes above this number and would never run it.
 *
 * @package Nails\Admin\Database\Migration
 */
class Migration12 implements Interfaces\Database\Migration\Repeatable
{
    use Traits\Database\Migration;
    use PermissionMap {
        mapPermission as protected mapPermissionByMap;
    }

    // --------------------------------------------------------------------------

    const MAP = [
        'admin:superuser'                     => Permission\SuperUser::class,
        'admin:admin:changelog:browse'        => Permission\ChangeLog\Browse::class,
        'admin:admin:changelog:create'        => '',
        'admin:admin:changelog:edit'          => '',
        'admin:admin:changelog:delete'        => '',
        'admin:admin:changelog:restore'       => '',
        'admin:admin:changelog:view'          => Permission\ChangeLog\View::class,
        'admin:admin:help:view'               => Permission\Help\View::class,
        'admin:admin:utilities:rewriteroutes' => Permission\Utilities\Routes\Rewrite::class,
        'admin:admin:utilities:export'        => Permission\Utilities\DataExport\Generate::class,
    ];

    // --------------------------------------------------------------------------

    /**
     * Every settings permission collapses into a single one, which the map cannot express
     *
     * @param string $sPermission The permission to translate
     *
     * @return string|null
     */
    protected function mapPermission(string $sPermission): ?string
    {
        return preg_match('/^admin:admin:settings:/', $sPermission)
            ? Permission\Settings\Manage::class
            : $this->mapPermissionByMap($sPermission);
    }
}
