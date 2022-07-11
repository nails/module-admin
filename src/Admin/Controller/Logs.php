<?php

/**
 * This class renders the log browsers
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Admin\Model\SiteLog;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Asset;
use Nails\Common\Service\Input;
use Nails\Common\Service\Uri;
use Nails\Factory;

/**
 * Class Logs
 *
 * @package Nails\Admin\Admin
 */
class Logs extends Base
{
    public static function announce()
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup
            ->setLabel('Logs')
            ->setIcon('fa-archive');

        if (userHasPermission('admin:admin:logs:site:browse')) {
            $oNavGroup->addAction('Browse Site Logs', 'site');
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
        return array_merge(
            parent::permissions(),
            [
                'site:browse' => 'Can browse site logs',
            ]
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Route site log pages
     *
     * @throws FactoryException
     */
    public function site()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');

        $sMethod = $oUri->segment(5) ? $oUri->segment(5) : 'index';
        $sMethod = 'site' . underscoreToCamelcase(strtolower($sMethod), false);

        if (method_exists($this, $sMethod)) {
            $this->{$sMethod}();
        } else {
            show404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse site log files
     */
    protected function siteIndex()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Browse Logs';

        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->library('MUSTACHE')
            ->load('admin.logs.site.min.js', Constants::MODULE_SLUG);

        Helper::loadView('site/index');
    }

    // --------------------------------------------------------------------------

    /**
     * View a single log file
     */
    protected function siteView()
    {
        if (!userHasPermission('admin:admin:logs:site:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        /** @var Input $oUri */
        $oInput = Factory::service('Input');
        /** @var SiteLog $oSiteLogModel */
        $oSiteLogModel = Factory::model('SiteLog', Constants::MODULE_SLUG);

        $sFile                     = $oInput->get('log');
        $this->data['page']->title = 'Browse Logs &rsaquo; ' . $sFile;
        $this->data['logs']        = $oSiteLogModel->readLog($sFile);

        if (!$this->data['logs']) {
            show404();
        }

        Helper::loadView('site/view');
    }
}
