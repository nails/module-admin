<?php

/**
 * This class renders the admin dashboard
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Interfaces\Dashboard\Alert;
use Nails\Admin\Service\Dashboard\Widget;
use Nails\Common\Service\Asset;
use Nails\Components;
use Nails\Factory;
use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;

/**
 * Class Dashboard
 *
 * @package Nails\Admin\Admin
 */
class Dashboard extends Base
{
    /**
     * Announces this controller's navGroups
     *
     * @return \Nails\Admin\Factory\Nav
     * @throws \Nails\Common\Exception\FactoryException
     */
    public static function announce()
    {
        /** @var Nav $oNavGroup */
        $oNavGroup = Factory::factory('Nav', Constants::MODULE_SLUG);
        $oNavGroup
            ->setLabel('Dashboard')
            ->setIcon('fa-home')
            ->addAction('Dashboard');

        return $oNavGroup;
    }

    // --------------------------------------------------------------------------

    /**
     * The admin homepage/dashboard
     *
     * @return void
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\NailsException
     */
    public function index()
    {
        $this
            ->setTitles(['Dashboard'])
            ->setData('aAlerts', $this->getDashboardAlerts())
            ->setData('aWidgets', $this->getDashboardWidgets())
            ->loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any dashboard alerts
     *
     * @return Alert[]
     * @throws \Nails\Common\Exception\NailsException
     */
    protected function getDashboardAlerts(): array
    {
        $aAlerts = [];

        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\Dashboard\\Alert')
                ->whichImplement(Alert::class);

            foreach ($aClasses as $sClass) {
                /** @var Alert $oAlert */
                $oAlert = new $sClass();
                if ($oAlert->isAlerting()) {
                    $aAlerts[] = $oAlert;
                }
            }
        }

        return $aAlerts;
    }

    // --------------------------------------------------------------------------

    /**
     * @return array
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function getDashboardWidgets(): array
    {
        /** @var Widget $oWidgets */
        $oWidgets = Factory::service('DashboardWidget', Constants::MODULE_SLUG);
        return array_values(
            array_filter(
                array_map(function (\Nails\Admin\Resource\Dashboard\Widget $oWidget) {

                    $sClass = $oWidget->slug;
                    /** @var \Nails\Admin\Interfaces\Dashboard\Widget $oInstance */
                    $oInstance = new $sClass($oWidget->config);

                    return $oInstance->isEnabled() ? [
                        'id'           => $oWidget->id,
                        'slug'         => $oWidget->slug,
                        'title'        => $oInstance->getTitle(),
                        'description'  => $oInstance->getDescription(),
                        'image'        => $oInstance->getImage(),
                        'body'         => $oInstance->getBody(),
                        'padded'       => $oInstance->isPadded(),
                        'configurable' => $oInstance->isConfigurable(),
                        'x'            => $oWidget->x,
                        'y'            => $oWidget->y,
                        'w'            => $oWidget->w,
                        'h'            => $oWidget->h,
                        'config'       => (object) $oWidget->config,
                    ] : null;
                }, $oWidgets->getWidgetsForUser())
            )
        );
    }
}
