<?php

/**
 * Migration:  16
 * Created:    30/07/2026
 */

namespace Nails\Admin\Database\Migration;

use Nails\Admin\Service\DataExport;
use Nails\Common\Traits;
use Nails\Common\Interfaces;
use Nails\Config;

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
        $ttl = Config::get('ADMIN_DATA_EXPORT_RETENTION', DataExport::RETENTION_PERIOD);

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
