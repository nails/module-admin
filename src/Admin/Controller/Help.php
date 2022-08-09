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

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Admin\Permission;
use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Factory;

class Help extends Base
{
    public static function announce()
    {
        /** @var \Nails\Admin\Model\Help $oHelpModel */
        $oHelpModel = Factory::model('Help', Constants::MODULE_SLUG);

        if (userHasPermission(Permission\Help\View::class) && $oHelpModel->countAll()) {

            /** @var \Nails\Admin\Factory\Nav $oNavGroup */
            $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
            $oNavGroup
                ->setLabel('Dashboard')
                ->setIcon('fa-home')
                ->addAction('Help Videos');

            return $oNavGroup;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the admin help pagge
     *
     * @return void
     */
    public function index()
    {
        if (!userHasPermission(Permission\Help\View::class)) {
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
