<?php

namespace Nails\Admin\Service\Dashboard;

use Nails\Admin\Constants;
use Nails\Admin\Interfaces;
use Nails\Admin\Resource;
use Nails\Auth\Resource\User;
use Nails\Components;
use Nails\Factory;

/**
 * Class Widget
 *
 * @package Nails\Admin\Service\Dashboard
 */
class Widget
{
    /** @var string[] */
    protected $aWidgets = [];

    // --------------------------------------------------------------------------

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->aWidgets = $this->discoverWidgets();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available generators
     *
     * @return string[]
     */
    private function discoverWidgets(): array
    {
        $aWidgets = [];
        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\Dashboard\\Widget')
                ->whichImplement(Interfaces\Dashboard\Widget::class)
                ->whichCanBeInstantiated();

            foreach ($aClasses as $sClass) {
                $aWidgets[] = $sClass;
            }
        }

        return $aWidgets;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns discovered widgets
     *
     * @return string[]
     */
    public function getAll(): array
    {
        return $this->aWidgets;
    }

    // --------------------------------------------------------------------------

    public function getBySlug(string $sSlug, array $aConfig = []): ?Interfaces\Dashboard\Widget
    {
        foreach ($this->getAll() as $sClass) {
            if ($sSlug === $sClass) {
                return new $sClass($aConfig);
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns instantiated and configured widgets for a given user
     *
     * @param User|null $oUser The suer to fetch for
     *
     * @return Resource\Dashboard\Widget[]
     */
    public function getWidgetsForUser(?User $oUser = null): array
    {
        /** @var \Nails\Admin\Model\Dashboard\Widget $oModel */
        $oModel = Factory::model('DashboardWidget', Constants::MODULE_SLUG);

        $oUser = $oUser ?? activeUser();

        if (empty($oUser)) {
            return [];
        }

        return $oModel->getAll([
            'where' => [
                [$oModel->getColumnCreatedBy(), $oUser->id],
            ],
        ]);
    }
}
