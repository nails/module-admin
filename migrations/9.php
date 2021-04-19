<?php

/**
 * Migration:  9
 * Started:    19/04/2021
 *
 * @package    Nails
 * @subpackage module-admin
 * @category   Database Migration
 * @author     Nails Dev Team
 */

namespace Nails\Database\Migration\Nails\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration9 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query('
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_session` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int unsigned NOT NULL,
                `url` varchar(500) DEFAULT NULL,
                `last_pageload` datetime DEFAULT NULL,
                `last_heartbeat` datetime DEFAULT NULL,
                `last_interaction` datetime DEFAULT NULL,
                `last_seen` datetime DEFAULT NULL,
                `created` datetime NOT NULL,
                `created_by` int unsigned DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                `modified_by` int unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `created_by` (`created_by`),
                KEY `modified_by` (`modified_by`),
                KEY `{{NAILS_DB_PREFIX}}admin_session_ibfk_3` (`user_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_session_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE CASCADE,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_session_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE CASCADE,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_session_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');
    }
}
