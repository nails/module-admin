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
use Nails\Admin\Helper;
use Nails\Admin\Service\DataExport;
use Nails\Common\Events;
use Nails\Common\Exception\NailsException;
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
     *
     * @return \stdClass
     */
    public static function announce()
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

        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');

        if ($oInput->post()) {
            try {

                $oFormValidation = Factory::service('FormValidation');
                $oFormValidation->set_rules('source', '', 'required');
                $oFormValidation->set_rules('format', '', 'required');
                $oFormValidation->set_message('required', lang('fv_required'));

                if (!$oFormValidation->run()) {
                    throw new NailsException(lang('fv_there_were_errors'));
                }

                //  Validate source
                $oSelectedSource = $oDataExport->getSourceBySlug($oInput->post('source'));
                if (empty($oSelectedSource)) {
                    throw new NailsException('Invalid data source');
                }

                //  Validate format
                $oSelectedFormat = $oDataExport->getFormatBySlug($oInput->post('format'));
                if (empty($oSelectedFormat)) {
                    throw new NailsException('Invalid data format');
                }

                //  Prepare options
                $aOptions       = [];
                $aPostedOptions = getFromArray($oSelectedSource->slug, (array) $oInput->post('options'));
                foreach ($oSelectedSource->options as $aOption) {
                    $sKey            = getFromArray('key', $aOption);
                    $aOptions[$sKey] = getFromArray($sKey, $aPostedOptions);
                }

                $oDataExportModel = Factory::model('Export', Constants::MODULE_SLUG);
                $aData            = [
                    'source'  => $oSelectedSource->slug,
                    'options' => json_encode($aOptions),
                    'format'  => $oSelectedFormat->slug,
                ];
                if (!$oDataExportModel->create($aData)) {
                    throw new NailsException('Failed to schedule export.');
                }

                $this->oUserFeedback->success('Routes rewritten successfully.');

            } catch (\Exception $e) {
                $this->oUserFeedback->error($e->getMessage());
            }
        }

        // --------------------------------------------------------------------------

        $oModel  = Factory::model('Export', Constants::MODULE_SLUG);
        $aRecent = $oModel->getAll([
            'where' => [[$oModel->getColumnCreatedBy(), activeUser('id')]],
            'sort'  => [[$oModel->getColumnCreated(), 'desc']],
            'limit' => 10,
        ]);

        //  Pretty source labels, format labels, and options
        $aRecent = array_map(function ($oItem) use ($aSources, $aFormats) {

            //  Sources
            foreach ($aSources as $oSource) {
                if ($oSource->slug === $oItem->source) {
                    $oItem->source = $oSource->label;
                }
            }

            if (empty($oItem->source)) {
                $oItem->source = 'Unknown';
            }

            //  Formats
            foreach ($aFormats as $oFormat) {
                if ($oFormat->slug === $oItem->format) {
                    $oItem->format = $oFormat->label;
                }
            }

            if (empty($oItem->format)) {
                $oItem->format = 'Unknown';
            }

            //  Options
            $oOptions = json_decode($oItem->options);
            if ($oOptions) {
                $oItem->options = '<pre>' . json_encode($oOptions, JSON_PRETTY_PRINT) . '</pre>';
            } else {
                $oItem->options = '';
            }

            return $oItem;
        }, $aRecent);

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
        $this->data['aRecent']          = $aRecent;
        $this->data['sDefaultFormat']   = $oDataExport::DEFAULT_FORMAT;
        $this->data['iRetentionPeriod'] = $oDataExport->getRetentionPeriod();
        $this->data['iUrlTtl']          = $oDataExport->getUrlTtl();

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('export/index');
    }
}
