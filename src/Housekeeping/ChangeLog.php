<?php

namespace Nails\Admin\Housekeeping;

use Nails\Admin\Constants;
use Nails\Common\Model\Base as ModelBase;
use Nails\Config;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Routine\Context;
use Nails\Housekeeping\Routine\Result;
use Nails\Housekeeping\Traits\DeletesModelRows;

class ChangeLog extends Base
{
    use DeletesModelRows {
        execute as deleteModelRows;
    }

    const LABEL           = 'Admin changelog';
    const DESCRIPTION     = 'Deletes admin changelog rows older than ADMIN_CHANGELOG_RETENTION_DAYS';
    const CRON_EXPRESSION = '@daily';

    /**
     * Default retention in days. 0 disables deletion.
     */
    const DEFAULT_RETENTION_DAYS = 730;

    protected function model(): ModelBase
    {
        return Factory::model('ChangeLog', Constants::MODULE_SLUG);
    }

    /**
     * @return array<int, mixed>
     */
    protected function where(): array
    {
        $iDays = $this->retentionDays();
        if ($iDays < 1) {
            return [['id' => 0]];
        }

        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        $oNow->sub(new \DateInterval('P' . $iDays . 'D'));

        return [
            ['created <', $oNow->format('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return string[]
     */
    protected function auditColumns(): array
    {
        return ['id', 'user_id', 'created'];
    }

    protected function optimizeAfter(): bool
    {
        return true;
    }

    public function execute(Context $oContext): Result
    {
        $iDays = $this->retentionDays();
        if ($iDays < 1) {
            $oContext
                ->writeln('Changelog cleanup disabled')
                ->log('DISABLED ADMIN_CHANGELOG_RETENTION_DAYS=0');

            return Result::ok(0, 'Changelog cleanup disabled');
        }

        $oContext->writeln('Retention policy: <info>' . $iDays . ' days</info>');

        return $this->deleteModelRows($oContext);
    }

    protected function retentionDays(): int
    {
        return (int) Config::get('ADMIN_CHANGELOG_RETENTION_DAYS', static::DEFAULT_RETENTION_DAYS);
    }
}
