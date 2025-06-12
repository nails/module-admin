<?php

/**
 * This class renders the admin help section
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
use Nails\Factory;

class Help extends Base
{
    /**
     * Announces this controller's navGroups
     */
    public static function announce(): Nav|array|null
    {
        $oHelpModel = Factory::model('Help', Constants::MODULE_SLUG);

        if (userHasPermission('admin:admin:help:view') && $oHelpModel->countAll()) {
            $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
            $oNavGroup->setLabel('Dashboard');
            $oNavGroup->setIcon('fa-home');
            $oNavGroup->addAction('Help Videos');
        }

        return $oNavGroup ?? null;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions(): array
    {
        $aPermissions = parent::permissions();

        $aPermissions['view'] = 'Can view help videos';

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the admin help pagge
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:admin:help:view')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Page Title
        $this->data['page']->title = 'Help Videos';

        // --------------------------------------------------------------------------

        //  Get data
        $oInput      = Factory::service('Input');
        $oHelpModel  = Factory::model('Help', Constants::MODULE_SLUG);
        $sTableAlias = $oHelpModel->getTableAlias();

        //  Get pagination and search/sort variables
        $iPage      = (int) $oInput->get('page') ?: 0;
        $iPerPage   = (int) $oInput->get('perPage') ?: 50;
        $sSortOn    = $oInput->get('sortOn') ?: $sTableAlias . '.label';
        $sSortOrder = $oInput->get('sortOrder') ?: 'asc';
        $sKeywords  = $oInput->get('keywords') ?: '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $aSortColumns = [
            $sTableAlias . '.label'    => 'Label',
            $sTableAlias . '.duration' => 'Duration',
            $sTableAlias . '.created'  => 'Added',
            $sTableAlias . '.modified' => 'Modified',
        ];

        // --------------------------------------------------------------------------

        //  Define the $aData variable for the queries
        $aData = [
            'sort'     => [
                [$sSortOn, $sSortOrder],
            ],
            'keywords' => $sKeywords,
        ];

        //  Get the items for the page
        $iTotalRows           = $oHelpModel->countAll($aData);
        $this->data['videos'] = $oHelpModel->getAll($iPage, $iPerPage, $aData);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = Helper::searchObject(true, $aSortColumns, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);

        // --------------------------------------------------------------------------

        $oAsset = Factory::service('Asset');
        $oAsset->inline('$(\'a.video-button\').fancybox({ type : \'iframe\' });', 'JS');

        // --------------------------------------------------------------------------

        //  Load views
        Helper::loadView('index');
    }
}
