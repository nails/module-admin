<?php

namespace Tests\FindReplace\Exclusion;

use Nails\Admin\FindReplace\Exclusion\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('NAILS_DB_PREFIX')) {
            define('NAILS_DB_PREFIX', 'nails_');
        }
    }

    public function testHidden(): void
    {
        self::assertSame(
            [
                NAILS_DB_PREFIX . 'admin_session',
                NAILS_DB_PREFIX . 'admin_changelog',
                NAILS_DB_PREFIX . 'admin_export',
                NAILS_DB_PREFIX . 'admin_dashboard_widget',
                NAILS_DB_PREFIX . 'admin_note',
                NAILS_DB_PREFIX . 'user_meta_admin.nav_state',
            ],
            (new Package())->hidden()
        );
    }

    public function testReadOnly(): void
    {
        self::assertSame(
            [
                NAILS_DB_PREFIX . 'admin_help_video.vimeo_id',
            ],
            (new Package())->readOnly()
        );
    }
}
