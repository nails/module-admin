<?php

/**
 * This class provides a number of reuseable static methods which Admin Controllers can make use of
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Helper
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

use Nails\Admin\Factory\Helper\DynamicTable;
use Nails\Admin\Factory\IndexFilter;
use Nails\Admin\Service\Controller;
use Nails\Auth;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ViewNotFoundException;
use Nails\Common\Service\Input;
use Nails\Common\Service\Output;
use Nails\Common\Service\View;
use Nails\Config;
use Nails\Factory;
use ReflectionException;
use stdClass;

/**
 * Class Helper
 *
 * @package Nails\Admin
 */
class Helper
{
    protected static array $aHeaderButtons = [];
    protected static array $aModals        = [];

    // --------------------------------------------------------------------------

    public static function logo(): string
    {
        $sUrl = static::logoUrl();
        return $sUrl ? img($sUrl) : '';
    }

    // --------------------------------------------------------------------------

    public static function logoUrl(): string
    {
        return Config::get('ADMIN_LOGO_URL')
            ? siteUrl(Config::get('ADMIN_LOGO_URL'))
            : '';
    }

    // --------------------------------------------------------------------------

    /**
     * Loads a view in admin taking into account the module being accessed. Passes controller
     * data and optionally loads the header and footer views.
     *
     * @param string $sViewFile      The view to load
     * @param bool   $bLoadStructure Whether or not to include the header and footers in the output
     * @param bool   $bReturnView    Whether to return the view or send it to the Output class
     *
     * @return string|void           String when $bReturnView is true, void otherwise
     * @throws FactoryException
     * @throws ReflectionException
     * @throws ViewNotFoundException
     */
    public static function loadView(string $sViewFile, bool $bLoadStructure = true, bool $bReturnView = false)
    {
        $aData =& getControllerData();

        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var View $oView */
        $oView = Factory::service('View');

        //  Are we in a modal?
        if ($oInput->get('isModal')) {
            if (!isset($aData['headerOverride']) && !isset($aData['isModal'])) {
                $aData['isModal'] = true;
            }
        }

        $aHeaderViews = array_filter([
            $bLoadStructure && !empty($aData['headerOverride']) ? $aData['headerOverride'] : '',
            $bLoadStructure && empty($aData['headerOverride']) ? '_components/structure/header' : '',
        ]);
        $sHeaderView  = reset($aHeaderViews);

        $aFooterViews = array_filter([
            $bLoadStructure && !empty($aData['footerOverride']) ? $aData['footerOverride'] : '',
            $bLoadStructure && empty($aData['footerOverride']) ? '_components/structure/footer' : '',
        ]);
        $sFooterView  = reset($aFooterViews);

        if ($bReturnView) {
            $sOut = $oView->load($sHeaderView, $aData, true);
            $sOut .= static::loadInlineView($sViewFile, $aData, true);
            $sOut .= $oView->load($sFooterView, $aData, true);
            return $sOut;

        } else {
            $oView->load($sHeaderView, $aData);
            static::loadInlineView($sViewFile, $aData);
            $oView->load($sFooterView, $aData);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a CSV and sends to the browser, if a filename is given then it's
     * sent as a download
     *
     * @param mixed  $mData      The data to render, either an array or a DB query object
     * @param string $sFilename  The filename to give the file if downloading
     * @param bool   $bHeaderRow The first element in the $mData resultset is a header row
     *
     * @throws FactoryException
     * @throws ViewNotFoundException
     * @throws NailsException
     */
    public static function loadCsv(array|\CI_DB_mysqli_result $mData, string $sFilename = '', bool $bHeaderRow = true): void
    {
        //  If filename has been specified then set some additional headers
        if (!empty($sFilename)) {

            /** @var Input $oInput */
            $oInput = Factory::service('Input');
            /** @var Output $oOutput */
            $oOutput = Factory::service('Output');

            //  Common headers
            $oOutput
                ->setContentType('text/csv')
                ->setHeader('Content-Disposition: attachment; filename="' . $sFilename . '"')
                ->setHeader('Expires: 0')
                ->setHeader("Content-Transfer-Encoding: binary");

            //  Handle IE, classic.
            $userAgent = $oInput->server('HTTP_USER_AGENT');

            if (str_contains($userAgent, "MSIE")) {
                $oOutput
                    ->setHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0')
                    ->setHeader('Pragma: public');

            } else {
                $oOutput->setHeader('Pragma: no-cache');
            }
        }

        //  Not using self::loadInlineView() as this may be called from many contexts
        /** @var View $oView */
        $oView = Factory::service('View');
        if (is_array($mData)) {
            $oView->load('admin/_components/csv/array', ['data' => $mData, 'header' => $bHeaderRow]);

        } elseif (get_class($mData) == 'CI_DB_mysqli_result') {
            $oView->load('admin/_components/csv/dbResult', ['data' => $mData, 'header' => $bHeaderRow]);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load a single view taking into account the module being accessed.
     *
     * @param string $sViewFile   The view to load
     * @param array  $aViewData   The data to pass to the view
     * @param bool   $bReturnView Whether to return the view or send it to the Output class
     *
     * @return string|void        String when $bReturnView is true, void otherwise
     * @throws FactoryException
     * @throws ViewNotFoundException
     * @throws ReflectionException
     */
    public static function loadInlineView(string $sViewFile, array $aViewData = [], bool $bReturnView = false)
    {
        /** @var View $oView */
        $oView = Factory::service('View');
        /** @var Controller $oControllerService */
        $oControllerService = Factory::service('Controller', Constants::MODULE_SLUG);

        $oController = $oControllerService->getRoute();
        $aViewPaths  = $oControllerService->getViewPathsForController($oController);

        foreach ($aViewPaths as $sPath) {
            try {

                return $oView->load($sPath . $sViewFile, $aViewData, $bReturnView);

            } catch (ViewNotFoundException $e) {
            }
        }

        throw new ViewNotFoundException(
            sprintf(
                'Could not resolve view "%s" in any of the following paths: %s',
                $sViewFile,
                implode(', ', $aViewPaths)
            )
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "search" component
     *
     * @param stdClass $oSearchObj  An object as created by self::searchObject();
     * @param bool     $bReturnView Whether to return the view to the caller, or output to the browser
     *
     * @return string|void          String when $bReturnView is true, void otherwise
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function loadSearch(stdClass $oSearchObj, bool $bReturnView = true)
    {
        $aData = [
            'searchable'     => $oSearchObj->searchable ?? true,
            'sortColumns'    => $oSearchObj->sortColumns ?? [],
            'sortOn'         => $oSearchObj->sortOn ?? null,
            'sortOrder'      => $oSearchObj->sortOrder ?? null,
            'perPage'        => $oSearchObj->perPage ?? 50,
            'keywords'       => $oSearchObj->keywords ?? '',
            'checkboxFilter' => $oSearchObj->checkboxFilter ?? [],
            'dropdownFilter' => $oSearchObj->dropdownFilter ?? [],
        ];

        //  Not using self::loadInlineView() as this may be called from many contexts
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load('admin/_components/search', $aData, $bReturnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadSearch()
     *
     * @param bool   $bSearchable     Whether the result set is keyword searchable
     * @param array  $aSortColumns    An array of columns to sort results by
     * @param string $sSortOn         The column to sort on
     * @param string $sSortOrder      The order to sort results in
     * @param int    $iPerPage        The number of results to show per page
     * @param string $sKeywords       Keywords to apply to the search result
     * @param array  $aCheckboxFilter An array of filters to filter the results by, presented as checkboxes
     * @param array  $aDropdownFilter An array of filters to filter the results by, presented as a dropdown
     */
    public static function searchObject(
        bool $bSearchable,
        array $aSortColumns,
        string $sSortOn,
        string $sSortOrder,
        int $iPerPage,
        string $sKeywords = '',
        array $aCheckboxFilter = [],
        array $aDropdownFilter = []
    ): stdClass {
        return (object) [
            'searchable'     => $bSearchable,
            'sortColumns'    => $aSortColumns,
            'sortOn'         => $sSortOn,
            'sortOrder'      => $sSortOrder,
            'perPage'        => $iPerPage,
            'keywords'       => $sKeywords,
            'checkboxFilter' => $aCheckboxFilter,
            'dropdownFilter' => $aDropdownFilter,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::searchObject()'s
     * $checkboxFilter and $dropdownFilter parameters
     *
     * @param string $sColumn  The name of the column to filter on, leave blank if you do not wish to use Nails's automatic filtering
     * @param string $sLabel   The label to give the filter group
     * @param array  $aOptions An array of options for the dropdown, either key => value pairs or a 3 element array: 0 = label, 1 = value, 2 = default check status
     *
     * @throws FactoryException
     * @deprecated
     */
    public static function searchFilterObject(string $sColumn, string $sLabel, array $aOptions): IndexFilter
    {
        //  @todo (Pablo - 2018-04-10) - Remove this helper and use factories directly
        /** @var IndexFilter $oFilter */
        $oFilter = Factory::factory('IndexFilter', Constants::MODULE_SLUG);
        $oFilter
            ->setLabel($sLabel)
            ->setColumn($sColumn);

        foreach ($aOptions as $sIndex => $mOption) {

            if (is_array($mOption)) {
                $sLabel   = getFromArray(0, $mOption);
                $mValue   = getFromArray(1, $mOption);
                $bChecked = getFromArray(2, $mOption, false);
                $bQuery   = getFromArray(3, $mOption, false);
            } else {
                $sLabel   = $mOption;
                $mValue   = $sIndex;
                $bChecked = false;
                $bQuery   = false;
            }

            $oFilter->addOption($sLabel, $mValue, $bChecked, $bQuery);
        }

        return $oFilter;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object which is an option for self::searchFilterObject()
     *
     * @param string $sLabel   The label to give the option
     * @param string $sValue   The value to give the option (filters self::searchFilterObject's $sColumn parameter)
     * @param bool   $bChecked Whether the value is checked by default
     * @param bool   $bQuery   Whether the supplied value is an SQL query
     *
     * @deprecated
     */
    public static function searchFilterObjectOption(string $sLabel = '', string $sValue = '', bool $bChecked = false, bool $bQuery = false): stdClass
    {
        return (object) [
            'label'   => $sLabel,
            'value'   => $sValue,
            'checked' => $bChecked,
            'query'   => $bQuery,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a value from a filter object at a specific key
     *
     * @param stdClass $oFilterObj The filter object to search
     * @param int      $iKey       The key to inspect
     *
     * @return mixed               Mixed on success, null on failure
     */
    public static function searchFilterGetValueAtKey(stdClass $oFilterObj, int $iKey): mixed
    {
        return $oFilterObj->options[$iKey]->value ?? null;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the admin "pagination" component
     *
     * @param stdClass $oPaginationObject An object as created by self::paginationObject();
     * @param bool     $bReturnView       Whether to return the view to the caller, or output to the browser
     *
     * @return string|void                String when $bReturnView is true, void otherwise
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function loadPagination(stdClass $oPaginationObject, bool $bReturnView = true)
    {
        $aData = [
            'page'      => $oPaginationObject->page ?? null,
            'perPage'   => $oPaginationObject->perPage ?? null,
            'totalRows' => $oPaginationObject->totalRows ?? null,
        ];

        //  Not using self::loadInlineView() as this may be called from many contexts
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load('admin/_components/pagination', $aData, $bReturnView);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a standard object designed for use with self::loadPagination();
     *
     * @param int $iPage      The current page number
     * @param int $iPerPage   The number of results per page
     * @param int $iTotalRows The total number of results in the result set
     */
    public static function paginationObject(int $iPage, int $iPerPage, int $iTotalRows): stdClass
    {
        return (object) [
            'page'      => $iPage,
            'perPage'   => $iPerPage,
            'totalRows' => $iTotalRows,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Automatically decides what type of cell to load
     *
     * @param mixed  $mValue          The value of the cell
     * @param string $sCellClass      Any classes to add to the cell
     * @param string $sCellAdditional Any additional HTML to add to the cell (after the value)
     *
     * @throws FactoryException
     */
    public static function loadCellAuto(mixed $mValue, string $sCellClass = '', string $sCellAdditional = ''): string
    {
        //  @todo - handle more field types
        if ($mValue instanceof Auth\Resource\User) {
            return Helper::loadUserCell($mValue);

        } elseif (is_bool($mValue)) {
            return Helper::loadBoolCell($mValue);

        } elseif (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', (string) $mValue)) {
            return Helper::loadDateTimeCell($mValue);

        } elseif (preg_match('/^\d\d\d\d-\d\d-\d\d$/', (string) $mValue)) {
            return Helper::loadDateCell($mValue);

        } else {
            $mValue = $mValue ?: '<span class="text-muted">&mdash;</span>';
            return '<td class="' . $sCellClass . '">' . $mValue . $sCellAdditional . '</td>';
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "user" table cell component
     *
     * @param null|Auth\Resource\User|int|string $mUser The user object or the User's ID/email/username
     *
     * @throws FactoryException
     * @throws ViewNotFoundException
     * @throws ModelException
     */
    public static function loadUserCell(null|Auth\Resource\User|int|string $mUser): string
    {
        if ($mUser instanceof Auth\Resource\User) {
            $oUser = $mUser;

        } elseif (is_numeric($mUser)) {

            /** @var Auth\Model\User $oUserModel */
            $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);
            $oUser      = $oUserModel->getById($mUser);

        } elseif (is_string($mUser)) {

            /** @var Auth\Model\User $oUserModel */
            $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);
            $oUser      = $oUserModel->getByEmail($mUser);

            if (empty($oUser)) {
                $oUser = $oUserModel->getByUsername($mUser);
            }

        } else {
            $oUser = $mUser;
        }

        if (empty($oUser)) {
            return '<td class="no-data">&mdash;</td>';
        }

        $aUser = [
            'id'          => $oUser->id ?? null,
            'profile_img' => $oUser->profile_img ?? null,
            'gender'      => $oUser->gender ?? null,
            'first_name'  => $oUser->first_name ?? null,
            'last_name'   => $oUser->last_name ?? null,
            'email'       => $oUser->email ?? null,
            'group'       => $oUser->group_name ?? null,
        ];

        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load('admin/_components/table-cell-user', $aUser, true);
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "date" table cell component
     *
     * @param string|null $sDate   The date to render
     * @param string      $sNoData What to render if the date is invalid or empty
     *
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function loadDateCell(?string $sDate, string $sNoData = '&mdash;'): string
    {
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load(
            'admin/_components/table-cell-date',
            [
                'date'   => $sDate,
                'noData' => $sNoData,
            ],
            true
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "dateTime" table cell component
     *
     * @param string|null $sDateTime The dateTime to render
     * @param string      $sNoData   What to render if the datetime is invalid or empty
     *
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function loadDateTimeCell(?string $sDateTime, string $sNoData = '&mdash;'): string
    {
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load(
            'admin/_components/table-cell-datetime',
            [
                'dateTime' => $sDateTime,
                'noData'   => $sNoData,
            ],
            true
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Load the admin "boolean" table cell component
     *
     * @param mixed       $value     The value to 'truthy' test
     * @param string|null $sDateTime A datetime to show (for truthy values only)
     *
     * @return string
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function loadBoolCell(mixed $value, ?string $sDateTime = null): string
    {
        /** @var View $oView */
        $oView = Factory::service('View');
        return $oView->load(
            'admin/_components/table-cell-boolean',
            [
                'value'    => $value,
                'dateTime' => $sDateTime,
            ],
            true
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a button to Admin's header area
     *
     * @param string[]|string $mUrl          The button's URL
     * @param string          $sLabel        The button's label
     * @param string|null     $sContext      The button's context
     * @param string|null     $sConfirmTitle If a confirmation is required, the title to use
     * @param string|null     $sConfirmBody  If a confirmation is required, the body to use
     * @param string|null     $sTarget       The button's target attribute
     */
    public static function addHeaderButton(
        array|string $mUrl,
        string $sLabel,
        ?string $sContext = null,
        ?string $sConfirmTitle = null,
        ?string $sConfirmBody = null,
        ?string $sTarget = null
    ): void {
        $sContext = empty($sContext) ? 'primary' : $sContext;

        self::$aHeaderButtons[] = [
            'url'          => $mUrl,
            'label'        => $sLabel,
            'context'      => $sContext,
            'confirmTitle' => $sConfirmTitle,
            'confirmBody'  => $sConfirmBody,
            'target'       => $sTarget,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the admin header buttons
     *
     * @return array[]
     */
    public static function getHeaderButtons(): array
    {
        return self::$aHeaderButtons;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a dynamic table
     *
     * @param string $sKey        The key to give items in the table
     * @param array  $aFields     The fields to render
     * @param array  $aData       Data to populate the table with
     * @param bool   $bIsSortable Whether the table is sortable, or not
     *
     * @return DynamicTable
     * @throws FactoryException
     */
    public static function dynamicTable(
        string $sKey = '',
        array $aFields = [],
        array $aData = [],
        bool $bIsSortable = true
    ): DynamicTable {

        return Factory::factory('HelperDynamicTable', Constants::MODULE_SLUG)
            ->setKey($sKey)
            ->setFields($aFields)
            ->setData($aData)
            ->setIsSortable($bIsSortable);
    }

    // --------------------------------------------------------------------------

    /**
     * Convenience method for generating tabbed views
     *
     * @param array  $aTabs  The tab config ['label' => '', 'content' => 'string|callable()']
     * @param string $sGroup The group name, useful if more than one tab group appears on a page
     *
     * @return string
     * @throws FactoryException
     */
    public static function tabs(array $aTabs = [], string $sGroup = ''): string
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $i      = 0;
        $sGroup = $sGroup ? 'tab-group-' . $sGroup : 'tab-group';

        foreach ($aTabs as &$aTab) {

            $aTab['label']   = getFromArray('label', $aTab);
            $aTab['slug']    = url_title($aTab['label'], '-', true);
            $aTab['content'] = getFromArray('content', $aTab);

            if ($oInput->post($sGroup) == 'tab-' . $aTab['slug']) {
                $aTab['active'] = 'active';
            } elseif ($i === 0 && !$oInput->post($sGroup)) {
                $aTab['active'] = 'active';
            } else {
                $aTab['active'] = '';
            }

            $i++;
        }

        ob_start();
        ?>
        <input type="hidden" data-tabgroup="<?=$sGroup?>" name="<?=$sGroup?>" value="<?=set_value($sGroup)?>" />
        <ul class="tabs" data-tabgroup="<?=$sGroup?>" data-active-tab-input="#<?=$sGroup?>">
            <?php
            foreach ($aTabs as &$aTab) {
                ?>
                <li class="tab <?=$aTab['active']?>">
                    <a href="#" data-tab="tab-<?=$aTab['slug']?>">
                        <?=$aTab['label']?>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>
        <section class="tabs" data-tabgroup="<?=$sGroup?>">
            <?php
            foreach ($aTabs as &$aTab) {
                ?>
                <div class="tab-page tab-<?=$aTab['slug']?> <?=$aTab['active']?> fieldset">
                    <?php
                    if (is_callable($aTab['content'])) {
                        echo $aTab['content']();
                    } else {
                        echo $aTab['content'];
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </section>
        <?php
        return ob_get_clean();
    }

    // --------------------------------------------------------------------------

    /**
     * Registers a new modal for admin
     *
     * @param string $sTitle
     * @param string $sBody
     * @param bool   $bIsOpen
     */
    public static function addModal(string $sTitle, string $sBody, bool $bIsOpen = true): void
    {
        static::$aModals[] = (object) [
            'title' => $sTitle,
            'body'  => $sBody,
            'open'  => $bIsOpen,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns registered modals
     *
     * @return stdClass[]
     */
    public static function getModals(): array
    {
        return static::$aModals;
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the floating control bar
     *
     * @param array $aConfig The bar config
     *
     * @return string
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public static function floatingControls(array $aConfig = []): string
    {
        /** @var View $oView */
        $oView = Factory::service('View');

        return $oView
            ->load(
                'admin/_components/floating-controls',
                ['aFloatingConfig' => $aConfig],
                true
            );
    }
}
