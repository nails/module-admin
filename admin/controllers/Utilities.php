<?php

/**
 * This class renders Admin Utilities
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin;

use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Admin\Service\DataExport;
use Nails\Common\Events;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Event;
use Nails\Factory;

/**
 * Class Utilities
 *
 * @package Nails\Admin\Admin
 */
class Utilities extends Base
{
    protected $aExportSources;
    protected $aExportFormats;

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     */
    public static function announce(): Nav|array|null
    {
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup->setLabel('Utilities');
        $oNavGroup->setIcon('fa-sliders-h');

        if (userHasPermission('admin:admin:utilities:rewriteRoutes')) {
            $oNavGroup->addAction('Rewrite Routes', 'rewrite_routes');
        }

        if (userHasPermission('admin:admin:utilities:export')) {
            $oNavGroup->addAction('Export Data', 'export');
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     *
     * @return array
     */
    public static function permissions(): array
    {
        $aPermissions                  = parent::permissions();
        $aPermissions['rewriteRoutes'] = 'Can Rewrite Routes';
        $aPermissions['export']        = 'Can Export Data';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Rewrite the app's routes
     *
     * @return void
     */
    public function rewrite_routes()
    {
        if (!userHasPermission('admin:admin:utilities:rewriteRoutes')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oInput = Factory::service('Input');
        if ($oInput->post('go')) {
            try {

                /** @var Event $oEventService */
                $oEventService = Factory::service('Event');
                $oEventService->trigger(Events::ROUTES_UPDATE);

                $this->oUserFeedback->success('Routes rewritten successfully.');

            } catch (\Exception $e) {
                $this->oUserFeedback->success(sprintf(
                    'Routes rewritten successfully. %s',
                    $e->getMessage()
                ));
            }
        }

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('rewriteRoutes');
    }

    // --------------------------------------------------------------------------

    /**
     * Export data
     *
     * @return void
     * @throws FactoryException
     */
    public function export()
    {
        if (!userHasPermission('admin:admin:utilities:export')) {
            unauthorised();
        }

        /** @var DataExport $oDataExport */
        $oDataExport = Factory::service('DataExport', Constants::MODULE_SLUG);
        $aSources    = $oDataExport->getAllSources();
        $aFormats    = $oDataExport->getAllFormats();

        // --------------------------------------------------------------------------

        //  Cron running?
        if (!$oDataExport->isRunning()) {
            $this->oUserFeedback->warning(
                '<strong>The data export cron job is not running</strong>' .
                '<br>The cron job has not been executed within the past 5 minutes.'
            );
        }

        // --------------------------------------------------------------------------

        //  Set view data
        $this->data['page']->title      = 'Export Data';
        $this->data['aSources']         = $aSources;
        $this->data['aFormats']         = $aFormats;
        $this->data['sDefaultFormat']   = $oDataExport::DEFAULT_FORMAT;
        $this->data['iRetentionPeriod'] = $oDataExport->getRetentionPeriod();
        $this->data['iUrlTtl']          = $oDataExport->getUrlTtl();

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('export/index');
    }
}
