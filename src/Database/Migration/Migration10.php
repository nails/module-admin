<?php

/**
 * Migration:  10
 * Created:    03/05/2022
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Constants;
use Nails\Common\Console\Migrate\Base;

class Migration10 extends Base
{
    /**
     * Execute the migration
     *
     * @return Void
     */
    public function execute()
    {
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}app_setting` SET `grouping` = "' . Constants::MODULE_SLUG . '" WHERE `grouping` = "admin";');
    }
}
