<?php

namespace Nails\Admin\Housekeeping;

use Nails\Admin\Constants;
use Nails\Common\Model\Base as ModelBase;
use Nails\Config;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Traits\DeletesModelRows;

class Sessions extends Base
{
    use DeletesModelRows;

    const LABEL            = 'Admin sessions';
    const DESCRIPTION      = 'Deletes admin sessions whose heartbeat is older than ADMIN_SESSION_RETENTION seconds';
    const CRON_EXPRESSION  = '*/5 * * * *';
    const CONFIG_RETENTION = 'ADMIN_SESSION_RETENTION';
    const RETENTION        = 3600;

    protected function model(): ModelBase
    {
        return Factory::model('Session', Constants::MODULE_SLUG);
    }

    /**
     * @return array<int, mixed>
     */
    protected function where(): array
    {
        $iSeconds = (int) Config::get(static::CONFIG_RETENTION, static::RETENTION) ?: static::RETENTION;

        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        $oNow->sub(new \DateInterval('PT' . $iSeconds . 'S'));

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
