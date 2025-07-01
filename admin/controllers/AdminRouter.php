<?php

/**
 * This class routes all requests in admin to the appropriate place
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Admin\Constants;
use Nails\Admin\Service\Controller;
use Nails\Common\Controller\Base;
use Nails\Common\Exception\FactoryException;
use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Class AdminRouter
 */
class AdminRouter extends Base
{
    /**
     * Initial touchpoint for admin, all requests are routed through here.
     *
     * @throws FactoryException
     */
    public function index(): void
    {
        if (!isAdmin()) {
            unauthorised();
        }

        /** @var Controller $oControllerService */
        $oControllerService = Factory::service('Controller', Constants::MODULE_SLUG);
        $oControllerService->handleRoute();
    }
}
