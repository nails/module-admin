<?php

namespace Nails\Admin\Housekeeping;

use Nails\Admin\Constants;
use Nails\Admin\Model\Export;
use Nails\Admin\Resource\Export as ExportResource;
use Nails\Cdn\Service\Cdn;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Database;
use Nails\Factory;
use Nails\Housekeeping\Routine\Base;
use Nails\Housekeeping\Routine\Context;
use Nails\Housekeeping\Routine\Result;

class DataExport extends Base
{
    const LABEL           = 'Admin data export';
    const DESCRIPTION     = 'Deletes expired data exports and their CDN download objects';
    const CRON_EXPRESSION = '*/15 * * * *';

    public function execute(Context $oContext): Result
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \DateTime $oNow */
        $oNow = Factory::factory('DateTime');
        /** @var Export $oModel */
        $oModel = Factory::model('Export', Constants::MODULE_SLUG);
        /** @var Cdn $oCdn */
        $oCdn = Factory::service('Cdn', \Nails\Cdn\Constants::MODULE_SLUG);

        $aWhere     = [['expires <', $oNow->format('Y-m-d H:i:s')]];
        $iBatchSize = 200;
        $iProcessed = 0;
        $iFailed    = 0;
        $iPage      = 1;

        $oContext
            ->writeln('Time now is <comment>' . $oNow->format('Y-m-d H:i:s') . '</comment>')
            ->log(sprintf(
                'TABLE %s batch_size=%d dry_run=%s',
                $oModel->getTableName(),
                $iBatchSize,
                $oContext->isDryRun() ? 'true' : 'false'
            ));

        while (true) {
            /** @var ExportResource[] $aRows */
            $aRows = $oModel->getAll($iPage, $iBatchSize, [
                'where' => $aWhere,
                'sort'  => [['id', 'asc']],
            ]);

            if (empty($aRows)) {
                break;
            }

            foreach ($aRows as $oExport) {
                $sAudit = sprintf(
                    'id=%d download_id=%s expires=%s',
                    (int) $oExport->id,
                    $oExport->download_id === null || $oExport->download_id === '' ? 'null' : (string) $oExport->download_id,
                    $this->stringifyDate($oExport->expires)
                );

                $oContext
                    ->log('DELETE ' . $sAudit)
                    ->writeln(' ↳ ' . $sAudit);

                if ($oContext->isDryRun()) {
                    $iProcessed++;
                    continue;
                }

                try {
                    $oDb->transaction()->start();
                    $oModel->delete($oExport->id);

                    if (!empty($oExport->download_id)) {
                        if (!$oCdn->objectDestroy((int) $oExport->download_id)) {
                            throw new NailsException(
                                'Failed to delete object. ' . $oCdn->lastError()
                            );
                        }
                    }

                    $oDb->transaction()->commit();
                    $iProcessed++;

                } catch (\Exception $e) {
                    $oDb->transaction()->rollback();
                    $iFailed++;
                    $oContext
                        ->log('ERROR id=' . $oExport->id . ' ' . $e->getMessage())
                        ->writeln('<error>' . $e->getMessage() . '</error>');
                }
            }

            if ($oContext->isDryRun()) {
                $iPage++;
            }
        }

        if ($iFailed > 0) {
            return Result::fail(
                'Failed to clean ' . $iFailed . ' export(s)',
                $iProcessed,
                $iFailed
            );
        }

        return Result::ok($iProcessed);
    }

    protected function stringifyDate(mixed $mValue): string
    {
        if ($mValue === null) {
            return 'null';
        }

        if ($mValue instanceof \DateTimeInterface) {
            return $mValue->format('Y-m-d H:i:s');
        }

        return (string) $mValue;
    }
}
