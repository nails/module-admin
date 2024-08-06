<?php

/**
 * Migration:  10
 * Started:    06/08/2024
 *
 * @package    Nails
 * @subpackage module-admin
 * @category   Database Migration
 * @author     Nails Dev Team
 */

namespace Nails\Admin\Database\Migration;

use Nails\Common\Console\Migrate\Base;

class Migration10 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}admin_note` CHANGE `model` `item_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';');
    }
}
