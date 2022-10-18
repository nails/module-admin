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
use Nails\Admin\Exception\RouterException;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Components;
use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Allow the app to add functionality, if needed
 * Negative conditional helps with static analysis
 */
if (!class_exists('\App\Admin\Controller\BaseRouter')) {
    abstract class BaseMiddle extends \Nails\Common\Controller\Base
    {
    }
} else {
    abstract class BaseMiddle extends \App\Admin\Controller\BaseRouter
    {
        public function __construct()
        {
            if (!classExtends(parent::class, \Nails\Common\Controller\Base::class)) {
                throw new NailsException(sprintf(
                    'Class %s must extend %s',
                    parent::class,
                    \Nails\Common\Controller\Base::class
                ));
            }
            parent::__construct();
        }
    }
}

// --------------------------------------------------------------------------

/**
 * Class AdminRouter
 */
class AdminRouter extends BaseMiddle
{
    /**
     * Initial touch point for admin, all requests are routed through here.
     *
     * @throws FactoryException
     */
    public function index()
    {
        if (!isAdmin()) {
            unauthorised();
        }

        /** @var \Nails\Admin\Service\Controller $oControllerService */
        $oControllerService = Factory::service('Controller', Constants::MODULE_SLUG);
        $oControllerService->handleRoute();
    }
}
