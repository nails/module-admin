<?php

namespace Nails\Admin\Admin\Ui\Header\Button\Search\Section;

use Nails\Admin\Constants;
use Nails\Admin\Interfaces\Ui\Header\Button\Search\Section;
use Nails\Admin\Service\Controller;
use Nails\Factory;

class Sidebar implements Section
{
    protected Controller $oController;

    // --------------------------------------------------------------------------

    public function __construct()
    {
        $this->oController = Factory::service('Controller', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    public function getLabel(): string
    {
        return 'Admin Sidebar';
    }

    // --------------------------------------------------------------------------

    public function getResults(string $sQuery): array
    {
        $aResults = [];
        foreach ($this->oController->getSidebarGroups() as $oGroup) {

            $aGroupKeywords = array_merge(
                [$oGroup->getLabel()],
                $oGroup->getKeywords()
            );

            foreach ($oGroup->getActions() as $oAction) {
                $sActionKeywords = implode(' ', array_unique(
                    array_filter(
                        array_merge(
                            $aGroupKeywords,
                            [$oAction->getLabel()],
                            $oAction->getKeywords()
                        )
                    )
                ));

                if (stripos($sActionKeywords, $sQuery) !== false) {
                    /** @var \Nails\Admin\Factory\Ui\Header\Button\Search\Result $oResult */
                    $oResult = Factory::factory('UiHeaderButtonSearchResult', Constants::MODULE_SLUG);
                    /** @var \Nails\Admin\Factory\Ui\Header\Button\Search\Result\Action $oResultAction */
                    $oResultAction = Factory::factory('UiHeaderButtonSearchResultAction', Constants::MODULE_SLUG);
                    $oResultAction
                        ->setIconClass('fa-arrow-right')
                        ->setLabel('Go')
                        ->setUrl($oAction->getUrl());

                    $oResult
                        ->setIconClass($oGroup->getIcon())
                        ->setLabel($oAction->getLabel())
                        ->setDescription($oGroup->getLabel())
                        ->setActions([$oResultAction]);

                    $aResults[] = $oResult;
                }
            }

        }

        return $aResults;
    }

    // --------------------------------------------------------------------------

    public static function getOrder(): int
    {
        return 0;
    }
}
