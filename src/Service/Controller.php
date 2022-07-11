<?php

namespace Nails\Admin\Service;

use Nails\Admin\Admin\Controller\Dashboard;
use Nails\Admin\Constants;
use Nails\Admin\Controller\Base;
use Nails\Admin\Controller\DefaultController;
use Nails\Admin\Exception\AdminException;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Model\Admin;
use Nails\Common\Factory\Component;
use Nails\Common\Helper\ArrayHelper;
use Nails\Components;
use Nails\Factory;

class Controller
{
    /**
     * The required namespace for controllers
     */
    const SRC_PATH = 'Admin\\Controller';

    /**
     * Groups which should be at the top, by default
     */
    const GROUP_TOP = [
        'Dashboard',
    ];

    /**
     * Groups which should be at the bottom, by default
     */
    const GROUP_BOTTOM = [
        'Settings',
        'Utilities',
    ];

    // --------------------------------------------------------------------------

    /** @var \Nails\Admin\Interfaces\Controller */
    protected $oRoute;

    // --------------------------------------------------------------------------

    /**
     * @return void
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function handleRoute(): void
    {
        /** @var \Nails\Common\Service\Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Common\Service\Session $oSession */
        $oSession = Factory::service('Session');

        $sComponentSlug  = $oUri->segment(2);
        $sControllerSlug = $oUri->segment(3);
        $sMethod         = $oUri->segment(4, 'index');

        if (empty($sComponentSlug)) {
            $oSession->keepFlashData();
            redirect($this->getDashboardUrl());
        }

        foreach ($this->discover() as $aController) {

            /** @var \Nails\Common\Factory\Component $oComponent */
            [$oComponent, $sClass, $sClassSlug] = array_values($aController);

            if ($oComponent->slugUrl === $sComponentSlug && $sClassSlug === $sControllerSlug) {
                $this->oRoute = new $sClass();
                break;
            }
        }

