<?php

/**
 * This class provides basic functionality for standard admin sections which are powered by the model.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Exception\DefaultController\ItemModifiedException;
use Nails\Admin\Factory\DefaultController\Sort\Section;
use Nails\Admin\Factory\IndexFilter;
use Nails\Admin\Factory\IndexFilter\Option;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Admin\Model\ChangeLog;
use Nails\Auth\Model\User;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Factory\Model\Field;
use Nails\Common\Helper\Form;
use Nails\Common\Helper\Inflector;
use Nails\Common\Resource;
use Nails\Common\Service\Database;
use Nails\Common\Service\FormValidation;
use Nails\Common\Service\Input;
use Nails\Common\Service\Locale;
use Nails\Common\Service\Uri;
use Nails\Common\Traits\Model\Copyable;
use Nails\Common\Traits\Model\HasDataColumns;
use Nails\Common\Traits\Model\Localised;
use Nails\Common\Traits\Model\Nestable;
use Nails\Common\Traits\Model\Publishable;
use Nails\Common\Traits\Model\Searchable;
use Nails\Common\Traits\Model\Sortable;
use Nails\Factory;

/**
 * Class DefaultController
 *
 * @package Nails\Admin\Controller
 */
abstract class DefaultController extends Base
{
    /**
     * The model to use for this admin view (and which module provides it)
     */
    const CONFIG_MODEL_NAME     = '';
    const CONFIG_MODEL_PROVIDER = 'app';

    /**
     * The permission string to use when checking permissions; if not provided then no permissions required
     */
    const CONFIG_PERMISSION = '';

    /**
     * The singular and plural name of the item being managed; defaults to the model name
     */
    const CONFIG_TITLE_SINGLE = '';
    const CONFIG_TITLE_PLURAL = '';

    /**
     * Where to display this controller in the admin sidebar; defaults to CONFIG_TITLE_PLURAL
     */
    const CONFIG_SIDEBAR_GROUP = '';

    /**
     * What icon to display in the nav
     */
    const CONFIG_SIDEBAR_ICON = '';

    /**
     * The format for the sidebar link
     */
    const CONFIG_SIDEBAR_FORMAT = 'Manage %s';

    /**
     * Search terms to apply to the entire group
     */
    const CONFIG_SIDEBAR_SEARCH_TERMS = [];

    /**
     * the base URL for this controller
     */
    const CONFIG_BASE_URL = '';

    /**
     * The sorting options to give the user on the index view
     */
    const CONFIG_SORT_OPTIONS = [
        'Label'    => 'label',
        'Created'  => 'created',
        'Modified' => 'modified',
    ];

    /**
     * The default sorting order
     */
    const CONFIG_SORT_DIRECTION = self::SORT_ASCENDING;

    /**
     * The fields to show on the index view;
     *
     * This array forms the basis for the $this->$aConfig['INDEX_FIELDS'] config.
     * You are free to manipulate this after the constructor has been called.
     *
     * If you wish to create any dynamic fields then the element value can be a
     * closure/callable which is passed $oItem as the one and only argument.
     */
    const CONFIG_INDEX_FIELDS = [
        'Label'       => 'label',
        'Modified'    => 'modified',
        'Modified By' => 'modified_by',
    ];

    /**
     * Any additional header buttons to add to the index page.
     */
    const CONFIG_INDEX_HEADER_BUTTONS = [];

    /**
     * Any additional buttons to add to each row on the page.
     * See static::$aConfigIndexRowButtons for details
     */
    const CONFIG_INDEX_ROW_BUTTONS = [];

    /**
     * Specify whether the controller supports item creation
     */
    const CONFIG_CAN_CREATE = true;

    /**
     * Specify whether the controller supports item editing
     */
    const CONFIG_CAN_EDIT = true;

    /**
     * Specify whether the controller supports linking to the item
     */
    const CONFIG_CAN_VIEW = true;

    /**
     * Specify whether the controller supports item deletion
     */
    const CONFIG_CAN_DELETE = true;

    /**
     * Specify whether the controller supports item restoration
     */
    const CONFIG_CAN_RESTORE = true;

    /**
     * Specify whether the controller supports item copying
     */
    const CONFIG_CAN_COPY = true;

    /**
     * Specify whether the controller supports item sorting
     */
    const CONFIG_CAN_SORT = true;

    /**
     * Additional data to pass into the getAll call on the index view
     */
    const CONFIG_INDEX_DATA = [];

    /**
     * The fields on the index view which should be rendered as user cells
     */
    const CONFIG_INDEX_USER_FIELDS = [
        'created_by',
        'modified_by',
        'user_id',
    ];

    /**
     * The fields on the index view which should be rendered as boolean cells
     */
    const CONFIG_INDEX_BOOL_FIELDS = [
        'is_active',
        'is_published',
        'is_deleted',
    ];

    /**
     * The fields on the index view which should be run through number_format
     */
    const CONFIG_INDEX_NUMERIC_FIELDS = [
        'id',
    ];

    /**
     * The fields on the index view which should be centered
     */
    const CONFIG_INDEX_CENTERED_FIELDS = [
        'id',
    ];

    /**
     * The ID to give the index page
     */
    const CONFIG_INDEX_PAGE_ID = '';

    /**
     * Enable or disable the "Notes" feature on the index
     */
    const CONFIG_INDEX_NOTES_ENABLE = false;

    /**
     * Enable or disable the "Notes" count on note buttons
     */
    const CONFIG_INDEX_NOTES_COUNT = false;

    /**
     * Fields which should be marked as readonly when creating an item
     */
    const CONFIG_CREATE_READONLY_FIELDS = [];

    /**
     * The fields to ignore on the create view
     */
    const CONFIG_CREATE_IGNORE_FIELDS = [
        'id',
        'slug',
        'token',
        'is_deleted',
        'created',
        'created_by',
        'modified',
        'modified_by',
    ];

    /**
     * The fields to ignore on the edit view
     */
    const CONFIG_EDIT_IGNORE_FIELDS = self::CONFIG_CREATE_IGNORE_FIELDS;

    /**
     * Fields which should be marked as readonly when editing an item
     */
    const CONFIG_EDIT_READONLY_FIELDS = [];

    /**
     * Additional data to pass into the getAll call on the edit view
     */
    const CONFIG_EDIT_DATA = [];

    /**
     * Any additional header buttons to add to the edit page.
     */
    const CONFIG_EDIT_HEADER_BUTTONS = [];

    /**
     * Specify a specific order for fieldsets
     */
    const CONFIG_EDIT_FIELDSET_ORDER = [];

    /**
     * The ID to give the edit page
     */
    const CONFIG_EDIT_PAGE_ID = '';

    /**
     * Enable or disable the "Notes" feature
     */
    const CONFIG_EDIT_NOTES_ENABLE = true;

    /**
     * Additional data to pass into the getAll call on the delete view
     */
    const CONFIG_DELETE_DATA = [];

    /**
     * Additional data to pass into the getAll call on the sort view
     */
    const CONFIG_SORT_DATA = [];

    /**
     * Which column to use for the label when sorting
     */
    const CONFIG_SORT_LABEL = 'label';

    /**
     * Any additional columns to add to the sort view
     */
    const CONFIG_SORT_COLUMNS = [];

    /**
     * When browsing, this string is passed to supporting functions
     */
    const EDIT_MODE_BROWSE = 'BROWSE';

    /**
     * When creating, this string is passed to supporting functions
     */
    const EDIT_MODE_CREATE = 'CREATE';

    /**
     * When editing, this string is passed to supporting functions
     */
    const EDIT_MODE_EDIT = 'EDIT';

    /**
     * When deleting, this string is passed to supporting functions
     */
    const EDIT_MODE_DELETE = 'DELETE';

    /**
     * When restoring, this string is passed to supporting functions
     */
    const EDIT_MODE_RESTORE = 'RESTORE';

    /**
     * When copying, this string is passed to supporting functions
     */
    const EDIT_MODE_COPY = 'COPY';

    /**
     * When sorting, this string is passed to supporting functions
     */
    const EDIT_MODE_SORT = 'SORT';

    /**
     * Enable or disable the "last modified" check on save
     */
    const EDIT_MODIFIED_CHECK_ENABLED = true;

    /**
     * The ID of the "modified" hidden input
     */
    const EDIT_MODIFIED_CHECK_ID_MODIFIED = 'default-controller-modified';

    /**
     * The ID of the "overwrite" hidden input
     */
    const EDIT_MODIFIED_CHECK_ID_OVERWRITE = 'default-controller-overwrite';

    /**
     * Message displayed to user when an item is successfully created
     */
    const CREATE_SUCCESS_MESSAGE = 'Item created successfully. %s';

    /**
     * Message displayed to user when an item fails to be created
     */
    const CREATE_ERROR_MESSAGE = 'Failed to create item.';

    /**
     * Message displayed to user when an item is successfully updated
     */
    const EDIT_SUCCESS_MESSAGE = 'Item updated successfully. %s';

    /**
     * Message displayed to user when an item fails to be created
     */
    const EDIT_ERROR_MESSAGE = 'Failed to update item.';

    /**
     * Message displayed to user when an item is successfully deleted
     */
    const DELETE_SUCCESS_MESSAGE = 'Item deleted successfully.';

    /**
     * Message displayed to user when an item fails to be deleted
     */
    const DELETE_ERROR_MESSAGE = 'Failed to delete item.';

    /**
     * Message displayed to user when an item is successfully restored
     */
    const RESTORE_SUCCESS_MESSAGE = 'Item restored successfully.';

    /**
     * Message displayed to user when an item fails to be restored
     */
    const RESTORE_ERROR_MESSAGE = 'Failed to restore item.';

