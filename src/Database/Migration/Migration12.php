<?php

/**
 * Migration:  12
 * Created:    09/08/2022
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Admin\Permission;
use Nails\Common\Traits;
use Nails\Common\Interfaces;

class Migration12 implements Interfaces\Database\Migration
{
    use Traits\Database\Migration;

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
     * Execute the migration
     */
    public function execute(): void
    {
        $oResult = $this->query('SELECT id, acl FROM `{{NAILS_DB_PREFIX}}user_group`');
        while ($row = $oResult->fetchObject()) {

            $acl = json_decode($row->acl) ?? [];

            foreach ($acl as &$old) {

                $old = preg_match('/^admin:admin:settings:/', $old)
                    ? Permission\Settings\Manage::class
                    : self::MAP[$old] ?? $old;
            }

            $acl = array_filter($acl);
            $acl = array_unique($acl);
            $acl = array_values($acl);

            $this
                ->prepare('UPDATE `{{NAILS_DB_PREFIX}}user_group` SET `acl` = :acl WHERE `id` = :id')
                ->execute([
                    ':id'  => $row->id,
                    ':acl' => json_encode($acl),
                ]);
        }
    }
}
