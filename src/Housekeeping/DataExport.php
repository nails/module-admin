<?php

namespace Nails\Admin\Housekeeping;

use Nails\Admin\Constants;
use Nails\Common\Model\Base as ModelBase;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Traits\DeletesModelRows;

class DataExport extends Base
{
    use DeletesModelRows;

    const LABEL           = 'Admin data export';
    const DESCRIPTION     = 'Deletes expired data exports and their CDN download objects';
    const CRON_EXPRESSION = '*/15 * * * *';

    protected function model(): ModelBase
    {
        return Factory::model('Export', Constants::MODULE_SLUG);
    }

    /**
     * @return array<int, mixed>
     */
    protected function where(): array
    {
        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');

        return [
            ['expires <', $oNow->format('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return string[]
     */
    protected function auditColumns(): array
    {
        return ['id', 'download_id', 'expires'];
    }
}
