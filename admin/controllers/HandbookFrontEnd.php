<?php

/**
 * This class renders the admin handbook frontend
 *
 * @note        THIS IS NOT AN ADMIN CONTROLLER
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

use App\Controller\Base;
use Nails\Factory;

class HandbookFrontEnd extends Base
{
    public function render()
    {
        $oUri    = Factory::service('Uri');
        $oModel  = Factory::model('Handbook', 'nails/module-admin');
        $iPageId = $oUri->rsegment(3);

        $oPage = $oModel->getById($iPageId);
        if (empty($oPage)) {
            show404();
        }

        //  @todo (Pablo - 2018-11-07) - Get immediate children
        //  @todo (Pablo - 2018-11-07) - Get previous page
        //  @todo (Pablo - 2018-11-07) - Get next page

        Factory::service('View')
            ->setData([
                'oPage' => $oPage,
            ])
            ->load([
                'structure/header',
                'admin/HandbookFrontEnd/render',
                'structure/footer',
            ]);
    }

    // --------------------------------------------------------------------------

    public function search()
    {
        //  @todo (Pablo - 2018-11-07) - Search pages
    }
}
