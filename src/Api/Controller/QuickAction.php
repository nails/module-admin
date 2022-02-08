<?php

/**
 * Admin API end points: QuickAction
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Interfaces\QuickAction\Action;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\NailsException;
use Nails\Components;
use Nails\Factory;

/**
 * Class QuickAction
 *
 * @package Nails\Admin\Api\Controller
 */
class QuickAction extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    public function getIndex()
    {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput  = Factory::service('Input');
        $sOrigin = $oInput->get('origin');
        $sQuery  = trim($oInput->get('query'));

        $aDiscoveredActions = $this->discoverActions();
        $aResults           = [];

        foreach ($aDiscoveredActions as $oAction) {

            foreach ($oAction->getActions($sQuery, $sOrigin) as $oAction) {

                if (!$oAction instanceof Action) {
                    throw new NailsException('Action must be an instance of ' . Action::class);
                }

                $aResults[] = (object) [
                    'label'    => $oAction->getLabel(),
                    'sublabel' => $oAction->getSubLabel(),
                    'url'      => $oAction->getUrl(),
                ];
            }
        }

        arraySortMulti($aResults, 'label');
        $aResults = array_values($aResults);

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData($aResults);

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Interfaces\QuickAction[]
     * @throws \Nails\Common\Exception\NailsException
     */
    private function discoverActions(): array
    {
        $aActions = [];

        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('Admin\\QuickAction')
                ->whichImplement(\Nails\Admin\Interfaces\QuickAction::class)
                ->whichCanBeInstantiated();

            foreach ($aClasses as $sClass) {
                $aActions[] = new $sClass();
            }
        }

        return $aActions;
    }
}
