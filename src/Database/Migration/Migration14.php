<?php

/**
 * Migration:  14
 * Created:    19/08/2024
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Admin\Permission;
use Nails\Common\Traits;
use Nails\Common\Interfaces;

class Migration14 implements Interfaces\Database\Migration
{
    use Traits\Database\Migration;

    // --------------------------------------------------------------------------

    /**
     * Execute the migration
     */
    public function execute(): void
    {
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}admin_changelog` DROP `article`;');
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}admin_changelog` SET `operation` = "CREATE" WHERE `operation` = "created";');
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}admin_changelog` SET `operation` = "EDIT" WHERE `operation` = "updated";');
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}admin_changelog` SET `operation` = "DELETE" WHERE `operation` = "deleted";');
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}admin_changelog` SET `operation` = "RESTORE" WHERE `operation` = "restore";');
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}admin_changelog` CHANGE `verb` `operation` ENUM(\'CREATE\',\'EDIT\',\'DELETE\',\'RESTORE\') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');
    }
}