        if ($this->oRoute && method_exists($this->oRoute, '_remap')) {
            $this->oRoute->_remap();

        } elseif ($this->oRoute && method_exists($this->oRoute, $sMethod)) {
            $this->oRoute->{$sMethod}();

        } else {
            show404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @return array
     * @throws \Nails\Common\Exception\NailsException
     */
    public function getSidebarGroups(): array
    {
        /** @var \Nails\Admin\Factory\Nav[] $aGroups */
        $aGroups = [];

        /**
         * Track icon usage; multiple controllers part of the same group will
         * define an icon, in the case of conflict the most common icon will be
         * used (unless one has been marked as !important).
         */
        $aIcons = [];

        $this
            ->mergeGroups($aGroups, $aIcons, $this->discover())
            ->setIcons($aGroups, $aIcons)
            ->setOpenState($aGroups)
            ->sortGroups($aGroups);

        return array_values($aGroups);
    }

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Interfaces\Controller|null
     */
    public function getRoute(): ?\Nails\Admin\Interfaces\Controller
    {
        return $this->oRoute;
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Admin\Interfaces\Controller $oController
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getViewPathsForController(\Nails\Admin\Interfaces\Controller $oController): array
    {
        $sClass1 = get_class($oController);
        $oComp1  = Components::detectClassComponent($sClass1);

        $sClass2 = array_values(class_parents($oController))[0];
        $oComp2  = Components::detectClassComponent($sClass2);

        $sClass3 = array_values(class_parents($oController))[1];
        $oComp3  = Components::detectClassComponent($sClass3);

        /**
         * Detect the hierarchy controllers under normal circumstances are up
         * to three layers deep, with the top of the hierarchy being Nails\Admin\Controller\Base
         * or Nails\Admin\Controller\DefaultController
         *
         * - {App} -> {Module} -> {Admin}
         *
         * But more typically, they simply extend Nails\Admin\Controller\Base or Nails\Admin\Controller\DefaultController
         * - {App} -> {Admin}
         * - {Module} -> {Admin}
         */

        $aPaths = [];
        $oApp   = Components::getApp();

        // These classes are considered top of the hierarchy
        $aAdminControllers = [
            Base::class,
            DefaultController::class,
        ];

        if (in_array($sClass2, $aAdminControllers) && $oComp1->isApp()) {
            /**
             * {App} -> {Admin}
             */
            $sPartial = $this->generatePartial($oComp1, $sClass1);
            $aPaths[] = [$oComp1->path, 'application', 'modules', 'admin', 'views', $sPartial];

        } elseif (in_array($sClass2, $aAdminControllers) && !$oComp1->isApp()) {
            /**
             * {Module} -> {Admin}
             */
            $sPartial = $this->generatePartial($oComp1, $sClass1);
            $aPaths[] = [$oApp->path, 'application', 'modules', $oComp1->moduleName, 'admin', 'views', $sPartial];
            $aPaths[] = [$oComp1->path, 'admin', 'views', $sPartial];

        } elseif (in_array($sClass3, $aAdminControllers) && $oComp1->isApp()) {
            /**
             * {App} -> {Module} -> {Admin}
             */
            $sPartial = $this->generatePartial($oComp2, $sClass2);
            $aPaths[] = [$oApp->path, 'application', 'modules', $oComp2->moduleName, 'admin', 'views', $sPartial];
            $aPaths[] = [$oComp2->path, 'admin', 'views', $sPartial];

        } else {
            throw new AdminException('Unsupported Controller hierarchy');
        }

        if ($oController instanceof DefaultController) {
            $oAdmin   = Components::getBySlug(Constants::MODULE_SLUG);
            $aPaths[] = [$oAdmin->path, 'admin', 'views', 'DefaultController'];
        }

        /**
         * Tidy up paths before returning:
         * - Trim any trailing directory separators
         * - Ensure a trailing directory separator
         */
        return array_map(
            fn($aPath) => implode(
                DIRECTORY_SEPARATOR,
                array_map(
                    fn($sPath) => rtrim($sPath, DIRECTORY_SEPARATOR),
                    array_merge($aPath, [''])
                )
            ),
            $aPaths
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Common\Factory\Component $oComponent
     * @param string                          $sClass
     *
     * @return string
     */
    protected function generatePartial(Component $oComponent, string $sClass): string
    {
        return preg_replace(
            '/^' . preg_quote(ltrim($oComponent->namespace, '\\') . static::SRC_PATH . '\\', '/') . '/',
            '',
            $sClass
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Admin\Interfaces\Controller $oController
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getControllerRelatedComponents(\Nails\Admin\Interfaces\Controller $oController)
    {
        foreach (array_merge([get_class($oController)], class_parents($oController)) as $sParent) {
            $aComponents[] = Components::detectClassComponent($sParent);
        }

        return ArrayHelper::arrayUniqueMulti($aComponents);
    }

    // --------------------------------------------------------------------------

    /**
     * @return string
     * @throws \ReflectionException
     */
    protected function getDashboardUrl(): string
    {
        $sAppClass = 'App\\Admin\\Controller\\Dashboard';
        if (class_exists($sAppClass)) {
            $sClass = $sAppClass;
        } else {
            $sClass = Dashboard::class;
        };

        $oComponent = Components::detectClassComponent($sClass);

        return sprintf(
            'admin/%s/dashboard',
            $oComponent->slugUrl
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @return array
     * @throws \Nails\Common\Exception\NailsException
     */
    protected function discover(): array
    {
        //  @todo (Pablo 2022-05-03) - Cache this somehow for perf reasons
        $aControllers    = [];
        $aAppControllers = [];

        foreach (Components::available() as $oComponent) {

            $oClasses = $oComponent
                ->findClasses(static::SRC_PATH)
                ->whichImplement(\Nails\Admin\Interfaces\Controller::class)
                ->whichCanBeInstantiated();

            foreach ($oClasses as $sClass) {

                if ($oComponent->slug !== Components::$sAppSlug) {

                    /**
                     * The app will always be searched first. If any of the controllers it provides
                     * extends the current controller then consider it an override and do not include
                     * this class in the hierarchy.
                     */
                    foreach ($aAppControllers as $sAppController) {
                        if (classExtends($sAppController, $sClass)) {
                            continue 2;
                        }
                    }

                } else {
                    $aAppControllers[] = $sClass;
                }

                $aControllers[] = [
                    'component' => $oComponent,
                    'class'     => $sClass,
                    'slug'      => $this->generateSlug($oComponent, $sClass),
                ];
            }
        }

        return $aControllers;
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Common\Factory\Component $oComponent
     * @param string                          $sClass
     *
     * @return string
     */
    protected function generateSlug(Component $oComponent, string $sClass): string
    {
        $sPattern = '/^' . preg_quote($oComponent->namespace . static::SRC_PATH . '\\', '/') . '/';

        $sSlug = preg_replace($sPattern, '', $sClass);
        $sSlug = str_replace(['/', '\\'], '-', $sSlug);

        return \Nails\Common\Helper\Strings::camelcase_to_dash($sSlug);
    }

    // --------------------------------------------------------------------------

    /**
     * @return bool
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function isUserAuthorised(): bool
    {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');

        $aWhitelistIp = (array) appSetting('whitelist', 'admin');

        if (!empty($aWhitelistIp) && !isIpInRange($oInput->ipAddress(), $aWhitelistIp)) {
            return false;

        } elseif (!isAdmin()) {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * @param array                      $aGroupsOut
     * @param array                      $aIconsOut
     * @param \Nails\Admin\Factory\Nav[] $aGroupsIn
     *
     * @return $this
     */
    protected function mergeGroups(array &$aGroupsOut, array &$aIconsOut, array $aGroupsIn): self
    {
        foreach ($aGroupsIn as $aGroup) {

            [$oComponent, $sClass, $sSlug] = array_values($aGroup);

            $mNavGroups = call_user_func($sClass . '::announce');
            /** @var \Nails\Admin\Factory\Nav[] $aNavGroups */
            $aNavGroups = is_array($mNavGroups) ? $mNavGroups : [$mNavGroups];

            foreach (array_filter($aNavGroups) as $oNavGroup) {

                //  Set the base URL for the group
                $sGroupUrl = 'admin/' . $oComponent->slugUrl . '/' . $sSlug;

                //  Track icon (so we can calculate which one to use)
                if (!array_key_exists($oNavGroup->getLabel(), $aIconsOut)) {
                    $aIconsOut[$oNavGroup->getLabel()] = [];
                }

                $aIconsOut[$oNavGroup->getLabel()][] = $oNavGroup->getIcon();
                $aIconsOut[$oNavGroup->getLabel()]   = array_filter($aIconsOut[$oNavGroup->getLabel()]);

                //  Create or merge group
                if (array_key_exists($oNavGroup->getLabel(), $aGroupsOut)) {
                    foreach ($oNavGroup->getActions(false) as $oAction) {
                        $aGroupsOut[$oNavGroup->getLabel()]->addAction(
                            $oAction->compileUrl($sGroupUrl)
                        );
                    }

                } else {
                    $aGroupsOut[$oNavGroup->getLabel()] = clone $oNavGroup;
                    foreach ($aGroupsOut[$oNavGroup->getLabel()]->getActions() as $oAction) {
                        $oAction->compileUrl($sGroupUrl);
                    }
                }
            }
        }

        $aGroupsOut = array_filter($aGroupsOut, fn(Nav $oNav) => count($oNav->getActions(false)) > 0);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Admin\Factory\Nav[] $aGroups
     * @param array                      $aIcons
     *
     * @return $this
     */
    protected function setIcons(array &$aGroups, array $aIcons): self
    {
        $aIcons = array_map(function ($aIcons) {

            //  Are any icons !important?
            $aImportantIcons = preg_grep('/^(.*)!important$/', $aIcons);
            if (!empty($aImportantIcons)) {
                return rtrim(reset($aImportantIcons), '!important');
            }

            $aIcons = array_count_values($aIcons);
            $aIcons = array_keys($aIcons);
            return reset($aIcons);

        }, $aIcons);

        foreach ($aGroups as $oGroup) {
            ($oGroup->setIcon($aIcons[$oGroup->getLabel()] ?? ''));
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @param array $aGroups
     *
     * @return $this
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function setOpenState(array &$aGroups): self
    {
        $aNavPrefs = $this->getNavPrefs();

        $aGroups = array_map(function (Nav $oGroup) use ($aNavPrefs) {

            $oGroup->setIsOpen($aNavPrefs[$oGroup->getLabel()] ?? false);
            return $oGroup;

        }, $aGroups);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Admin\Factory\Nav[] $aGroups
     *
     * @return $this
     */
    protected function sortGroups(array &$aGroups): self
    {
        ksort($aGroups);

        /**
         * Define the defualt order of items; if the user has any preferences then
         * this'll be handled below.
         */
        $aGroups = array_merge(
            array_filter(
                $aGroups,
                fn($oGroup) => in_array($oGroup->getLabel(), static::GROUP_TOP)
            ),
            array_filter(
                $aGroups,
                fn($oGroup) => !in_array($oGroup->getLabel(), static::GROUP_TOP) && !in_array($oGroup->getLabel(), static::GROUP_BOTTOM)
            ),
            array_filter(
                $aGroups,
                fn($oGroup) => in_array($oGroup->getLabel(), static::GROUP_BOTTOM)
            )
        );

        /**
         * If the user has any sort preferences then respect them. Any items which are not
         * explicitly listed in the prefs will get added to the end; this will catch new or
         * changed sections as admin controllers get added and removed.
         */
        $aNavPrefs = $this->getNavPrefs();
        if (!empty($aNavPrefs)) {

            $aSorted = [];
            foreach ($aNavPrefs as $sLabel => $bState) {
                if (array_key_exists($sLabel, $aGroups)) {
                    $aSorted[$sLabel] = $aGroups[$sLabel];
                    $aGroups[$sLabel] = null;
                }
            }

            $aGroups = array_filter($aGroups);

            foreach ($aGroups as $sLabel => $oGroup) {
                $aSorted[$sLabel] = $oGroup;
            }

            //  Replace the variable (which is passed by ref)
            $aGroups = $aSorted;
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return array
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function getNavPrefs(): array
    {
        /** @var Admin $oAdminModel */
        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);

        $aNavPrefs = [];
        foreach ($oAdminModel->getAdminData('nav_state') ?? [] as $aDatum) {
            [$sLabel, $bState] = $aDatum;
            $aNavPrefs[$sLabel] = $bState;
        }

        return $aNavPrefs;
    }

    // --------------------------------------------------------------------------

    public function determineBaseUrl(string $sClass): string
    {
        $oComponent = Components::detectClassComponent($sClass);

        if ($oComponent->isApp()) {
            $sModule     = $oComponent->slugUrl;
            $sController = $this->generateSlug($oComponent, '\\' . $sClass);

        } else {

            $oApp     = Components::getApp();
            $oClasses = $oApp
                ->findClasses(static::SRC_PATH)
                ->whichExtend($sClass)
                ->whichCanBeInstantiated();

            if ($oClasses->count() > 0) {

                $oClasses->rewind();
                $sModule     = $oApp->slugUrl;
                $sController = $this->generateSlug($oApp, $oClasses->current());

            } else {
                $sModule     = $oComponent->slugUrl;
                $sController = $this->generateSlug($oComponent, '\\' . $sClass);
            }
        }

        return sprintf(
            'admin/%s/%s',
            $sModule,
            $sController
        );
    }
}
