<?php

namespace Nails\Admin\FindReplace\Exclusion;

use Nails\FindReplace\Interfaces\Exclusion;

class Package implements Exclusion
{
    public function hidden(): array
    {
        return [
            NAILS_DB_PREFIX . 'admin_session',
            NAILS_DB_PREFIX . 'admin_changelog',
            NAILS_DB_PREFIX . 'admin_export',
            NAILS_DB_PREFIX . 'user_meta_admin.nav_state',
        ];
    }

    public function readOnly(): array
    {
        return [
            NAILS_DB_PREFIX . 'admin_help_video.vimeo_id',
            NAILS_DB_PREFIX . 'admin_note.item_model',
            NAILS_DB_PREFIX . 'admin_dashboard_widget.config',
        ];
    }
}
