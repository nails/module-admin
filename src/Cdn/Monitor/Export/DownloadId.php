<?php

namespace Nails\Admin\Cdn\Monitor\Export;

use Nails\Admin\Constants;
use Nails\Cdn\Cdn\Monitor\ObjectIsInColumn;
use Nails\Common\Model\Base;
use Nails\Factory;

class DownloadId extends ObjectIsInColumn
{
    protected function getModel(): Base
    {
        return Factory::model('Export', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    protected function getColumn(): string
    {
        return 'download_id';
    }
}
