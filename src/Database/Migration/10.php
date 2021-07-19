<?php

/**
 * Migration:   10
 * Started:     7/11/2018
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nails\ModuleAdmin;

use Nails\Common\Console\Migrate\Base;

class Migration10 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}admin_handbook` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `slug` varchar(150) DEFAULT NULL,
                `parent_id` int(11) unsigned DEFAULT NULL,
                `label` varchar(150) DEFAULT NULL,
                `body` text,
                `order` int(11) DEFAULT '0',
                `breadcrumbs` text,
                `is_deleted` tinyint(1) DEFAULT '0',
                `created` datetime NOT NULL,
                `created_by` int(10) unsigned DEFAULT NULL,
                `modified` datetime NOT NULL,
                `modified_by` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `created_by` (`created_by`),
                KEY `modified_by` (`modified_by`),
                KEY `parent_id` (`parent_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_handbook_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_handbook_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}admin_handbook_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `{{NAILS_DB_PREFIX}}admin_handbook` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }
}