    /**
     * Message displayed to user when an items are ordered successfully
     */
    const ORDER_SUCCESS_MESSAGE = 'Items ordered successfully.';

    /**
     * Message displayed to user when an item fails to be ordered
     */
    const ORDER_ERROR_MESSAGE = 'Failed to order items.';

    /**
     * Message displayed to user when an item is successfully copied
     */
    const COPY_SUCCESS_MESSAGE = 'Item copied successfully.';

    /**
     * Whether to record updates in the admin change log
     */
    const CHANGELOG_ENABLED = true;

    /**
     * The name to use when creating changelog items, defaults to the resource class name
     */
    const CHANGELOG_ENTITY_NAME = null;

    /**
     * An array of fields to ignore when processing change log updates
     */
    const CHANGELOG_FIELDS_IGNORE = [
        'id',
        'is_deleted',
        'created',
        'created_by',
        'modified',
        'modified_by',
    ];

    /**
     * An array of fields to redact/mask when processing changelog updates
     */
    const CHANGELOG_FIELDS_REDACT = [
        'password',
    ];

    /**
     * The string to use when sorting in ascending order
     */
    const SORT_ASCENDING = 'asc';

    /**
     * The string to use when sorting in descending order
     */
    const SORT_DESCENDING = 'desc';

    // --------------------------------------------------------------------------

    /**
     * Any additional buttons to add for each row item (will sit between "View" if available and "Edit").
     * Takes the following format:
     *
     *   [
     *       // The button's URL, row items can be substituted in using double curly syntax.
     *       // Will be prefixed with $CONFIG['BASE_URL'] (which is the URL to the controller)
     *       'url'        => 'edit/{{id}}',
     *
     *       // The button's value/label
     *       'label'      => 'Edit',
     *
     *       // Additional classes to add to the button
     *       'class'      => 'btn-primary',
     *
     *       // Additional attributes to add to the button
     *       'attr'       => '',
     *
     *       // If required, a permission string to check in order to render the button;
     *       // will be appended to `admin:$CONFIG['PERMISSION']:`
     *       'permission' => 'edit',
     *
     *       // An expression to determine if the button can be rendered
     *       'enabled'   => function($oItem) { return true; },
     *   ],
     */
    protected static $aConfigIndexRowButtons = [];

    // --------------------------------------------------------------------------

    /**
     * Contains the configs for this controller
     *
     * @var array
     */
    protected $aConfig;

    // --------------------------------------------------------------------------

    /**
     * The ChangeLog model instance
     *
     * @var ChangeLog
     */
    protected $oChangeLogModel;

    // --------------------------------------------------------------------------

