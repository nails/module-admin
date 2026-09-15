<?php

namespace Nails\Admin\Housekeeping;

use Nails\Admin\Constants;
use Nails\Common\Model\Base as ModelBase;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Traits\DeletesModelRows;

class Sessions extends Base
{
    use DeletesModelRows;

    const LABEL           = 'Admin sessions';
    const DESCRIPTION     = 'Deletes admin sessions whose heartbeat is older than one hour';
    const CRON_EXPRESSION = '*/5 * * * *';

    protected function model(): ModelBase
    {
        return Factory::model('Session', Constants::MODULE_SLUG);
    }

    /**
     * @return array<int, mixed>
     */
    protected function where(): array
    {
        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        $oNow->sub(new \DateInterval('PT1H'));

        return [
            ['heartbeat <', $oNow->format('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return string[]
     */
    protected function auditColumns(): array
    {
        return ['id', 'user_id', 'heartbeat'];
    }
}
