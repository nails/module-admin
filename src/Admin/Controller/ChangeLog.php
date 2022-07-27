<?php

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Controller\DefaultController;
use Nails\Admin\Factory\IndexFilter;
use Nails\Admin\Helper;
use Nails\Auth;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Helper\ArrayHelper;
use Nails\Common\Service\Database;
use Nails\Factory;

/**
 * Class ChangeLog
 *
 * @package Nails\Admin\Admin
 */
class ChangeLog extends DefaultController
{
    const CONFIG_MODEL_NAME     = 'ChangeLog';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;
    const CONFIG_SIDEBAR_GROUP  = 'Logs';
    const CONFIG_SIDEBAR_FORMAT = 'Browse Change Logs';
    const CONFIG_SORT_OPTIONS   = [
        'Created' => 'created',
    ];
    const CONFIG_SORT_DIRECTION = self::SORT_DESCENDING;
    const CONFIG_INDEX_FIELDS   = [
        'Changes' => null,
        'Date'    => 'created',
    ];
    const CONFIG_INDEX_DATA     = [
        'expand' => [
            'user',
        ],
    ];
    const CONFIG_CAN_CREATE     = false;
    const CONFIG_CAN_DELETE     = false;
    const CONFIG_CAN_EDIT       = false;
    const CONFIG_CAN_VIEW       = false;
    const CONFIG_PERMISSION     = 'admin:logs:change';

    // --------------------------------------------------------------------------

    /**
     * ChangeLog constructor.
     *
     * @throws \Nails\Common\Exception\NailsException
     */
    public function __construct()
    {
        parent::__construct();
        $this->aConfig['INDEX_FIELDS']['Changes'] = function (\Nails\Admin\Resource\ChangeLog $oLog) {
            return $oLog->getSentence();
        };

        $this->addIndexRowButton(
            'view/{{id}}',
            'View Changes',
            'btn-default fancybox',
            null,
            null,
            fn(\Nails\Admin\Resource\ChangeLog $oItem) => (bool) $oItem->getChangesAsList()
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Adds filters to the index page
     *
     * @return array
     * @throws FactoryException
     * @throws ModelException
     */
    protected function indexDropdownFilters(): array
    {
        return array_merge(
            parent::indexDropdownFilters(),
            array_filter([
                $this->filterByUser(),
                $this->filterByEntity(),
            ])
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an IndexFilter for the users who have made changes
     *
     * @return IndexFilter|null
     * @throws FactoryException
     * @throws ModelException
     */
    protected function filterByUser(): ?IndexFilter
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \Nails\Admin\Model\ChangeLog $oChangeLogModel */
        $oChangeLogModel = static::getModel();
        /** @var Auth\Model\User $oUserModel */
        $oUserModel = Factory::model('User', Auth\Constants::MODULE_SLUG);

        $aUserIds = $oDb
            ->select('DISTINCT(user_id)')
            ->get($oChangeLogModel->getTableName())->result();

        $aUsers = $oUserModel->getByIds(arrayExtractProperty($aUserIds, 'user_id'));

        if (empty($aUsers) || count($aUsers) === 1) {
            return null;
        }

        /** @var IndexFilter $oFilter */
        $oFilter = Factory::factory('IndexFilter', Constants::MODULE_SLUG);
        $oFilter
            ->setLabel('User')
            ->setColumn('user_id')
            ->addOption('Everyone')
            ->addOptions(array_map(function (Auth\Resource\User $oUser) {
                /** @var IndexFilter\Option $oOption */
                $oOption = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOption
                    ->setLabel(sprintf(
                        '#%s - %s (%s)',
                        $oUser->id,
                        $oUser->name,
                        $oUser->email
                    ))
                    ->setValue($oUser->id);

                return $oOption;
            }, $aUsers));

        return $oFilter;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an IndexFilter for the various types of entities in the changelog
     *
     * @return IndexFilter|null
     * @throws FactoryException
     * @throws ModelException
     */
    protected function filterByEntity(): ?IndexFilter
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var \Nails\Admin\Model\ChangeLog $oChangeLogModel */
        $oChangeLogModel = static::getModel();

        $aEntities = $oDb
            ->select('DISTINCT(item)')
            ->get($oChangeLogModel->getTableName())->result();

        $aEntities = arrayExtractProperty($aEntities, 'item');

        if (empty($aEntities) || count($aEntities) === 1) {
            return null;
        }

        $aSortedEntities = [];
        foreach ($aEntities as $sEntity) {
            if (class_exists($sEntity) && classImplements($sEntity, \Nails\Admin\Interfaces\ChangeLog::class)) {
                $aSortedEntities[$sEntity] = call_user_func($sEntity . '::getChageLogTypeLabel');
            } else {
                $aSortedEntities[$sEntity] = $sEntity;
            }
        }

        sort($aSortedEntities);

        /** @var IndexFilter $oFilter */
        $oFilter = Factory::factory('IndexFilter', Constants::MODULE_SLUG);
        $oFilter
            ->setLabel('Type')
            ->setColumn('item')
            ->addOption('All')
            ->addOptions(array_map(function (string $sValue, string $sLabel) {

                /** @var IndexFilter\Option $oOption */
                $oOption = Factory::factory('IndexFilterOption', Constants::MODULE_SLUG);
                $oOption
                    ->setLabel($sLabel)
                    ->setValue($sValue);

                return $oOption;
            }, array_keys($aSortedEntities), $aSortedEntities));

        return $oFilter;
    }

    // --------------------------------------------------------------------------

    public function view()
    {
        $this->data['oItem'] = $this->getItem();
        Helper::loadView('view');
    }
}