    /**
     * DefaultController constructor.
     *
     * @throws NailsException
     */
    public function __construct()
    {
        parent::__construct();
        $this->getConfig();
        $this->oChangeLogModel = Factory::model('ChangeLog', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     *
     * @return array|Nav
     * @throws NailsException
     */
    public static function announce()
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup
            ->setLabel(static::getSidebarGroup())
            ->setIcon(static::CONFIG_SIDEBAR_ICON)
            ->setSearchTerms(static::CONFIG_SIDEBAR_SEARCH_TERMS);

        if (static::userCan(static::EDIT_MODE_BROWSE)) {
            $oNavGroup
                ->addAction(
                    sprintf(static::CONFIG_SIDEBAR_FORMAT, static::getTitlePlural()),
                    'index'
                );
        }

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     *
     * @return array
     */
    public static function permissions(): array
    {
        $aPermissions = parent::permissions();

        if (!empty(static::CONFIG_PERMISSION)) {

            $aPermissions[static::EDIT_MODE_BROWSE]  = 'Can browse items';
            $aPermissions[static::EDIT_MODE_CREATE]  = 'Can create items';
            $aPermissions[static::EDIT_MODE_EDIT]    = 'Can edit items';
            $aPermissions[static::EDIT_MODE_DELETE]  = 'Can delete items';
            $aPermissions[static::EDIT_MODE_RESTORE] = 'Can restore items';

            if (static::isCopyButtonEnabled()) {
                $aPermissions[static::EDIT_MODE_COPY] = 'Can copy items';
            }

            if (static::isSortButtonEnabled()) {
                $aPermissions[static::EDIT_MODE_SORT] = 'Can sort items';
            }
        }

        return $aPermissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse all items
     *
     * @return void
     */
    public function index(): void
    {
        if (!static::userCan(static::EDIT_MODE_BROWSE)) {
            unauthorised();
        }

        /** @var Input $oInput */
        $oInput  = Factory::service('Input');
        $oModel  = $this->getModel();
        $aConfig = $this->getConfig();

        $sAlias      = $oModel->getTableAlias();
        $aSortConfig = $aConfig['SORT_OPTIONS'];

        if (classUses($oModel, Nestable::class)) {
            $aSortConfig = array_merge(
                ['Hierarchy' => 'order'],
                $aSortConfig
            );
        }

        //  Other parameters
        $iPage      = (int) $oInput->get('page') ?: 0;
        $iPerPage   = (int) $oInput->get('perPage') ?: 50;
        $sSortOn    = (int) $oInput->get('sortOn') ?: 0;
        $sSortOrder = $oInput->get('sortOrder') ?: $aConfig['SORT_DIRECTION'];
        $sKeywords  = $oInput->get('keywords');
        $aCbFilters = $this->indexCheckboxFilters();
        $aDdFilters = $this->indexDropdownFilters();

        // Translate a sorting index to a column
        $sSortKey = getFromArray(
            $sSortOn,
            array_values($aSortConfig),
            reset($aSortConfig)
        );

        if (is_string($sSortKey) && strpos('.', $sSortKey) === false) {
            $sSortKey = $oModel->getTableAlias(true) . $sSortKey;
        }

        $aData = [
                'cbFilters' => $aCbFilters,
                'ddFilters' => $aDdFilters,
                'keywords'  => $sKeywords,
                'sort'      => array_filter([
                    is_callable($sSortKey)
                        ? [call_user_func($sSortKey), $sSortOrder, false]
                        : [$sSortKey, $sSortOrder],
                ]),
            ] + $aConfig['INDEX_DATA'];

        // --------------------------------------------------------------------------

        if (classUses($oModel, HasDataColumns::class)) {
            $aData['select'] = $oModel->describeFieldsExcludingData();
        }

        // --------------------------------------------------------------------------

        if (classUses($oModel, Localised::class)) {
            $aData['NO_LOCALISE_FILTER'] = true;
        }

        // --------------------------------------------------------------------------

        $iTotalRows               = $oModel->countAll($aData);
        $this->data['items']      = $oModel->getAll($iPage, $iPerPage, $aData);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $iTotalRows);
        $this->data['search']     = Helper::searchObject(
            classUses($oModel, Searchable::class),
            array_keys($aSortConfig),
            $sSortOn,
            $sSortOrder,
            $iPerPage,
            $sKeywords,
            $aCbFilters,
            $aDdFilters
        );

        // --------------------------------------------------------------------------

        static::addHeaderButtons($aConfig['INDEX_HEADER_BUTTONS']);

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_PLURAL'] . ' &rsaquo; Manage';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new item
     *
     * @return void
     */
    public function create(): void
    {
        if (!static::isCreateButtonEnabled()) {
            show404();
        } elseif (!static::userCan(static::EDIT_MODE_CREATE)) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData();

        // --------------------------------------------------------------------------

        if ($oInput->post()) {

            try {

                $this->runFormValidation(static::EDIT_MODE_CREATE);
                $oDb->transaction()->start();
                $this->beforeCreateAndEdit(static::EDIT_MODE_CREATE);
                $this->beforeCreate();

                if (classUses($oModel, Localised::class)) {

                    /** @var Locale $oLocale */
                    $oLocale = Factory::service('Locale');
                    /** @var \Nails\Common\Factory\Locale $oItemLocale */
                    $oItemLocale = Factory::factory('Locale');
                    $oLocale->setFromString($oItemLocale, $oInput->post('locale'));

                    $oItem = $oModel->create($this->getPostObject(), true, $oItemLocale);

                } else {
                    $oItem = $oModel->create($this->getPostObject(), true);
                }

                if (!$oItem) {
                    throw new NailsException(static::CREATE_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                $this->afterCreateAndEdit(static::EDIT_MODE_CREATE, $oItem);
                $this->afterCreate($oItem);
                $this->addToChangeLog(static::EDIT_MODE_CREATE, $oItem);
                $oDb->transaction()->commit();

                if (property_exists($oItem, 'url')) {
                    $sLink = anchor(
                        $oItem->url,
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } elseif (method_exists($oItem, 'getUrl')) {
                    $sLink = anchor(
                        $oItem->getUrl(),
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                $this->oUserFeedback->success(sprintf(static::CREATE_SUCCESS_MESSAGE, $sLink));

                if (static::isEditButtonEnabled($oItem)) {
                    if (classUses($oModel, Localised::class)) {
                        redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id . '/' . $oItem->locale);
                    } else {
                        redirect($aConfig['BASE_URL'] . '/edit/' . $oItem->id);
                    }
                } else {
                    $this->returnToIndex();
                }

            } catch (\Exception $e) {
                $oDb->transaction()->rollback();
                $this->oUserFeedback->error($e->getMessage());
            }
        }

        // --------------------------------------------------------------------------

        /** @var Uri $oUri */
        $oUri           = Factory::service('Uri');
        $iExistingId    = (int) $oUri->segment(5) ?: null;
        $sDesiredLocale = $oUri->segment(6) ?: null;
        if (classUses($oModel, Localised::class) && !$iExistingId) {

            /** @var Locale $oLocale */
            $oLocale = Factory::service('Locale');
            /** @var Field $oField */
            foreach ($aConfig['FIELDS'] as $oField) {
                if ($oField->getKey() === 'locale') {
                    $oField->setInfo(
                        $oField->getInfo() .
                        ' New items must be created in ' . $oLocale->getDefautLocale()->getDisplayLanguage()
                    );
                    break;
                }
            }

        } elseif (classUses($oModel, Localised::class)) {

            //  Validate the ID, and only allow creation of new locales
            $aExisting = $oModel->getAll([
                'NO_LOCALISE_FILTER' => true,
                'where'              => [
                    ['id', $iExistingId],
                ],
            ]);

            if (empty($aExisting)) {
                show404();
            }

            //  Test if new locales can be created, filter out existing locales from the options list
            $aExistingLocales = arrayExtractProperty($aExisting, 'locale');

            /** @var Field $oField */
            foreach ($aConfig['FIELDS'] as $oField) {
                if ($oField->getKey() === 'locale') {

                    $aDiff = array_diff(
                        array_keys($oField->getOptions()),
                        array_values($aExistingLocales)
                    );

                    if (empty($aDiff)) {
                        $this->oUserFeedback->error('No more variations of this item can be created.');
                        $this->returnToIndex();
                    }

                    $oField->setOptions(array_intersect_key(
                        $oField->options,
                        array_flip($aDiff)
                    ));

                    $oField->setDefault($sDesiredLocale);

                    unset($this->aConfig['CREATE_READONLY_FIELDS'][array_search('locale', $this->aConfig['CREATE_READONLY_FIELDS'])]);
                    break;
                }
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_SINGLE'] . ' &rsaquo; Create';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing item
     *
     * @return void
     */
    public function edit(): void
    {
        if (!static::userCan(static::EDIT_MODE_EDIT)) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();
        $oItem   = $this->getItem($aConfig['EDIT_DATA']);

        if (!static::isEditButtonEnabled($oItem)) {
            show404();
        }

        // --------------------------------------------------------------------------

        //  View Data & Assets
        $this->loadEditViewData($oItem);

        // --------------------------------------------------------------------------

        if ($oInput->post()) {
            try {

                $this->runFormValidation(static::EDIT_MODE_EDIT);
                $oDb->transaction()->start();
                $this->beforeCreateAndEdit(static::EDIT_MODE_EDIT, $oItem);
                $this->beforeEdit($oItem);

                if (classUses($oModel, Localised::class)) {
                    $bResult = $oModel->update($oItem->id, $this->getPostObject(), $oItem->locale);
                } else {
                    $bResult = $oModel->update($oItem->id, $this->getPostObject());
                }

                if (!$bResult) {
                    throw new NailsException(static::EDIT_ERROR_MESSAGE . ' ' . $oModel->lastError());
                }

                if (classUses($oModel, Localised::class)) {
                    $oNewItem = $this->getItem(
                        array_merge(
                            $aConfig['EDIT_DATA'],
                            ['USE_LOCALE' => $oInput->post('locale')]
                        )
                    );
                } else {
                    $oNewItem = $this->getItem($aConfig['EDIT_DATA']);
                }
                $this->afterCreateAndEdit(static::EDIT_MODE_EDIT, $oNewItem, $oItem);
                $this->afterEdit($oNewItem, $oItem);
                $this->addToChangeLog(static::EDIT_MODE_EDIT, $oNewItem, $oItem);
                $oDb->transaction()->commit();

                if (property_exists($oNewItem, 'url')) {
                    $sLink = anchor(
                        $oNewItem->url,
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } elseif (method_exists($oNewItem, 'getUrl')) {
                    $sLink = anchor(
                        $oNewItem->getUrl(),
                        'View &nbsp;<span class="fa fa-external-link-alt"></span>',
                        'class="btn btn-success btn-xs pull-right" target="_blank"'
                    );
                } else {
                    $sLink = '';
                }

                $this->oUserFeedback->success(sprintf(static::EDIT_SUCCESS_MESSAGE, $sLink));

                if (classUses($oModel, Localised::class)) {
                    $sRedirectUrl = $aConfig['BASE_URL'] . '/edit/' . $oItem->id . '/' . $oItem->locale;
                } else {
                    $sRedirectUrl = $aConfig['BASE_URL'] . '/edit/' . $oItem->id;
                }

                redirect(
                    $oInput->get('isModal')
                        ? $sRedirectUrl .= '?isModal=true'
                        : $sRedirectUrl
                );

            } catch (ItemModifiedException $e) {

                $oDb->transaction()->rollback();

                $oItem = $this->getItem();
                /** @var User $oUserModel */
                $oUserModel = Factory::model('User', \Nails\Auth\Constants::MODULE_SLUG);
                $oUser      = $oUserModel->getById($oItem->modified_by);

                $sFuncName   = sprintf('submitForm_%s', uniqid());
                $sModifiedId = static::EDIT_MODIFIED_CHECK_ID_OVERWRITE;

                $sBody = <<<EOT
                    <script>
                    function $sFuncName() {
                        document.getElementById('$sModifiedId').value = 1;
                        document.querySelector('body .content form').submit();
                    }
                    </script>
                    <p class="alert alert-danger">
                        This item has been modified since you started editing.
                    </p>
                    <p>
                        This item was last saved at %s, by %s.
                    </p>
                    <p>
                        <button
                            class="btn btn-danger btn-block"
                            onclick="$sFuncName()"
                        >
                            Overwrite
                        </button>
                    </p>
                EOT;

                Helper::addModal(
                    'Changes have not yet been saved',
                    sprintf(
                        $sBody,
                        toUserDatetime($oItem->modified),
                        $oUser->name ?? 'the system',
                    )
                );

            } catch (\Exception $e) {
                $oDb->transaction()->rollback();
                $this->oUserFeedback->error($e->getMessage());
            }
        }

        // --------------------------------------------------------------------------

        static::addHeaderButtons($aConfig['EDIT_HEADER_BUTTONS']);

        // --------------------------------------------------------------------------

        $this->data['page']->title = $aConfig['TITLE_SINGLE'] . ' &rsaquo; Edit';
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an item
     *
     * @return void
     */
    public function delete(): void
    {
        if (!static::userCan(static::EDIT_MODE_DELETE)) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();
        $oItem   = $this->getItem($aConfig['DELETE_DATA']);

        if (!static::isDeleteButtonEnabled($oItem)) {
            show404();
        }

        try {

            $oDb->transaction()->start();
            $this->beforeDelete($oItem);

            if (classUses($oModel, Localised::class)) {
                $oModel->delete($oItem->id, $oItem->locale);
            } elseif (!$oModel->delete($oItem->id)) {
                throw new NailsException(static::DELETE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $this->afterDelete($oItem);
            $this->addToChangeLog(static::EDIT_MODE_DELETE, $oItem);
            $oDb->transaction()->commit();

            if (static::isRestoreButtonEnabled($oItem)) {
                if (classUses($oModel, Localised::class)) {
                    $sRestoreLink = anchor(
                        $aConfig['BASE_URL'] . '/restore/' . $oItem->id . '/' . $oItem->locale,
                        'Restore?'
                    );
                } else {
                    $sRestoreLink = anchor(
                        $aConfig['BASE_URL'] . '/restore/' . $oItem->id,
                        'Restore?'
                    );
                }
            } else {
                $sRestoreLink = '';
            }

            $this->oUserFeedback->success(static::DELETE_SUCCESS_MESSAGE . ' ' . $sRestoreLink);
            $this->returnToIndex();

        } catch (\Exception $e) {
            $oDb->transaction()->rollback();
            $this->oUserFeedback->error(static::DELETE_ERROR_MESSAGE . ' ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Restore an item
     *
     * @return void
     */
    public function restore(): void
    {
        if (!static::userCan(static::EDIT_MODE_RESTORE)) {
            unauthorised();
        }

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();
        $oItem   = $this->getItem([], null, true);

        if (!static::isRestoreButtonEnabled($oItem)) {
            show404();
        }

        try {

            $oDb->transaction()->start();
            if (classUses($oModel, Localised::class)) {
                $bResult = $oModel->restore($oItem->id, $oItem->locale);
            } else {
                $bResult = $oModel->restore($oItem->id);
            }

            if (!$bResult) {
                throw new NailsException(static::RESTORE_ERROR_MESSAGE . ' ' . $oModel->lastError());
            }

            $this->addToChangeLog(static::EDIT_MODE_RESTORE, $oItem);
            $oDb->transaction()->commit();
            $this->oUserFeedback->success(static::RESTORE_SUCCESS_MESSAGE);
            $this->returnToIndex();

        } catch (\Exception $e) {
            $oDb->transaction()->rollback();
            $this->oUserFeedback->error(static::RESTORE_ERROR_MESSAGE . ' ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sort items into order
     *
     * @return void
     */
    public function sort(): void
    {
        if (!static::isSortButtonEnabled()) {
            show404();
        } elseif (!static::userCan(static::EDIT_MODE_SORT)) {
            unauthorised();
        }

        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();

        if ($oInput->post()) {
            try {

                $oDb->transaction()->start();
                $aItems = array_values((array) $oInput->post('order'));
                foreach ($aItems as $iOrder => $iId) {

                    if (classUses($oModel, Localised::class)) {

                        $aItems = $oModel->getAll([
                            'NO_LOCALISE_FILTER' => true,
                            'where'              => [
                                ['id', $iId],
                            ],
                        ]);

                        foreach ($aItems as $oItem) {
                            if (!$oModel->skipUpdateTimestamp()->update((int) $iId, ['order' => $iOrder], $oItem->locale)) {
                                throw new NailsException(
                                    static::ORDER_ERROR_MESSAGE . ' ' . $oModel->lastError()
                                );
                            }
                        }

                    } elseif (!$oModel->skipUpdateTimestamp()->update((int) $iId, ['order' => $iOrder])) {
                        throw new NailsException(
                            static::ORDER_ERROR_MESSAGE . ' ' . $oModel->lastError()
                        );
                    }
                }

                //  @todo (Pablo - 2019-10-30) - Add changelog support here

                $oDb->transaction()->commit();
                $this->oUserFeedback->success(static::ORDER_SUCCESS_MESSAGE);

                redirect($aConfig['BASE_URL'] . '/sort');

            } catch (\Exception $e) {
                $oDb->transaction()->rollback();
                $this->oUserFeedback->error($e->getMessage());
            }
        }

        $aItems    = $this->getItemsToSort();
        $aSections = $this->sortItemsIntoSections($aItems);

        $this->data['aSections']   = $aSections;
        $this->data['page']->title = $aConfig['TITLE_PLURAL'] . ' &rsaquo; Sort';
        Helper::loadView('order');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the items for the sort view
     *
     * @return array
     */
    protected function getItemsToSort(): array
    {
        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();

        return $oModel->getAll($aConfig['SORT_DATA']);
    }

    // --------------------------------------------------------------------------

    /**
     * Sorts sorting items into sections, if they aren't already a section
     *
     * @param array $aItems
     *
     * @return Section[]
     * @throws FactoryException
     */
    protected function sortItemsIntoSections(array $aItems): array
    {
        /** @var Section $oSection */
        $oSection  = Factory::factory('DefaultControllerSortSection', Constants::MODULE_SLUG);
        $aSections = [];

        //  Ensure each item is in a sort section
        foreach ($aItems as $oItem) {
            if (!$oItem instanceof Section) {
                $oSection->addItem(clone $oItem);
            } else {
                $aSections[] = $oItem;
            }
        }

        if (count($oSection->getItems())) {
            $aSections[] = $oSection;
        }

        return $aSections;
    }

    // --------------------------------------------------------------------------

    /**
     * Duplicate an item
     *
     * @throws FactoryException
     */
    public function copy()
    {
        if (!static::userCan(static::EDIT_MODE_COPY)) {
            unauthorised();
        }

        /** @var Database $oDb */
        $oDb = Factory::service('Database');

        $aConfig = $this->getConfig();
        $oModel  = $this->getModel();
        $oItem   = $this->getItem();

        if (!static::isCopyButtonEnabled($oItem)) {
            show404();
        }

        try {

            $oDb->transaction()->start();
            $this->beforeCreateAndEdit(static::EDIT_MODE_CREATE);
            $this->beforeCreate();
            $this->beforeCopy($oItem);

            $oNewItem = $oModel->copy($oItem->id, true);
            if (empty($oNewItem)) {
                throw new \Exception($oModel->lastError());
            }

            //  @todo (Pablo - 2019-12-10) - Add support for classes which implement Localised trait

            $this->afterCreateAndEdit(static::EDIT_MODE_CREATE, $oNewItem, $oItem);
            $this->afterCreate($oNewItem);
            $this->afterCopy($oNewItem, $oItem);
            $this->addToChangeLog(static::EDIT_MODE_CREATE, $oNewItem);
            $oDb->transaction()->commit();

            $this->oUserFeedback->success(static::COPY_SUCCESS_MESSAGE);
            redirect($aConfig['BASE_URL'] . '/edit/' . $oNewItem->id);

        } catch (\Exception $e) {
            $this->oUserFeedback->error('Failed to copy item. ' . $e->getMessage());
            $this->returnToIndex();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the controllers config
     *
     * @return array
     * @throws NailsException
     * @throws FactoryException
     */
    protected function &getConfig(): array
    {
        if (!empty($this->aConfig)) {
            return $this->aConfig;
        }

        //  Ensure required constants are set
        $aRequiredConstants = [
            'CONFIG_MODEL_NAME',
            'CONFIG_MODEL_PROVIDER',
        ];
        foreach ($aRequiredConstants as $sConstant) {
            if (empty(constant('static::' . $sConstant))) {
                throw new NailsException(
                    'The constant "static::' . $sConstant . '" must be set in ' . get_called_class()
                );
            }
        }

        //  Build the config array
        $oModel  = static::getModel();
        $aConfig = [
            'MODEL_NAME'             => static::CONFIG_MODEL_NAME,
            'MODEL_PROVIDER'         => static::CONFIG_MODEL_PROVIDER,
            'MODEL_INSTANCE'         => $oModel,
            'CAN_CREATE'             => static::CONFIG_CAN_CREATE,
            'CAN_EDIT'               => static::CONFIG_CAN_EDIT,
            'CAN_VIEW'               => static::CONFIG_CAN_VIEW,
            'CAN_DELETE'             => static::CONFIG_CAN_DELETE,
            'CAN_RESTORE'            => static::CONFIG_CAN_RESTORE,
            'CAN_COPY'               => static::CONFIG_CAN_COPY,
            'CAN_SORT'               => static::CONFIG_CAN_SORT,
            'PERMISSION'             => static::CONFIG_PERMISSION,
            'TITLE_SINGLE'           => static::getTitleSingle(),
            'TITLE_PLURAL'           => static::getTitlePlural(),
            'SIDEBAR_GROUP'          => static::getSidebarGroup(),
            'SIDEBAR_ICON'           => static::CONFIG_SIDEBAR_ICON,
            'BASE_URL'               => static::getBaseUrl(),
            'SORT_OPTIONS'           => static::CONFIG_SORT_OPTIONS,
            'SORT_DIRECTION'         => static::CONFIG_SORT_DIRECTION,
            'INDEX_FIELDS'           => static::CONFIG_INDEX_FIELDS,
            'INDEX_HEADER_BUTTONS'   => static::CONFIG_INDEX_HEADER_BUTTONS,
            'INDEX_ROW_BUTTONS'      => array_merge(static::$aConfigIndexRowButtons, static::CONFIG_INDEX_ROW_BUTTONS),
            'INDEX_DATA'             => static::CONFIG_INDEX_DATA,
            'INDEX_BOOL_FIELDS'      => static::CONFIG_INDEX_BOOL_FIELDS,
            'INDEX_USER_FIELDS'      => static::CONFIG_INDEX_USER_FIELDS,
            'INDEX_NUMERIC_FIELDS'   => static::CONFIG_INDEX_NUMERIC_FIELDS,
            'INDEX_CENTERED_FIELDS'  => static::CONFIG_INDEX_CENTERED_FIELDS,
            'INDEX_PAGE_ID'          => static::CONFIG_INDEX_PAGE_ID,
            'INDEX_NOTES_ENABLE'     => static::CONFIG_INDEX_NOTES_ENABLE,
            'INDEX_NOTES_COUNT'      => static::CONFIG_INDEX_NOTES_COUNT,
            'CREATE_READONLY_FIELDS' => static::CONFIG_CREATE_READONLY_FIELDS,
            'CREATE_IGNORE_FIELDS'   => static::CONFIG_CREATE_IGNORE_FIELDS,
            'EDIT_HEADER_BUTTONS'    => static::CONFIG_EDIT_HEADER_BUTTONS,
            'EDIT_READONLY_FIELDS'   => static::CONFIG_EDIT_READONLY_FIELDS,
            'EDIT_IGNORE_FIELDS'     => static::CONFIG_EDIT_IGNORE_FIELDS,
            'EDIT_DATA'              => static::CONFIG_EDIT_DATA,
            'EDIT_PAGE_ID'           => static::CONFIG_EDIT_PAGE_ID,
            'EDIT_NOTES_ENABLE'      => static::CONFIG_EDIT_NOTES_ENABLE,
            'DELETE_DATA'            => static::CONFIG_DELETE_DATA,
            'SORT_DATA'              => static::CONFIG_SORT_DATA,
            'SORT_LABEL'             => static::CONFIG_SORT_LABEL,
            'SORT_COLUMNS'           => static::CONFIG_SORT_COLUMNS,
            'FIELDSET_ORDER'         => static::CONFIG_EDIT_FIELDSET_ORDER,
            'FIELDS'                 => $oModel->describeFields(),
            'FLOATING_CONFIG'        => [
                'last_modified' => [
                    'enabled'       => static::EDIT_MODIFIED_CHECK_ENABLED,
                    'last_modified' => [
                        'id' => static::EDIT_MODIFIED_CHECK_ID_MODIFIED,
                    ],
                    'overwrite'     => [
                        'id' => static::EDIT_MODIFIED_CHECK_ID_OVERWRITE,
                    ],
                ],
                'notes'         => [
                    'enabled'  => static::CONFIG_EDIT_NOTES_ENABLE,
                    'model'    => static::CONFIG_MODEL_NAME,
                    'provider' => static::CONFIG_MODEL_PROVIDER,
                ],
            ],
        ];

        $this->aConfig        =& $aConfig;
        $this->data['CONFIG'] =& $this->aConfig;

        if (classUses($oModel, Sortable::class)) {
            $aConfig['SORT_OPTIONS']           = array_merge(['Defined Order' => 'order'], $aConfig['SORT_OPTIONS']);
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getSortableColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getSortableColumn();
        }

        if (classUses($oModel, Nestable::class)) {
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getBreadcrumbsColumn();
            $aConfig['CREATE_IGNORE_FIELDS'][] = $oModel->getOrderColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getBreadcrumbsColumn();
            $aConfig['EDIT_IGNORE_FIELDS'][]   = $oModel->getOrderColumn();
        }

        if (classUses($oModel, Localised::class)) {
            $aConfig['CREATE_READONLY_FIELDS'][] = 'locale';
            $aConfig['EDIT_READONLY_FIELDS'][]   = 'locale';
            $aConfig['INDEX_FIELDS']             = array_merge(
                [
                    'Locale' => function ($oRow) {
                        $sFlag = $oRow->locale->getFlagEmoji();
                        return $sFlag ? '<span class="hint--bottom" aria-label="' . $oRow->locale->getDisplayLanguage() . '">' . $sFlag . '</span>' : $oRow->locale;
                    },
                ],
                $aConfig['INDEX_FIELDS']
            );
        }

        if (classUses($oModel, Publishable::class)) {
            $sColumnIsPublished      = $oModel->getColumnIsPublished();
            $sColumnDatePublished    = $oModel->getColumnDatePublished();
            $sColumnDateExpire       = $oModel->getColumnDateExpire();
            $aConfig['INDEX_FIELDS'] = array_merge(
                $aConfig['INDEX_FIELDS'],
                [
                    'Published' => function ($oItem) use ($sColumnIsPublished, $sColumnDatePublished, $sColumnDateExpire) {

                        if (empty($oItem->{$sColumnIsPublished})) {
                            return [
                                sprintf(
                                    '<span class="hint--top" aria-label="%s"><b class="fa fa-lg %s"></b></span>',
                                    'Item is explicitly marked as unpublished',
                                    'fa-times-circle'
                                ),
                                'text-center danger',
                            ];
                        }

                        $oDatePublished = $sColumnDatePublished ? ($oItem->{$sColumnDatePublished} ?? null) : null;
                        $oDateExpire    = $sColumnDateExpire ? ($oItem->{$sColumnDateExpire} ?? null) : null;

                        $bIsPublished = (!$oDatePublished || $oDatePublished->isPast()) && (!$oDateExpire || $oDateExpire->isFuture());

                        return [
                            sprintf(
                                '<span class="hint--top" aria-label="%s"><b class="fa fa-lg %s"></b></span>',
                                $bIsPublished
                                    ? ''
                                    : implode('; ', array_filter([
                                    $oDatePublished && $oDatePublished->isFuture()
                                        ? 'Will be published: ' . $oDatePublished->formatted . ' (' . $oDatePublished->relative() . ')'
                                        : null,
                                    $oDateExpire && $oDateExpire->isPast()
                                        ? 'Expired: ' . $oDateExpire->formatted . ' (' . $oDateExpire->relative() . ')'
                                        : null,
                                ])),
                                $bIsPublished
                                    ? 'fa-check-circle'
                                    : 'fa-times-circle'
                            ),
                            implode(' ', [
                                'text-center',
                                $bIsPublished
                                    ? 'success'
                                    : 'danger',
                            ]),
                        ];
                    },
                ],
            );
        }

        // --------------------------------------------------------------------------

        if (static::isCreateButtonEnabled() && classUses($oModel, Localised::class)) {
            $oItem = $this->getItem([], null, false, false);
            if (!empty($oItem) && !empty($oItem->missing_locales)) {
                $aVersions = [];
                foreach ($oItem->missing_locales as $oLocale) {
                    $aVersions['Create ' . $oLocale->getDisplayLanguage()] = $aConfig['BASE_URL'] . '/create/' . $oItem->id . '/' . $oLocale;
                }
                $this->addEditHeaderButton(
                    $aVersions,
                    'Create Version',
                    'btn btn-warning',
                );
            }
        }

        if (static::isCreateButtonEnabled()) {
            $this->addIndexHeaderButton(
                $aConfig['BASE_URL'] . '/create',
                'Create'
            );
            $this->addEditHeaderButton(
                $aConfig['BASE_URL'] . '/create',
                'Create'
            );
        }

        if (static::isSortButtonEnabled()) {
            $this->addIndexHeaderButton(
                $aConfig['BASE_URL'] . '/sort',
                'Set Order'
            );
        }

        // --------------------------------------------------------------------------

        $bIsLocalised = classUses(static::getModel(), Localised::class);
        $bIsCopyable  = classUses(static::getModel(), Copyable::class);

        /** @var Locale $oLocale */
        $oLocale = Factory::service('Locale');

        $aConfig['INDEX_ROW_BUTTONS'] = array_merge(
            $aConfig['INDEX_ROW_BUTTONS'],
            array_filter([
                [
                    'url'     => function ($oItem) {
                        return $oItem->url ?? (method_exists($oItem, 'getUrl') ? $oItem->getUrl() : null);
                    },
                    'label'   => lang('action_view'),
                    'class'   => 'btn-default',
                    'attr'    => 'target="_blank"',
                    'enabled' => function ($oItem) {
                        return static::isViewButtonEnabled($oItem);
                    },
                ],

                //  Non-localised buttons
                !$bIsLocalised ? [
                    'url'     => 'edit/{{id}}',
                    'label'   => lang('action_edit'),
                    'class'   => 'btn-primary',
                    'enabled' => function ($oItem) {
                        return static::isEditButtonEnabled($oItem);
                    },
                ] : null,
                !$bIsLocalised ? [
                    'url'     => 'delete/{{id}}',
                    'label'   => lang('action_delete'),
                    'class'   => 'btn-danger confirm',
                    'enabled' => function ($oItem) {
                        return static::isDeleteButtonEnabled($oItem);
                    },
                ] : null,

                //  Localised buttons
                $bIsLocalised ? [
                    'url'     => 'edit/{{id}}/{{locale}}',
                    'label'   => lang('action_edit'),
                    'class'   => 'btn-primary',
                    'enabled' => function ($oItem) {
                        return static::isEditButtonEnabled($oItem);
                    },
                ] : null,
                $bIsLocalised ? [
                    'url'   => function ($oItem) {
                        $aOut = [];
                        foreach ($oItem->missing_locales as $oLocale) {
                            $aOut['Add ' . $oLocale->getDisplayLanguage()] = 'create/{{id}}/' . $oLocale;
                        }
                        return $aOut;
                    },
                    'label' => 'Create Version',
                    'class' => function ($oItem) {
                        if (!static::localisedItemCanBeCreated($oItem)) {
                            return 'btn-warning btn-disabled';
                        }
                        return 'btn-warning';
                    },
                    'attr'  => function ($oItem) {
                        if (!static::localisedItemCanBeCreated($oItem)) {
                            //  @todo (Pablo - 2019-04-17) - Explicitly state why
                            return 'class="hint--bottom" aria-label="A new version cannot be created; you may not have permission, ' .
                                'or all supported locales exist already"';
                        }
                        return '';
                    },
                ] : null,
                $bIsLocalised ? [
                    'url'     => 'delete/{{id}}/{{locale}}',
                    'label'   => lang('action_delete'),
                    'class'   => function ($oItem) {
                        if (!static::localisedItemCanBeDeleted($oItem)) {
                            return 'btn-danger btn-disabled';
                        }
                        return 'btn-danger confirm';
                    },
                    'attr'    => function ($oItem) {
                        if (!static::localisedItemCanBeDeleted($oItem)) {
                            //  @todo (Pablo - 2019-04-17) - Explicitly state why
                            return 'class="hint--bottom" aria-label="This item cannot be deleted; you may not have permission, ' .
                                'or other locales may exist which need to be deleted first"';
                        }
                        return '';
                    },
                    'enabled' => function ($oItem) use ($oLocale) {
                        return static::isDeleteButtonEnabled($oItem);
                    },
                ] : null,
                [
                    'url'     => 'copy/{{id}}',
                    'label'   => 'Copy',
                    'class'   => 'btn-default',
                    'enabled' => function ($oItem) {
                        return static::isCopyButtonEnabled($oItem);
                    },
                ],
                $aConfig['INDEX_NOTES_ENABLE'] ? [
                    'url'   => '#',
                    'label' => 'Notes',
                    'class' => 'btn-default js-admin-notes',
                    'attr'  => implode(' ', [
                        'data-model-name="' . $aConfig['MODEL_NAME'] . '"',
                        'data-model-provider="' . $aConfig['MODEL_PROVIDER'] . '"',
                        'data-id="{{id}}"',
                        'data-show-count="' . json_encode($aConfig['INDEX_NOTES_COUNT']) . '"',
                    ]),
                ] : null,
            ])
        );

        return $this->aConfig;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user has permission to create a localised item
     *
     * @param Resource $oItem The item to test
     *
     * @return bool
     */
    protected static function localisedItemCanBeCreated(Resource $oItem)
    {
        //  New versions can only be created if the user has permissions and there is a remaining supported locale
        if (static::CONFIG_CAN_CREATE && static::userCan(static::EDIT_MODE_CREATE)) {
            return !empty($oItem->missing_locales);
        }
        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a localised item can be delted
     *
     * @param Resource $oItem The item to test
     *
     * @return bool
     * @throws FactoryException
     */
    protected static function localisedItemCanBeDeleted(Resource $oItem)
    {
        /** @var Locale $oLocale */
        $oLocale        = Factory::service('Locale');
        $sDefaultLocale = (string) $oLocale->getDefautLocale();
        $sItemLocale    = (string) $oItem->locale;
        if ($sDefaultLocale !== $sItemLocale) {
            return true;
        } elseif ($sDefaultLocale === $sItemLocale && count($oItem->available_locales) === 1) {
            return true;
        } else {
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the string to use as the "single" title
     *
     * @return string
     */
    protected static function getTitleSingle(): string
    {
        if (!empty(static::CONFIG_TITLE_SINGLE)) {
            return static::CONFIG_TITLE_SINGLE;
        }

        $sTitle = preg_replace('/([a-z])([A-Z])/', '$1 $2', static::CONFIG_MODEL_NAME);
        $sTitle = strtolower($sTitle);
        $sTitle = ucwords($sTitle);

        return $sTitle;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the title to use as the "plural" title
     *
     * @return string
     * @throws FactoryException
     */
    protected static function getTitlePlural(): string
    {
        Factory::helper('inflector');
        return static::CONFIG_TITLE_PLURAL ?: pluralise(2, static::getTitleSingle());
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the value to use for the sidebar group
     *
     * @return string
     * @throws FactoryException
     */
    protected static function getSidebarGroup(): string
    {
        return static::CONFIG_SIDEBAR_GROUP ?: static::getTitlePlural();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the base URL
     *
     * @return string
     */
    protected static function getBaseUrl(): string
    {
        if (!empty(static::CONFIG_BASE_URL)) {
            return static::CONFIG_BASE_URL;
        }

        $aBits   = explode('\\', get_called_class());
        $sModule = lcfirst($aBits[count($aBits) - 2]);
        $sClass  = lcfirst($aBits[count($aBits) - 1]);
        return 'admin/' . $sModule . '/' . $sClass;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether a user has permission to perform an action
     *
     * @param string $sPermission The permission to check
     *
     * @return bool
     */
    protected static function userCan(string $sPermission): bool
    {
        return empty(static::CONFIG_PERMISSION) || userHasPermission('admin:' . static::CONFIG_PERMISSION . ':' . $sPermission);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the model instance
     *
     * @return \Nails\Common\Model\Base
     * @throws FactoryException
     */
    protected static function getModel(): \Nails\Common\Model\Base
    {
        return Factory::model(
            static::CONFIG_MODEL_NAME,
            static::CONFIG_MODEL_PROVIDER
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Any checkbox style filters to include on the index page
     *
     * @return IndexFilter[]
     */
    protected function indexCheckboxFilters(): array
    {
        return [];
    }

    // --------------------------------------------------------------------------

    /**
     * Any dropdown style filters to include on the index page
     *
     * @return IndexFilter[]
     */
    protected function indexDropdownFilters(): array
    {
        return array_merge(
            $this->getLocalisedDropdownFilters(),
            $this->getPublishableDropdownFilters(),
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns filters for models which implement Localised trait
     *
     * @return IndexFilter[]
     * @throws FactoryException
     */
    protected function getLocalisedDropdownFilters(): array
    {
        $oModel = static::getModel();
        /** @var IndexFilter $aFilters */
        $aFilters = [];

        if (classUses($oModel, Localised::class)) {

            /** @var IndexFilter $oFilter */
            $oFilter = Factory::factory('IndexFilter', Constants::MODULE_SLUG);
            $oFilter
                ->setLabel('Locale')
                ->setColumn('CONCAT(`language`, \'_\', `region`)');

            /** @var Locale $oLocale */
            $oLocale = Factory::service('Locale');

            /** @var Option $aOption */
            $aOption = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
            $oOption->setLabel('All Locales');

            $oFilter->addOption($oOption);

            foreach ($oLocale->getSupportedLocales() as $oSupportedLocale) {
                /** @var Option $aOption */
                $aOption = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOption
                    ->setLabel($oSupportedLocale->getDisplayLanguage())
                    ->setValue($oSupportedLocale->getLanguage() . '_' . $oSupportedLocale->getRegion());

                $oFilter->addOption($oOption);
            }

            $aFilters[] = $oFilter;
        }

        return $aFilters;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns filters for models which implement Publishable trait
     *
     * @return IndexFilter[]
     * @throws FactoryException
     */
    protected function getPublishableDropdownFilters(): array
    {
        $oModel = static::getModel();
        /** @var IndexFilter $aFilters */
        $aFilters = [];

        if (classUses($oModel, Publishable::class)) {

            $sColumnIsPublished = $oModel->getColumnIsPublished();
            $sColumnDatePublish = $oModel->getColumnDatePublished();
            $sColumnDateExpire  = $oModel->getColumnDateExpire();

            /** @var IndexFilter $oFilter */
            $oFilter = Factory::factory('IndexFilter', Constants::MODULE_SLUG);
            $oFilter
                ->setLabel('Show')
                //  This must be set to something, but as we're dealing with queries
                //  it doesn't matter what it is set to.
                ->setColumn('column');

            /** @var Option $oOptionAll */
            $oOptionAll = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
            $oOptionAll
                ->setLabel('All items');

            /** @var Option $oOptionYes */
            $oOptionYes = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
            $oOptionYes
                ->setLabel('All published items')
                ->setIsQuery(true)
                ->setValue('(' . implode(' AND ', array_filter([

                        '`' . $sColumnIsPublished . '` = 1',

                        $sColumnDatePublish
                            ? '(`' . $sColumnDatePublish . '` IS NULL OR `' . $sColumnDatePublish . '` <= NOW())'
                            : null,

                        $sColumnDateExpire
                            ? '(`' . $sColumnDateExpire . '` IS NULL OR `' . $sColumnDateExpire . '` > NOW())'
                            : null,

                    ])) . ')');

            /** @var Option $oOptionNo */
            $oOptionNo = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
            $oOptionNo
                ->setLabel('All unpublished items')
                ->setIsQuery(true)
                ->setValue('(' . implode(' OR ', array_filter([

                        '`' . $sColumnIsPublished . '` = 0',

                        $sColumnDatePublish
                            ? '(`' . $sColumnDatePublish . '` IS NOT NULL AND `' . $sColumnDatePublish . '` > NOW())'
                            : null,

                        $sColumnDateExpire
                            ? '(`' . $sColumnDateExpire . '` IS NOT NULL AND `' . $sColumnDateExpire . '` <= NOW())'
                            : null,

                    ])) . ')');

            if ($sColumnDateExpire || $sColumnDateExpire) {
                /** @var Option $oOptionNoExplicit */
                $oOptionNoExplicit = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOptionNoExplicit
                    ->setLabel('Explicitly unpublished items')
                    ->setIsQuery(true)
                    ->setValue('`' . $sColumnIsPublished . '` = 0',);
            }

            if ($sColumnDateExpire) {
                /** @var Option $oOptionNoExpired */
                $oOptionNoExpired = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOptionNoExpired
                    ->setLabel('Expired items')
                    ->setIsQuery(true)
                    ->setValue('(`' . $sColumnIsPublished . '` = 1 AND `' . $sColumnDateExpire . '` IS NOT NULL AND `' . $sColumnDateExpire . '` <= NOW())');
            }

            if ($sColumnDatePublish) {
                /** @var Option $oOptionNoPublish */
                $oOptionNoPublish = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOptionNoPublish
                    ->setLabel('Scheduled items')
                    ->setIsQuery(true)
                    ->setValue('(`' . $sColumnIsPublished . '` = 1 AND `' . $sColumnDatePublish . '` IS NOT NULL AND `' . $sColumnDatePublish . '` > NOW())');
            }

            $oFilter
                ->addOptions(array_filter([
                    $oOptionAll,
                    $oOptionYes,
                    $oOptionNo,
                    $oOptionNoExplicit ?? null,
                    $oOptionNoExpired ?? null,
                    $oOptionNoPublish ?? null,
                ]));

            $aFilters[] = $oFilter;
        }

        return $aFilters;
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param string   $sMode Whether the action was CREATE or EDIT
     * @param Resource $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeCreateAndEdit($sMode, Resource $oItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is edited
     *
     * @param Resource $oItem The old item, before it was edited
     *
     * @return void
     */
    protected function beforeEdit(Resource $oItem = null): void
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $sUserTimestamp = $oInput->post('last_modified');
        $sItemTimestamp = $oItem->modified->raw;
        $bOverwrite     = stringToBoolean($oInput->post('overwrite'));

        /**
         * If the user does not provide a timestamp then we cannot reliably determine
         * if it has changed. It is possible that the edit view is overridden and has
         * not provided the relevant inputs.
         */
        if (
            static::EDIT_MODIFIED_CHECK_ENABLED
            && $sUserTimestamp
            && $sItemTimestamp !== $sUserTimestamp
            && !$bOverwrite
        ) {
            throw new ItemModifiedException();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is created
     *
     * @return void
     */
    protected function beforeCreate(): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is copied
     *
     * @param Resource $oItem The item being copied
     *
     * @return void
     */
    protected function beforeCopy(Resource $oItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed before an item is deleted
     *
     * @param Resource $oItem The item being deleted
     *
     * @return void
     */
    protected function beforeDelete(Resource $oItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param string   $sMode    Whether the action was CREATE or EDIT
     * @param Resource $oNewItem The new item, after it was edited
     * @param Resource $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterCreateAndEdit($sMode, Resource $oNewItem, Resource $oOldItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is edited
     *
     * @param Resource $oNewItem The new item, after it was edited
     * @param Resource $oOldItem The old item, before it was edited
     *
     * @return void
     */
    protected function afterEdit(Resource $oNewItem, Resource $oOldItem = null): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is created
     *
     * @param Resource $oNewItem The new item
     *
     * @return void
     */
    protected function afterCreate(Resource $oNewItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is copied
     *
     * @param Resource $oNewItem The new item
     * @param Resource $oOldItem The original item
     *
     * @return void
     */
    protected function afterCopy(Resource $oNewItem, Resource $oOldItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Executed after an item is deleted
     *
     * @param Resource $oItem The deleted item
     *
     * @return void
     */
    protected function afterDelete(Resource $oItem): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     *
     * @param string $sMode      The mode in which the validation is being run
     * @param array  $aOverrides Any overrides for the fields; best to do this in the model's describeFields() method
     *
     * @return void
     * @throws ValidationException
     */
    protected function runFormValidation(string $sMode, array $aOverrides = []): void
    {
        $aConfig = $this->getConfig();
        $oModel  = static::getModel();
        /** @var FormValidation $oFormValidation */
        $oFormValidation = Factory::service('FormValidation');

        $aRules = [];

        /** @var Field $oField */
        foreach ($aConfig['FIELDS'] as &$oField) {

            if (array_key_exists($oField->getKey(), $aOverrides)) {

                $aRules[$oField->getKey()] = $aOverrides[$oField->getKey()];

            } else {

                if ($sMode === static::EDIT_MODE_CREATE && classUses($oModel, Localised::class)) {
                    if ($oField->getKey() == 'locale') {

                        /** @var Locale $oLocale */
                        $oLocale = Factory::service('Locale');
                        /** @var Uri $oUri */
                        $oUri = Factory::service('Uri');

                        if (empty($oUri->segment(5))) {
                            $oField->addValidation('is[' . $oLocale->getDefautLocale() . ']');
                        }
                    }
                }

                $aRules[$oField->getKey()] = $oField->getValidation();
            }
        }

        $oFormValidation
            ->buildValidator(
                array_filter($aRules)
            )
            ->run();
    }

    // --------------------------------------------------------------------------

    /**
     * Load data for the edit/create view
     *
     * @param Resource $oItem The main item object
     *
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewData(Resource $oItem = null): void
    {
        $aConfig = $this->getConfig();
        $aFields = $aConfig['FIELDS'];
        foreach ($aFields as $oField) {
            $this->loadEditViewDataSetDefaultValue($oField, $oItem);
            $this->loadEditViewDataSetRequired($oField);
            $this->loadEditViewDataSetReadOnly($oField, $oItem);
        }

        $this->data['aFieldSets'] = $this->loadEditViewDataSetFieldsets($aFields);
        $this->data['oItem']      = $oItem;

        //  @deprecated (Pablo 15/02/2021) - kept for backwards compatability
        $this->data['item'] = $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "default" property for a field in the edit view
     *
     * @param Field         $oField The field being set
     * @param Resource|null $oItem  The item being edited
     */
    protected function loadEditViewDataSetDefaultValue(Field &$oField, Resource $oItem = null)
    {
        $sKey = preg_replace('/\[\]$/', '', $oField->getKey());

        if ($oField->getDefault() instanceof \Closure) {

            $oField->setDefault(call_user_func($oField->getDefault(), $oItem));

        } elseif (!is_null($oItem) && property_exists($oItem, $sKey)) {

            if ($oItem->{$sKey} instanceof Resource\ExpandableField) {
                $oField->setDefault($oItem->{$sKey}->data);
            } else {
                $oField->setDefault($oItem->{$sKey});
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "required" property for a field in the edit view
     *
     * @param Field $oField The field being set
     */
    protected function loadEditViewDataSetRequired(Field &$oField)
    {
        if (!property_exists($oField, 'required')) {
            $oField->required = in_array(FormValidation::RULE_REQUIRED, $oField->getValidation());
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the "required" property for a field in the edit view
     *
     * @param Field         $oField The field being set
     * @param Resource|null $oItem  The item being edited
     *
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewDataSetReadOnly(Field &$oField, Resource $oItem = null)
    {
        $aConfig = $this->getConfig();
        if (!is_null($oItem)) {
            $oField->readonly = in_array($oField->getKey(), $aConfig['EDIT_READONLY_FIELDS']);
        } else {
            $oField->readonly = in_array($oField->getKey(), $aConfig['CREATE_READONLY_FIELDS']);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Organises fields into their fieldsets
     *
     * @param Field[] $aFields The fields being organised
     *
     * @return Field[]
     * @throws FactoryException
     * @throws NailsException
     */
    protected function loadEditViewDataSetFieldsets(array $aFields): array
    {
        //  Extract the fields into fieldsets
        $aConfig    = $this->getConfig();
        $aFieldSets = array_combine(
            $aConfig['FIELDSET_ORDER'],
            array_pad(
                [],
                count($aConfig['FIELDSET_ORDER']),
                []
            )
        );

        //  Organsie fields into the fieldsets
        /** @var Field $oField */
        foreach ($aFields as $oField) {

            if (empty($oItem)) {
                if (in_array($oField->getKey(), $aConfig['CREATE_IGNORE_FIELDS'])) {
                    continue;
                }
            } else {
                if (in_array($oField->getKey(), $aConfig['EDIT_IGNORE_FIELDS'])) {
                    continue;
                }
            }

            $sFieldSet = $oField->getFieldset();

            if (!array_key_exists($sFieldSet, $aFieldSets)) {
                $aFieldSets[$sFieldSet] = [];
            }

            $aFieldSets[$sFieldSet][] = $oField;
        }

        return array_filter($aFieldSets);
    }

    // --------------------------------------------------------------------------

    /**
     * Extract data from post variable
     *
     * @return array
     */
    protected function getPostObject(): array
    {
        $aConfig = $this->getConfig();
        $oModel  = static::getModel();

        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');

        $aOut = [];

        /** @var Field $oField */
        foreach ($aConfig['FIELDS'] as $oField) {

            //  Support array type keys
            $sKey = preg_replace('/\[\]$/', '', $oField->getKey());

            if (in_array($sKey, $aConfig['EDIT_IGNORE_FIELDS'])) {
                continue;
            }

            $aOut[$sKey] = $oInput->post($sKey);

            if ($oField->isAllowNull() && empty($aOut[$sKey])) {
                $aOut[$sKey] = null;
            }

            //  Type casting
            switch ($oField->getType()) {
                case Form::FIELD_BOOLEAN:
                    $aOut[$sKey] = (bool) $aOut[$sKey];
                    break;

                //  @todo (Pablo - 2020-01-16) - This done to support CSV items (e.g. MySQL `SET`s) - feels a bit hack/arbitrary
                case Form::FIELD_DROPDOWN_MULTIPLE:
                    $aOut[$sKey] = is_array($aOut[$sKey]) ? implode(',', $aOut[$sKey]) : null;
                    break;

                //  @todo (Pablo 2021-07-28) - Refactor this method so that the model can format things, i.e this won't catch cms_widget type fields
                case Form::FIELD_JSON:
                    $aOut[$sKey] = $aOut[$sKey] === ''
                        ? null
                        : $aOut[$sKey];
                    break;
            }
        }

        if (classUses($oModel, Localised::class)) {
            $iExistingId = $oUri->segment(5);
            if ($oUri->segment(5)) {
                $aOut['id'] = $oUri->segment(5);
            }
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the item being requested, thorwing a 404 if it's not found
     *
     * @param array   $aData    Data to pass to the getById method
     * @param integer $iSegment The URL segment contianing the ID
     *
     * @return mixed
     * @throws FactoryException
     */
    protected function getItem(array $aData = [], int $iSegment = null, bool $bIncludeDeleted = false, bool $b404 = true)
    {
        $iSegment = $iSegment ?? 5;

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');

        $oModel  = $this->getModel();
        $iItemId = (int) $oUri->segment($iSegment);

        if (classUses($oModel, Localised::class) && $oUri->segment($iSegment + 1)) {
            $aData['USE_LOCALE'] = $oUri->segment($iSegment + 1);
        }

        if (!array_key_exists('where', $aData)) {
            $aData['where'] = [];
        }

        $aData['where'][] = [$oModel->getTableAlias() . '.' . $oModel->getColumn('id'), $iItemId];

        if ($bIncludeDeleted) {
            $oModel->includeDeleted();
        }

        $aItems = $oModel
            ->skipCache()
            ->getAll($aData);

        $oItem = reset($aItems);

        if ($b404 && empty($oItem)) {
            show404();
        }

        return $oItem;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Create" header button is enabled
     *
     * @return bool
     */
    protected static function isCreateButtonEnabled(): bool
    {
        return static::CONFIG_CAN_CREATE
            && static::userCan(static::EDIT_MODE_CREATE);
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "View" row button is enabled
     *
     * @param Resource $oItem The row item
     *
     * @return bool
     */
    protected static function isViewButtonEnabled($oItem): bool
    {
        return static::CONFIG_CAN_VIEW
            && (!empty($oItem->url) || (method_exists($oItem, 'getUrl') && !empty($oItem->getUrl())));
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Edit" row button is enabled
     *
     * @param Resource|null $oItem The row item
     *
     * @return bool
     */
    protected static function isEditButtonEnabled($oItem = null): bool
    {
        return static::CONFIG_CAN_EDIT
            && static::userCan(static::EDIT_MODE_EDIT);
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Delete" row button is enabled
     *
     * @param Resource|null $oItem The row item
     *
     * @return bool
     */
    protected static function isDeleteButtonEnabled($oItem = null): bool
    {
        return static::CONFIG_CAN_DELETE
            && static::userCan(static::EDIT_MODE_DELETE);
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Restore" button is enabled
     *
     * @param Resource|null $oItem The row item
     *
     * @return bool
     */
    protected static function isRestoreButtonEnabled($oItem = null): bool
    {
        return static::CONFIG_CAN_RESTORE
            && static::userCan(static::EDIT_MODE_RESTORE)
            && !static::getModel()->isDestructiveDelete();
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Copy" row button is enabled
     *
     * @param Resource|null $oItem The row item
     *
     * @return bool
     */
    protected static function isCopyButtonEnabled($oItem = null): bool
    {
        return static::CONFIG_CAN_COPY
            && static::userCan(static::EDIT_MODE_COPY)
            && classUses(static::getModel(), Copyable::class)
            && static::isEditButtonEnabled($oItem)
            && static::isCreateButtonEnabled();
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the "Sort" button is enabled
     *
     * @param Resource|null $oItem The row item
     *
     * @return bool
     */
    protected static function isSortButtonEnabled(): bool
    {
        return static::CONFIG_CAN_SORT
            && static::userCan(static::EDIT_MODE_SORT)
            && classUses(static::getModel(), Sortable::class)
            && static::isEditButtonEnabled();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the user to the index pagel if a referrer is available then go there instead
     * This is useful for returning the user to a filtered view
     *
     * @throws FactoryException
     */
    protected function returnToIndex(): void
    {
        /** @var Input $oInput */
        $oInput    = Factory::service('Input');
        $sReferrer = $oInput->server('HTTP_REFERER');

        if (!empty($sReferrer)) {
            redirect($sReferrer);
        } else {
            redirect($this->getConfig()['BASE_URL']);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a header button for the index context
     *
     * @param string|string[] $mUrl          The button's URL
     * @param string          $sLabel        The button's label
     * @param string|null     $sContext      The button's context
     * @param string|null     $sConfirmTitle If a confirmation is required, the title to use
     * @param string|null     $sConfirmBody  If a confirmation is required, the body to use
     */
    protected function addIndexHeaderButton(
        string $mUrl,
        string $sLabel,
        string $sContext = null,
        string $sConfirmTitle = null,
        string $sConfirmBody = null
    ): void {
        $this->aConfig['INDEX_HEADER_BUTTONS'][] = [
            'url'           => $mUrl,
            'label'         => $sLabel,
            'context'       => $sContext,
            'confirm_title' => $sConfirmTitle,
            'confirm_body'  => $sConfirmBody,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a header button for the edit context
     *
     * @param string|string[] $mUrl          The button's URL
     * @param string          $sLabel        The button's label
     * @param string|null     $sContext      The button's context
     * @param string|null     $sConfirmTitle If a confirmation is required, the title to use
     * @param string|null     $sConfirmBody  If a confirmation is required, the body to use
     */
    protected function addEditHeaderButton(
        $mUrl,
        string $sLabel,
        string $sContext = null,
        string $sConfirmTitle = null,
        string $sConfirmBody = null
    ): void {
        $this->aConfig['EDIT_HEADER_BUTTONS'][] = [
            'url'           => $mUrl,
            'label'         => $sLabel,
            'context'       => $sContext,
            'confirm_title' => $sConfirmTitle,
            'confirm_body'  => $sConfirmBody,
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Adds buttons to the header area
     *
     * @param array $aButtons the buttons to add
     */
    protected static function addHeaderButtons(array $aButtons): void
    {
        foreach ($aButtons as $aButton) {
            static::addHeaderButton(
                getFromArray(['url', 0], $aButton),
                getFromArray(['label', 1], $aButton),
                getFromArray(['context', 2], $aButton),
                getFromArray(['confirm_title', 3], $aButton),
                getFromArray(['confirm_body', 4], $aButton)
            );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a single header button
     *
     * @param string|string[] $mUrl          The button's URL
     * @param string          $sLabel        The button's label
     * @param string|null     $sContext      The button's context
     * @param string|null     $sConfirmTitle If a confirmation is required, the title to use
     * @param string|null     $sConfirmBody  If a confirmation is required, the body to use
     */
    protected static function addHeaderButton(
        $mUrl,
        string $sLabel,
        string $sContext = null,
        string $sConfirmTitle = null,
        string $sConfirmBody = null
    ) {
        Helper::addHeaderButton(
            $mUrl,
            $sLabel,
            $sContext,
            $sConfirmTitle,
            $sConfirmBody
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an appropriate action to the ChangeLog
     *
     * @param string               $sMode    The update mode
     * @param Resource\Entity      $oItem    The current version of the item
     * @param Resource\Entity|null $oOldItem the old version of the item
     *
     * @throws FactoryException
     * @throws ModelException
     */
    protected function addToChangeLog(
        string $sMode,
        Resource\Entity $oItem,
        Resource\Entity $oOldItem = null
    ): void {

        if (static::CHANGELOG_ENABLED) {
            switch ($sMode) {
                case static::EDIT_MODE_CREATE:
                    $this->addToChangeLogCreate($oItem);
                    break;
                case static::EDIT_MODE_EDIT:
                    $this->addToChangeLogEdit($oItem, $oOldItem);
                    break;
                case static::EDIT_MODE_DELETE:
                    $this->addToChangeLogDelete($oItem);
                    break;
                case static::EDIT_MODE_RESTORE:
                    $this->addToChangeLogRestore($oItem);
                    break;
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Create" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The item which was created
     *
     * @throws FactoryException
     * @throws ModelException
     */
    protected function addToChangeLogCreate(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'created',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id,
                $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an "Update" item to the ChangeLog
     *
     * @param Resource\Entity      $oItem    The new item
     * @param Resource\Entity|null $oOldItem The old item
     */
    protected function addToChangeLogEdit(Resource\Entity $oItem, Resource\Entity $oOldItem = null): void
    {
        $aNew = $this->changeLogFlattenObject($oItem);
        $aOld = $this->changeLogFlattenObject($oOldItem);

        $aSameKeys    = array_keys(array_intersect_key($aNew, $aOld));
        $aAddedKeys   = array_keys(array_diff_key($aNew, $aOld));
        $aRemovedKeys = array_keys(array_diff_key($aOld, $aNew));

        $aChangeData = [];
        foreach ($aSameKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }
        foreach ($aAddedKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }
        foreach ($aRemovedKeys as $sKey) {
            $aChangeData[$sKey] = [
                getFromArray($sKey, $aOld),
                getFromArray($sKey, $aNew),
            ];
        }

        foreach ($aChangeData as $sKey => $aValues) {

            [$sOldValue, $sNewValue] = $aValues;
            $bForce = false;
            if (in_array($sKey, static::CHANGELOG_FIELDS_REDACT)) {
                $bForce    = $sOldValue !== $sNewValue;
                $sOldValue = '[REDACTED]';
                $sNewValue = '[REDACTED]';
            }

            $this
                ->oChangeLogModel
                ->add(
                    'updated',
                    'a',
                    static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                    $oItem->id,
                    $oItem->label ?? 'Item #' . $oItem->id,
                    $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id,
                    $sKey,
                    $sOldValue,
                    $sNewValue,
                    false,
                    $bForce
                );
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Delete" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The deleted item
     */
    protected function addToChangeLogDelete(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'deleted',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a "Restore" item to the ChangeLog
     *
     * @param Resource\Entity $oItem The restored item
     */
    protected function addToChangeLogRestore(Resource\Entity $oItem): void
    {
        $this
            ->oChangeLogModel
            ->add(
                'restored',
                'a',
                static::CHANGELOG_ENTITY_NAME ?? get_class($oItem),
                $oItem->id,
                $oItem->label ?? 'Item #' . $oItem->id,
                $this->aConfig['BASE_URL'] . '/edit/' . $oItem->id
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Flattens the object suitable for the change log
     *
     * @param mixed        $mItem   The item to flattem
     * @param string       $sPrefix The prefix to give the key
     * @param null|integer $iDepth  The depth of the array
     *
     * @return array
     */
    protected function changeLogFlattenObject($mItem, string $sPrefix = '', $iDepth = null): array
    {
        $sPrefix = $sPrefix ? $sPrefix . '.' : '';
        $aOut    = [];

        foreach ($mItem as $sKey => $mValue) {

            if (in_array($sKey, static::CHANGELOG_FIELDS_IGNORE)) {
                continue;
            }

            if ($mValue instanceof Resource\ExpandableField) {

                foreach ($mValue->data as $iIndex => $mArrayValue) {
                    $aOut = array_merge(
                        $aOut,
                        $this->changeLogFlattenObject($mValue->data, $sPrefix . $sKey, $iIndex)
                    );
                }

            } elseif (is_object($mValue)) {

                $aOut = array_merge($aOut, $this->changeLogFlattenObject($mValue, $sPrefix . $sKey));

            } elseif (is_array($mValue)) {

                foreach ($mValue as $iIndex => $mArrayValue) {
                    $aOut = array_merge(
                        $aOut,
                        $this->changeLogFlattenObject($mValue, $sPrefix . $sKey, $iIndex)
                    );
                }

            } else {
                $aOut[$sPrefix . ($iDepth !== null ? $iDepth : '') . $sKey] = (string) $mValue;
            }
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new index row button
     *
     * @param \Closure|string|null   $mUrl        The button's URL
     * @param \Closure|string|null   $mLabel      The button's label
     * @param \Closure|string|null   $mClass      The buttons classes
     * @param \Closure|string[]|null $mAttr       Any additional attributed for the button
     * @param \Closure|string|null   $mPermission The permission required to render the button
     * @param \Closure|bool|null     $mEnabled    Whether the button is enabled or not
     */
    protected function addIndexRowButton(
        $mUrl,
        $mLabel,
        $mClass = null,
        $mAttr = null,
        $mPermission = null,
        $mEnabled = null
    ): self {

        $this->aConfig['INDEX_ROW_BUTTONS'][] = [
            'url'        => $mUrl,
            'label'      => $mLabel,
            'class'      => $mClass ?? 'btn-default',
            'attr'       => $mAttr,
            'permission' => $mPermission,
            'enabled'    => $mEnabled,
        ];

        return $this;
    }
}
