<?php

/**
 * Export Model
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Model
 * @author      Nails Dev Team
 */

namespace Nails\Admin\Model;

use Nails\Admin\Constants;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Model\Base;
use Nails\Factory;

class Export extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'admin_export';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'Export';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    /**
     * The various statuses
     */
    const STATUS_PENDING  = 'PENDING';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_FAILED   = 'FAILED';

    // --------------------------------------------------------------------------

    /**
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->hasOne('created_by', 'User', \Nails\Auth\Constants::MODULE_SLUG, 'created_by');
    }

    // --------------------------------------------------------------------------

    /**
     * Updates the status of multiple request items in a batch
     *
     * @param array  $aIds    The requests to update
     * @param string $sStatus The status to set
     * @param string $sError  Any error message to set
     *
     * @throws FactoryException
     * @throws ModelException
     */
    public function setBatchStatus(array $aIds, string $sStatus, string $sError = ''): void
    {
        if (is_object(reset($aIds))) {
            $aIds = arrayExtractProperty($aIds, 'id');
        }
        if (!empty($aIds)) {
            $oDb = Factory::service('Database');
            $oDb->set('status', $sStatus);
            $oDb->set('modified', 'NOW()', false);
            if ($sError) {
                $oDb->set('error', $sError);
            }
            $oDb->where_in('id', $aIds);
            $oDb->update($this->getTableName());
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Updates the download ID of multiple requests
     *
     * @param int[] $aIds        The requests to update
     * @param int   $iDownloadId The download ID
     *
     * @throws FactoryException
     * @throws ModelException
     */
    public function setBatchDownloadId(array $aIds, int $iDownloadId): void
    {
        if (is_object(reset($aIds))) {
            $aIds = arrayExtractProperty($aIds, 'id');
        }
        if (!empty($aIds)) {
            $oDb = Factory::service('Database');
            $oDb->set('download_id', $iDownloadId);
            $oDb->set('modified', 'NOW()', false);
            $oDb->where_in('id', $aIds);
            $oDb->update($this->getTableName());
        }
    }
}
