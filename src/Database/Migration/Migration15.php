<?php

/**
 * Migration:  15
 * Created:    30/07/2026
 */

namespace Nails\Admin\Database\Migration;

use Nails\Common\Traits;
use Nails\Common\Interfaces;

class Migration15 implements Interfaces\Database\Migration
{
    use Traits\Database\Migration;

    // --------------------------------------------------------------------------

    /**
     * Execute the migration
     */
    public function execute(): void
    {
        if (!$this->columnExists('{{NAILS_DB_PREFIX}}admin_export', 'user_id')) {
            $this->query(
                <<<EOT
                ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                    ADD COLUMN `user_id` int unsigned NULL AFTER `id`;
                EOT
            );
            $this->query(
                <<<EOT
                UPDATE `{{NAILS_DB_PREFIX}}admin_export`
                    SET `user_id` = `created_by`;
                EOT
            );
            $this->query(
                <<<EOT
                ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                    CHANGE `user_id` `user_id` int unsigned NOT NULL;
                EOT
            );
        }

        if (!$this->foreignKeyExists('{{NAILS_DB_PREFIX}}admin_export', 'user_id')) {
            $this->query(
                <<<EOT
                ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                    ADD FOREIGN KEY (`user_id`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE CASCADE;
                EOT
            );
        }

        if (!$this->columnExists('{{NAILS_DB_PREFIX}}admin_export', 'method')) {
            $this->query(
                <<<EOT
                ALTER TABLE `{{NAILS_DB_PREFIX}}admin_export`
                    ADD COLUMN `method` enum('MANUAL','SCHEDULE') NOT NULL DEFAULT 'MANUAL' AFTER `user_id`;
                EOT
            );
        }
    }
}
