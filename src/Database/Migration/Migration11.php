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
    }
}
