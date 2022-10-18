<?php

/**
 * Admin API end points: dashbaord
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use ApiRouter;
use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Service\Dashboard\Widget;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Service\Uri;
use Nails\Factory;

/**
 * Class Dashboard
 *
 * @package Nails\Admin\Api\Controller
 */
class Dashboard extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    /** @var Widget */
    private $oWidgetService;

    // --------------------------------------------------------------------------

    public function __construct(ApiRouter $oApiRouter)
    {
        parent::__construct($oApiRouter);

        $this->oWidgetService = Factory::service('DashboardWidget', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available dashboard widgets
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ValidationException
     */
    public function getWidget(): Api\Factory\ApiResponse
    {
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData([
                'widgets' => array_values(
                    array_filter(
                        array_map(function (string $sClass) {

                            /** @var \Nails\Admin\Interfaces\Dashboard\Widget $oWidget */
                            $oWidget = new $sClass();
                            return $oWidget->isEnabled() ? (object) [
                                'slug'         => $sClass,
                                'title'        => $oWidget->getTitle(),
                                'description'  => $oWidget->getDescription(),
                                'image'        => $oWidget->getImage(),
                                'padded'       => $oWidget->isPadded(),
                                'configurable' => $oWidget->isConfigurable(),
                            ] : null;

                        }, $this->oWidgetService->getAll())
                    )
                ),
            ]);

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    public function postWidget(): Api\Factory\ApiResponse
    {
        /** @var Uri $oUri */
        $oUri  = Factory::service('Uri');
        $aData = $this->getRequestData();

        if (empty($aData['slug'])) {
            throw new Api\Exception\ApiException(
                '"slug" is required'
            );
        }

        switch ($oUri->segment(5)) {
            case 'body':
                return $this->getBodyHtml($aData['slug'], $aData['config'] ?? []);

            case 'config':
                return $this->getConfigHtml($aData['slug'], $aData['config'] ?? []);

            default:
                throw new Api\Exception\ApiException('Unsupported method');
        }
    }

    // --------------------------------------------------------------------------

    public function putWidget(): Api\Factory\ApiResponse
    {
        /** @var \Nails\Admin\Model\Dashboard\Widget $oModel */
        $oModel       = Factory::model('DashboardWidget', Constants::MODULE_SLUG);
        $aExistingIds = $oModel->getIds([
            'where' => [
                [$oModel->getColumnCreatedBy(), activeUser('id')],
            ],
        ]);

        $aData = $this->getRequestData();
        $aGrid = $aData['grid'] ?? [];

        foreach ($aGrid as &$aItem) {

            $aWidget = [
                'id'     => (int) getFromArray('id', $aItem) ?: null,
                'slug'   => getFromArray('slug', $aItem),
                'x'      => (int) getFromArray('x', $aItem) ?: 0,
                'y'      => (int) getFromArray('y', $aItem) ?: 0,
                'w'      => (int) getFromArray('w', $aItem) ?: 4,
                'h'      => (int) getFromArray('h', $aItem) ?: 1,
                'config' => json_encode(getFromArray('config', $aItem), JSON_OBJECT_AS_ARRAY) ?? [],
            ];

            if (!empty($aWidget['slug'])) {
                if (in_array($aWidget['id'], $aExistingIds)) {
                    $oModel->update($aWidget['id'], $aWidget);
                } else {
                    $aWidget['id'] = $oModel->create($aWidget);
                }
            }

            //  Return the `i` value so that the JS knows what item to update the ID on
            $aWidget['i'] = (int) getFromArray('i', $aItem) ?: 0;
            $aItem        = $aWidget;
        }

        $aTouchedIds = implode(',', arrayExtractProperty($aGrid, 'id'));
        $oModel->deleteWhere(array_filter([
            !empty($aTouchedIds) ? sprintf(
                '`id` NOT IN (%s)',
                $aTouchedIds
            ) : null,
            [$oModel->getColumnCreatedBy(), activeUser('id')],
        ]));

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse->setData($aGrid);
        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    private function getBodyHtml(string $sSlug, array $aConfig): Api\Factory\ApiResponse
    {
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        $oWidget = $this->getWidgetInstance($sSlug, $aConfig);

        $oApiResponse->setData($oWidget->getBody());

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    private function getConfigHtml(string $sSlug, array $aConfig): Api\Factory\ApiResponse
    {
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        $oWidget = $this->getWidgetInstance($sSlug, $aConfig);

        $oApiResponse->setData($oWidget->getConfig());

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    private function getWidgetInstance(string $sSlug, array $aConfig): \Nails\Admin\Interfaces\Dashboard\Widget
    {
        $oWidget = $this->oWidgetService->getBySlug($sSlug, $aConfig);
        if (empty($oWidget)) {
            throw new Api\Exception\ApiException(sprintf(
                '"" is not a valid widget',
                $sSlug
            ));
        }

        return $oWidget;
    }
}
