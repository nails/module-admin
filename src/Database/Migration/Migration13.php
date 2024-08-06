<?php

/**
 * Migration:  13
 * Created:    06/08/2024
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Admin\Permission;
use Nails\Common\Traits;
use Nails\Common\Interfaces;

class Migration13 implements Interfaces\Database\Migration
{
    use Traits\Database\Migration;

    // --------------------------------------------------------------------------

    /**
     * Execute the migration
     */
    public function execute(): void
    {
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}admin_note` CHANGE `model` `item_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';');
    }
}
