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

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Admin\Permission;
use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Admin\Service\DataExport;
use Nails\Common\Events;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Event;
use Nails\Common\Service\Input;
use Nails\Factory;

/**
 * Class Utilities
 *
 * @package Nails\Admin\Admin
 */
class Utilities extends Base
{
    /**
     * @throws FactoryException
     */
    public static function announce(): Nav|array|null
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup
            ->setLabel('Utilities')
            ->setIcon('fa-sliders-h');

        if (userHasPermission(Permission\Utilities\Routes\Rewrite::class)) {
            $oNavGroup->addAction('Rewrite Routes', 'rewrite_routes');
        }

        if (userHasPermission(Permission\Utilities\DataExport\Generate::class)) {
            $oNavGroup->addAction('Export Data', 'export');
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Rewrite the app's routes
     *
     * @return void
     * @throws FactoryException
     */
    public function rewrite_routes()
    {
        if (!userHasPermission(Permission\Utilities\Routes\Rewrite::class)) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        /** @var Input $oInput */
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
        if (!userHasPermission(Permission\Utilities\DataExport\Generate::class)) {
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

        $this
            ->setData('aSources', $aSources)
            ->setData('aFormats', $aFormats)
            ->setData('sDefaultFormat', $oDataExport::DEFAULT_FORMAT)
            ->setData('iRetentionPeriod', $oDataExport->getRetentionPeriod())
            ->setData('iUrlTtl', $oDataExport->getUrlTtl())
            ->setTitles(['Export Data'])
            ->loadView('export/index');
    }
}
