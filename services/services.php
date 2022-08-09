<?php

use Nails\Admin\Factory;
use Nails\Admin\Model;
use Nails\Admin\Resource;
use Nails\Admin\Service;
use Nails\Common\Factory\Component;

return [
    'services'  => [
        'Controller'      => function (): Service\Controller {
            if (class_exists('\App\Admin\Service\Controller')) {
                return new \App\Admin\Service\Controller();
            } else {
                return new Service\Controller();
            }
        },
        'DataExport'      => function (): Service\DataExport {
            if (class_exists('\App\Admin\Service\DataExport')) {
                return new \App\Admin\Service\DataExport();
            } else {
                return new Service\DataExport();
            }
        },
        'DashboardWidget' => function (): Service\Dashboard\Widget {
            if (class_exists('\App\Admin\Service\Dashboard\Widget')) {
                return new \App\Admin\Service\Dashboard\Widget();
            } else {
                return new Service\Dashboard\Widget();
            }
        },
        'Permission'      => function (): Service\Permission {
            if (class_exists('\App\Admin\Service\Permission')) {
                return new \App\Admin\Service\Permission();
            } else {
                return new Service\Permission();
            }
        },
    ],
    'models'    => [
        'Admin'           => function (): Model\Admin {
            if (class_exists('\App\Admin\Model\Admin')) {
                return new \App\Admin\Model\Admin();
            } else {
                return new Model\Admin();
            }
        },
        'ChangeLog'       => function (): Model\ChangeLog {
            if (class_exists('\App\Admin\Model\ChangeLog')) {
                return new \App\Admin\Model\ChangeLog();
            } else {
                return new Model\ChangeLog();
            }
        },
        'DashboardWidget' => function (): Model\Dashboard\Widget {
            if (class_exists('\App\Admin\Model\Dashboard\Widget')) {
                return new \App\Admin\Model\Dashboard\Widget();
            } else {
                return new Model\Dashboard\Widget();
            }
        },
        'Export'          => function (): Model\Export {
            if (class_exists('\App\Admin\Model\Export')) {
                return new \App\Admin\Model\Export();
            } else {
                return new Model\Export();
            }
        },
        'Help'            => function (): Model\Help {
            if (class_exists('\App\Admin\Model\Help')) {
                return new \App\Admin\Model\Help();
            } else {
                return new Model\Help();
            }
        },
        'Note'            => function (): Model\Note {
            if (class_exists('\App\Admin\Model\Note')) {
                return new \App\Admin\Model\Note();
            } else {
                return new Model\Note();
            }
        },
        'Session'         => function (): Model\Session {
            if (class_exists('\App\Admin\Model\Session')) {
                return new \App\Admin\Model\Session();
            } else {
                return new Model\Session();
            }
        },
    ],
    'resources' => [
        'ChangeLog'        => function ($mObj): Resource\ChangeLog {
            if (class_exists('\App\Admin\Resource\ChangeLog')) {
                return new \App\Admin\Resource\ChangeLog($mObj);
            } else {
                return new Resource\ChangeLog($mObj);
            }
        },
        'DashboardWidget'  => function ($mObj): Resource\Dashboard\Widget {
            if (class_exists('\App\Admin\Resource\Dashboard\Widget')) {
                return new \App\Admin\Resource\Dashboard\Widget($mObj);
            } else {
                return new Resource\Dashboard\Widget($mObj);
            }
        },
        'DataExportFormat' => function ($mObj): Resource\DataExport\Format {
            if (class_exists('\App\Admin\Resource\DataExport\Format')) {
                return new \App\Admin\Resource\DataExport\Format($mObj);
            } else {
                return new Resource\DataExport\Format($mObj);
            }
        },
        'DataExportSource' => function ($mObj): Resource\DataExport\Source {
            if (class_exists('\App\Admin\Resource\DataExport\Source')) {
                return new \App\Admin\Resource\DataExport\Source($mObj);
            } else {
                return new Resource\DataExport\Source($mObj);
            }
        },
        'Note'             => function ($mObj): Resource\Note {
            if (class_exists('\App\Admin\Resource\Note')) {
                return new \App\Admin\Resource\Note($mObj);
            } else {
                return new Resource\Note($mObj);
            }
        },
        'Session'          => function ($mObj): Resource\Session {
            if (class_exists('\App\Admin\Resource\Session')) {
                return new \App\Admin\Resource\Session($mObj);
            } else {
                return new Resource\Session($mObj);
            }
        },
    ],
    'factories' => [
        'DefaultControllerSortSection' => function (string $sLabel = '', array $aItems = []): Factory\DefaultController\Sort\Section {
            if (class_exists('\App\Admin\Factory\Nav')) {
                return new \App\Admin\Factory\DefaultController\Sort\Section($sLabel, $aItems);
            } else {
                return new Factory\DefaultController\Sort\Section($sLabel, $aItems);
            }
        },
        'EmailDataExportSuccess'       => function (): Factory\Email\DataExport\Success {
            if (class_exists('\App\Admin\Factory\Email\DataExport\Success')) {
                return new \App\Admin\Factory\Email\DataExport\Success();
            } else {
                return new Factory\Email\DataExport\Success();
            }
        },
        'EmailDataExportFail'          => function (): Factory\Email\DataExport\Fail {
            if (class_exists('\App\Admin\Factory\Email\DataExport\Fail')) {
                return new \App\Admin\Factory\Email\DataExport\Fail();
            } else {
                return new Factory\Email\DataExport\Fail();
            }
        },
        'HelperDynamicTable'           => function (): Factory\Helper\DynamicTable {
            if (class_exists('\App\Admin\Helper\DynamicTable')) {
                return new \App\Admin\Helper\DynamicTable();
            } else {
                return new Factory\Helper\DynamicTable();
            }
        },
        'Nav'                          => function (
            string $sLabel = '',
            string $sIcon = '',
            string $sUrl = '',
            array $aActions = [],
            array $aKeywords = [],
            bool $bIsOpen = false
        ): Factory\Nav {
            if (class_exists('\App\Admin\Factory\Nav')) {
                return new \App\Admin\Factory\Nav($sLabel, $sIcon, $sUrl, $aActions, $aKeywords, $bIsOpen);
            } else {
                return new Factory\Nav($sLabel, $sIcon, $sUrl, $aActions, $aKeywords, $bIsOpen);
            }
        },
        'NavAction'                    => function (
            string $sLabel,
            string $sUrl,
            array $aAlerts = [],
            int $iOrder = null,
            array $aKeywords = []
        ): Factory\Nav\Action {
            if (class_exists('\App\Admin\Factory\Nav\Action')) {
                return new \App\Admin\Factory\Nav\Action($sLabel, $sUrl, $aAlerts, $iOrder, $aKeywords);
            } else {
                return new Factory\Nav\Action($sLabel, $sUrl, $aAlerts, $iOrder, $aKeywords);
            }
        },
        'NavAlert'                     => function (): Factory\Nav\Alert {
            if (class_exists('\App\Admin\Factory\Nav\Alert')) {
                return new \App\Admin\Factory\Nav\Alert();
            } else {
                return new Factory\Nav\Alert();
            }
        },
        'PermissionGroup'              => function (Component $oComponent, array $aPermissions = []): Factory\Permission\Group {
            if (class_exists('\App\Admin\Factory\Permission\Group')) {
                return new \App\Admin\Factory\Permission\Group($oComponent, $aPermissions);
            } else {
                return new Factory\Permission\Group($oComponent, $aPermissions);
            }
        },
        'QuickActionAction'            => function (string $sLabel, string $sSubLabel, string $sUrl): Factory\QuickAction\Action {
            if (class_exists('\App\Admin\Factory\QuickAction\Action')) {
                return new \App\Admin\Factory\QuickAction\Action($sLabel, $sSubLabel, $sUrl);
            } else {
                return new Factory\QuickAction\Action($sLabel, $sSubLabel, $sUrl);
            }
        },
        'DataExportSourceResponse'     => function () {
            if (class_exists('\App\Admin\DataExport\SourceResponse')) {
                return new \App\Admin\DataExport\SourceResponse();
            } else {
                return new \Nails\Admin\DataExport\SourceResponse();
            }
        },
        'IndexFilter'                  => function (): Factory\IndexFilter {
            if (class_exists('\App\Admin\Factory\IndexFilter')) {
                return new \App\Admin\Factory\IndexFilter();
            } else {
                return new Factory\IndexFilter();
            }
        },
        'IndexFilterOption'            => function (): Factory\IndexFilter\Option {
            if (class_exists('\App\Admin\Factory\IndexFilter\Option')) {
                return new \App\Admin\Factory\IndexFilter\Option();
            } else {
                return new Factory\IndexFilter\Option();
            }
        },
        'ModelFieldDynamicTable'       => function (): Factory\Model\Field\DynamicTable {
            if (class_exists('\App\Admin\Factory\Model\Field\DynamicTable')) {
                return new \App\Admin\Factory\Model\Field\DynamicTable();
            } else {
                return new Factory\Model\Field\DynamicTable();
            }
        },
        'Setting'                      => function (): Factory\Setting {
            if (class_exists('\App\Admin\Factory\Setting')) {
                return new \App\Admin\Factory\Setting();
            } else {
                return new Factory\Setting();
            }
        },
    ],
];
