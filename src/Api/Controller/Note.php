<?php

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Helper\ArrayHelper;
use Nails\Common\Helper\Model\Where;
use Nails\Common\Service\Input;
use Nails\Factory;

/**
 * Class Note
 *
 * @package Nails\Admin\Api\Controller
 */
class Note extends Api\Controller\CrudController
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    const CONFIG_MODEL_NAME     = 'Note';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;
    const CONFIG_LOOKUP_DATA    = ['expand' => ['created_by']];

    // --------------------------------------------------------------------------

    /**
     * Handles GET requests
     *
     * @param string $sMethod The method being called
     * @param array  $aData   Any data to apply to the requests
     *
     * @return Api\Factory\ApiResponse
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    public function getRemap($sMethod, array $aData = [])
    {
        [$sModel, $iItemId] = $this->getModelClassAndId();
        $aData['where'] = [
            ['model', $sModel],
            ['item_id', $iItemId],
        ];

        return parent::getRemap($sMethod, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Counts the number of items for a specific model/item combination
     *
     * @param array $aData Any data to apply to the requests
     *
     * @return Api\Factory\ApiResponse
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    public function postCount(array $aData = [])
    {
        $aData   = $this->getRequestData();
        $aBundle = ArrayHelper::get('dataBundle', $aData, []);
        $aOut    = [];

        foreach ($aBundle as $sProvider => $aModels) {

            $aOut[$sProvider] = [];

            foreach ($aModels as $sModel => $aIds) {

                $aOut[$sProvider][$sModel] = [];

                try {

                    $oModel      = Factory::model($sModel, $sProvider);
                    $sModelClass = get_class($oModel);

                } catch (\Exception $e) {
                    throw new Api\Exception\ApiException(
                        '"' . $sProvider . ':' . $sModel . '" is not a valid model'
                    );
                }

                $aIds = array_unique($aIds);
                $aIds = array_filter($aIds);

                foreach ($aIds as $sId) {

                    $aOut[$sProvider][$sModel][$sId] = $this->oModel->countAll([
                        new Where('model', $sModelClass),
                        new Where('item_id', $sId),
                    ]);
                }
            }
        }

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData($aOut);

        return $oApiResponse;
    }

    // --------------------------------------------------------------------------

    /**
     * Validates user input; adds the model to the response
     *
     * @param array     $aData The user data to validate
     * @param \stdClass $oItem The current object (when editing)
     *
     * @return array
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    protected function validateUserInput($aData, $oItem = null): array
    {
        $aData = parent::validateUserInput($aData, $oItem);

        [$sModel] = $this->getModelClassAndId();
        $aData['model'] = $sModel;

        return $aData;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an arry of the model's class name and the item's ID
     *
     * @return array
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    protected function getModelClassAndId(): array
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $sModelName     = $oInput->get('model_name') ?: $oInput->post('model_name');
        $sModelProvider = $oInput->get('model_provider') ?: $oInput->post('model_provider');
        $iItemId        = (int) $oInput->get('item_id');

        try {
            $oModel = Factory::model($sModelName, $sModelProvider);
            $sModel = get_class($oModel);
        } catch (\Exception $e) {
            throw new Api\Exception\ApiException(
                '"' . $sModelProvider . ':' . $sModelName . '" is not a valid model'
            );
        }

        return [$sModel, $iItemId];
    }

    // --------------------------------------------------------------------------

    /**
     * Formats the response object
     *
     * @param \stdClass $oObj The object to format
     *
     * @return \stdClass
     */
    protected function formatObject($oObj): \stdClass
    {
        return (object) [
            'id'      => $oObj->id,
            'message' => auto_typography($oObj->message),
            'date'    => toUserDatetime($oObj->created),
            'user'    => (object) [
                'id'         => $oObj->created_by ? (int) $oObj->created_by->id : null,
                'first_name' => $oObj->created_by ? $oObj->created_by->first_name : null,
                'last_name'  => $oObj->created_by ? $oObj->created_by->last_name : null,
            ],
        ];
    }
}
