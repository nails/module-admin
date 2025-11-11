<?php

/**
 * Migration:  11
 * Created:    10/05/2022
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Constants;
use Nails\Common\Console\Migrate\Base;

class Migration11 extends Base
{
    /**
     * Execute the migration
     *
     * @return Void
     */
    public function execute()
    {
        $this->query('TRUNCATE `{{NAILS_DB_PREFIX}}user_meta_admin`;');

        /**
         *  Replicating this migration from 10 for applications switching from the `pre-new-admin` branch to `develop`.
         *  `develop`'s version of 10 will not run for apps previously on `pre-new-admin`, and it is safe to run this
         *  query more than once
         */
        $this->query('UPDATE `{{NAILS_DB_PREFIX}}app_setting` SET `grouping` = "' . Constants::MODULE_SLUG . '" WHERE `grouping` = "admin";');
    }
}
