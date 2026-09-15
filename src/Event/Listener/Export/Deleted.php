<?php

namespace Nails\Admin\Event\Listener\Export;

use Nails\Admin\Constants;
use Nails\Admin\Model\Export;
use Nails\Admin\Resource\Export as ExportResource;
use Nails\Cdn;
use Nails\Cdn\Service\Cdn as CdnService;
use Nails\Common\Events\Subscription;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Logger;
use Nails\Factory;
use Throwable;

/**
 * Destroys the export's CDN download when the row is deleted.
 *
 * Identical pending requests share one `download_id`, so the file is only
 * destroyed when no other `admin_export` row still points at it. Failures
 * are logged rather than thrown: the row is already gone, and
 * `objectDestroy()` is not transactional.
 */
class Deleted extends Subscription
{
    /**
     * @throws FactoryException
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $oModel = Factory::model('Export', Constants::MODULE_SLUG);
        $this
            ->setEvent(Export::EVENT_DELETED)
            ->setNamespace($oModel::getEventNamespace())
            ->setCallback([$this, 'execute']);
    }

    public function execute(int|string $iId, ?ExportResource $oItem, Export $oModel): void
    {
        $iDownloadId = (int) ($oItem->download_id ?? 0);
        if ($iDownloadId < 1) {
            return;
        }

        try {
            if ($oModel->countAll([
                'where' => [
                    ['download_id', $iDownloadId],
                ],
            ]) > 0) {
                return;
            }

            $oCdn = $this->cdn();
            if (!$oCdn->objectDestroy($iDownloadId)) {
                $this->logFailure($iId, $iDownloadId, $oCdn->lastError() ?: 'Unknown error');
            }
        } catch (Throwable $e) {
            $this->logFailure($iId, $iDownloadId, $e->getMessage());
        }
    }

    /**
     * @throws FactoryException
     */
    protected function cdn(): CdnService
    {
        /** @var CdnService $oCdn */
        $oCdn = Factory::service('Cdn', Cdn\Constants::MODULE_SLUG);
        return $oCdn;
    }

    /**
     * @throws FactoryException
     */
    protected function logger(): Logger
    {
        /** @var Logger $oLogger */
        $oLogger = Factory::service('Logger');
        return $oLogger;
    }

    protected function logFailure(int|string $iExportId, int $iDownloadId, string $sError): void
    {
        $this->logger()->error(sprintf(
            'Failed to destroy CDN object #%s belonging to admin export #%s; %s',
            $iDownloadId,
            $iExportId,
            $sError
        ));
    }
}
