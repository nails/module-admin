<?php

/**
 * Admin API end points: CKEditor
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Interfaces\Ui\Header\Button\Create;
use Nails\Admin\Interfaces\Ui\Header\Button\Search\Result;
use Nails\Admin\Interfaces\Ui\Header\Button\Search\Section;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api\Exception\ApiException;
use Nails\Api\Factory\ApiResponse;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Service\HttpCodes;
use Nails\Common\Service\Uri;
use Nails\Factory;

/**
 * Class Ui
 *
 * @package Nails\Admin\Api\Controller
 */
class Ui extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    protected \Nails\Admin\Service\Ui $oUi;
    protected Uri                     $oUri;
    protected HttpCodes               $oHttpCodes;

    // --------------------------------------------------------------------------

    /**
     * @param \ApiRouter $oApiRouter
     *
     * @throws FactoryException
     * @throws NailsException
     * @throws \ReflectionException
     */
    public function __construct(\ApiRouter $oApiRouter)
    {
        parent::__construct($oApiRouter);
        $this->oUi        = Factory::service('Ui', Constants::MODULE_SLUG);
        $this->oUri       = Factory::service('Uri');
        $this->oHttpCodes = Factory::service('HttpCodes');
    }

    // --------------------------------------------------------------------------

    /**
     * @param int    $iSegment
     * @param string $sPrefix
     *
     * @return ApiResponse
     * @throws ApiException
     */
    protected function callOr404(int $iSegment, string $sPrefix = ''): ApiResponse
    {
        $sMethod = ucfirst(strtolower($this->oUri->segment($iSegment)));
        $aMethod = [$this, $sPrefix . $sMethod];

        if (is_callable($aMethod)) {
            return call_user_func($aMethod);
        }

        $this->show404();
    }

    // --------------------------------------------------------------------------

    /**
     * @return void
     * @throws ApiException
     */
    protected function show404(): void
    {
        throw new ApiException(
            $this->oHttpCodes::getByCode($this->oHttpCodes::STATUS_NOT_FOUND),
            $this->oHttpCodes::STATUS_NOT_FOUND
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @return ApiResponse
     * @throws ApiException
     */
    public function getRemap(): ApiResponse
    {
        return $this->callOr404(4);
    }

    // --------------------------------------------------------------------------

    /**
     * @return ApiResponse
     * @throws ApiException
     */
    protected function header(): ApiResponse
    {
        return $this->callOr404(5, 'header');
    }

    // --------------------------------------------------------------------------

    /**
     * @return ApiResponse
     * @throws ApiException
     */
    protected function headerButton(): ApiResponse
    {
        return $this->callOr404(6, 'headerButton');
    }

    // --------------------------------------------------------------------------

    /**
     * @return ApiResponse
     * @throws FactoryException
     * @throws NailsException
     * @throws ValidationException
     */
    protected function headerButtonCreate(): ApiResponse
    {
        /** @var ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', \Nails\Api\Constants::MODULE_SLUG);
        $oApiResponse->setData(
            array_map(
                function (Create $oItem) {
                    return [
                        'icon'        => [
                            'class' => $oItem->getIconClass(),
                        ],
                        'label'       => $oItem->getLabel(),
                        'description' => $oItem->getDescription(),
                        'url'         => $oItem->getUrl(),
                    ];
                },
                $this->oUi->getHeaderButtonCreateItems()
            )
        );
        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * @return ApiResponse
     * @throws FactoryException
     * @throws ValidationException
     */
    protected function headerButtonSearch(): ApiResponse
    {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');
        $sQuery = $this->sanitiseQuery($oInput->get('query'));

        /** @var ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', \Nails\Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData(
                array_values(
                    array_filter(
                        array_map(
                            function (Section $oSection) use ($sQuery) {

                                $aResults = $oSection->getResults($sQuery);

                                return (object) [
                                    'label'   => $oSection->getLabel(),
                                    'count'   => count($aResults),
                                    'results' => array_map(
                                        function (Result $oResult) {
                                            return (object) [
                                                'icon'        => [
                                                    'class' => $oResult->getIconClass(),
                                                ],
                                                'label'       => $oResult->getLabel(),
                                                'description' => $oResult->getDescription(),
                                                'actions'     => array_map(
                                                    function (Result\Action $oAction) {
                                                        return (object) [
                                                            'label'   => $oAction->getLabel(),
                                                            'icon'    => [
                                                                'class' => $oAction->getIconClass(),
                                                            ],
                                                            'url'     => $oAction->getUrl(),
                                                            'new_tab' => $oAction->isNewTab(),
                                                        ];
                                                    },
                                                    $oResult->getActions()
                                                ),
                                            ];
                                        },
                                        $aResults
                                    ),
                                ];
                            },
                            $this->oUi->getHeaderButtonSearchSections()
                        ),
                        fn(\stdClass $oSection) => $oSection->count
                    )
                )
            );

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    protected function sanitiseQuery(string $sQuery): string
    {
        $sQuery = trim($sQuery);

        return $sQuery;
    }
}
