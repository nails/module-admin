<?php

/**
 * Migration:  16
 * Created:    30/07/2026
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Admin\Permission;
use Nails\Admin\Constants;
use Nails\Admin\Service\DataExport;
use Nails\Common\Traits;
use Nails\Common\Interfaces;
use Nails\Factory;

class Migration16 implements Interfaces\Database\Migration
{
    use Traits\Database\Migration;

    // --------------------------------------------------------------------------

    /**
     * Execute the migration
     */
    public function execute(): void
    {
        //  Explicit expiry column
        $this->query(
            <<<EOT
            ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                ADD COLUMN `expires` datetime NULL AFTER `download_id`;
            EOT
        );

        //  Set according to default retention rules
        /** @var DataExport $service */
        $service = Factory::service('DataExport', Constants::MODULE_SLUG);
        $ttl     = $service->getRetentionPeriod();

        $this->query(
            <<<EOT
            UPDATE `{{NAILS_DB_PREFIX}}admin_export`
                SET `expires` = DATE_ADD(`created`, INTERVAL $ttl SECOND);
            EOT
        );

        //  Make the column not null
        $this->query(
            <<<EOT
            ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                CHANGE `expires` `expires` datetime NOT NULL;
            EOT
        );
    }
}
