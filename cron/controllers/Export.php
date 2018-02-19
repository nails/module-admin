<?php

/**
 * Cron controller responsible for generating admin exports
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 */

namespace Nails\Cron\Admin;

use Nails\Cron\Controller\Base;
use Nails\Factory;

class Export extends Base
{
    public function index()
    {
        $this->writeLog('Generating exports');

        $oService  = Factory::service('DataExport', 'nailsapp/module-admin');
        $oModel    = Factory::model('Export', 'nailsapp/module-admin');
        $aRequests = $oModel->getAll(['where' => [['status', $oModel::STATUS_PENDING]]]);

        if (!empty($aRequests)) {

            $this->writeLog(count($aRequests) . ' requests');
            $this->writeLog('Marking as "RUNNING"');
            $oModel->setBatchStatus($aRequests, $oModel::STATUS_PENDING);

            //  Group identical requests
            $aGroupedRequests = [];
            foreach ($aRequests as $oRequest) {
                $aHash = [$oRequest->source, $oRequest->format, $oRequest->options];
                $sHash = md5(json_encode($aHash));
                if (array_key_exists($sHash, $aGroupedRequests)) {
                    $aGroupedRequests[$sHash]->recipients[] = $oRequest->created_by;
                    $aGroupedRequests[$sHash]->ids[]        = $oRequest->id;
                } else {
                    $aGroupedRequests[$sHash] = (object) [
                        'source'     => $oRequest->source,
                        'format'     => $oRequest->format,
                        'options'    => json_decode($oRequest->options),
                        'recipients' => [$oRequest->created_by],
                        'ids'        => [$oRequest->id],
                    ];
                }
            }

            //  Process each request
            foreach ($aGroupedRequests as $oRequest) {
                try {
                    $oModel->setBatchDownloadId(
                        $oRequest->ids,
                        $oService->export($oRequest->source, $oRequest->format)
                    );
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_COMPLETE);
                } catch (\Exception $e) {
                    $this->writeLog('Exception: ' . $e->getMessage());
                    $oModel->setBatchStatus($oRequest->ids, $oModel::STATUS_FAILED, $e->getMessage());
                }
            }

        } else {
            $this->writeLog('Nothing to do');
        }

        $this->writeLog('Complete');
    }
}
