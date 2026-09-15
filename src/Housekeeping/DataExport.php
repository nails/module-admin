<?php

namespace Nails\Admin\Housekeeping;

use DateInterval;
use Nails\Admin\Constants;
use Nails\Admin\Service\DataExport as DataExportService;
use Nails\Common\Model\Base as ModelBase;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Routine\Context;
use Nails\Housekeeping\Routine\Result;
use Nails\Housekeeping\Traits\DeletesModelRows;

class DataExport extends Base
{
    use DeletesModelRows {
        execute as deleteModelRows;
    }

    const LABEL           = 'Admin data export';
    const DESCRIPTION     = 'Deletes data exports older than ADMIN_DATA_EXPORT_RETENTION and their CDN download objects';
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
        $iRetention = $this->retentionSeconds();
        if ($iRetention < 1) {
            return [['id' => 0]];
        }

        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        $oNow->sub(new DateInterval('PT' . $iRetention . 'S'));

        return [
            [$this->model()->getColumnModified() . ' <', $oNow->format('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return string[]
     */
    protected function auditColumns(): array
    {
        return ['id', 'download_id', 'modified'];
    }

    public function execute(Context $oContext): Result
    {
        $iRetention = $this->retentionSeconds();
        if ($iRetention < 1) {
            $oContext
                ->writeln('Data export cleanup disabled')
                ->log('DISABLED ADMIN_DATA_EXPORT_RETENTION=0');

            return Result::ok(0, 'Data export cleanup disabled');
        }

        $oContext->writeln('Retention policy: <info>' . $iRetention . ' seconds</info>');

        return $this->deleteModelRows($oContext);
    }

    protected function retentionSeconds(): int
    {
        /** @var DataExportService $oExportService */
        $oExportService = Factory::service('DataExport', Constants::MODULE_SLUG);

        return $oExportService->getRetentionPeriod();
    }
}
