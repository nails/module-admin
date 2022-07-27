<?php

/**
 * This class is the base class of all Admin controllers, it defines some basic
 * methods which should exist.
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Events;
use Nails\Admin\Interfaces\Controller;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Asset;
use Nails\Common\Service\Event;
use Nails\Common\Service\UserFeedback;
use Nails\Components;
use Nails\Config;
use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Allow the app to add functionality, if needed
 */
if (!class_exists('\App\Admin\Controller\Base')) {
    abstract class BaseMiddle
    {
        protected UserFeedback $oUserFeedback;

        public function __construct()
        {
            $this->oUserFeedback = Factory::service('UserFeedback');
        }
    }
} else {
    abstract class BaseMiddle extends \App\Admin\Controller\Base
    {
        protected \AdminRouter $oRouter;
        protected UserFeedback $oUserFeedback;

        public function __construct()
        {
            parent::__construct();
            $this->oUserFeedback = Factory::service('UserFeedback');
        }
    }
}

// --------------------------------------------------------------------------

/**
 * Class Base
 *
 * @package Nails\Admin\Controller
 */
abstract class Base extends BaseMiddle implements Controller
{
    public $data;

    /**
     * Construct the controller, load all the admin assets, etc
     */
    public function __construct()
    {
        parent::__construct();

        //  Setup Events
        /** @var Event $oEventService */
        $oEventService = Factory::service('Event');

        //  Call the ADMIN:STARTUP event, admin is constructing
        $oEventService->trigger(Events::ADMIN_STARTUP, Events::getEventNamespace());

        // --------------------------------------------------------------------------

        //  Provide access to the main controller's data property
        $this->data =& getControllerData();

        // --------------------------------------------------------------------------

        $this
            ->loadConfigs()
            ->loadHelpers();

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->clear()
            ->compileGlobalData();

        //  @todo (Pablo - 2017-06-08) - Try and reduce the number of things being loaded, or theme it
        $this
            ->loadLibraries()
            ->loadCss()
            ->loadJs()
            ->autoLoad();

        // --------------------------------------------------------------------------

        \Nails\Common\Controller\Base::populateUserFeedback($this->data);

        // --------------------------------------------------------------------------

        //  Call the ADMIN:READY event, admin is all geared up and ready to go
        $oEventService->trigger(Events::ADMIN_READY, Events::getEventNamespace());
    }

    // --------------------------------------------------------------------------

    public static function permissions(): array
    {
        return [];
    }

    public static function url(string $sUrl = ''): string
    {
        /** @var \Nails\Admin\Service\Controller $oControllerService */
        $oControllerService = Factory::service('Controller', Constants::MODULE_SLUG);
        $sBaseUrl           = $oControllerService->determineBaseUrl(static::class);

        return siteUrl(
            sprintf(
                '%s%s',
                $sBaseUrl,
                $sUrl ? '/' . $sUrl : ''
            )
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Load admin configs
     *
     * @return $this
     * @throws FactoryException
     */
    protected function loadConfigs(): self
    {
        $oConfig = Factory::service('Config');

        $aPaths = [
            Config::get('NAILS_APP_PATH') . 'application/config/admin.php',
            Config::get('NAILS_APP_PATH') . 'application/modules/admin/config/admin.php',
        ];

        foreach ($aPaths as $sPath) {
            if (file_exists($sPath)) {
                $oConfig->load($sPath);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load admin helpers
     *
     * @return $this
     * @throws FactoryException
     */
    protected function loadHelpers(): self
    {
        Factory::helper('admin', Constants::MODULE_SLUG);
        Factory::helper('form', Constants::MODULE_SLUG);

        return $this;
    }

    // --------------------------------------------------------------------------

    protected function loadCss(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->load('admin.ui.min.css', Constants::MODULE_SLUG)
            ->load('admin.plugins.min.css', Constants::MODULE_SLUG)
            ->load('admin.min.css', Constants::MODULE_SLUG);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Load all Admin orientated JS
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadJs(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset
            ->load('admin.ui.min.js', Constants::MODULE_SLUG)
            ->load('admin.plugins.min.js', Constants::MODULE_SLUG)
            ->load('admin.forms.min.js', Constants::MODULE_SLUG);

        //  Inline assets
        $aJs = [
            //  Trigger a UI Refresh, most JS components should use this to bind to and render items
            'window.NAILS.ADMIN.refreshUi();',
        ];

        $oAsset->inline(implode(PHP_EOL, $aJs), 'JS');

        return $this;
    }


    // --------------------------------------------------------------------------

    /**
     * Load services required by admin
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function loadLibraries(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset

            //  jQuery
            ->load('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js')

            //  Fancybox
            ->load('https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/js/jquery.fancybox.min.js')
            ->load('https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/css/jquery.fancybox.min.css')

            //  jQuery Toggles
            ->load('https://cdn.jsdelivr.net/gh/simontabor/jquery-toggles/toggles.min.js')
            ->load('https://cdn.jsdelivr.net/gh/simontabor/jquery-toggles/css/toggles.css')
            ->load('https://cdn.jsdelivr.net/gh/simontabor/jquery-toggles/css/themes/toggles-modern.css')

            //  jQuery serializeObject
            ->load('https://cdnjs.cloudflare.com/ajax/libs/jquery-serialize-object/2.5.0/jquery.serialize-object.min.js')

            //  jQuery scrollTo
            ->load('https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/1.4.14/jquery.scrollTo.min.js')

            //  jQuery Cookies
            ->load('https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js')

            //  hint.css
            ->load('https://cdnjs.cloudflare.com/ajax/libs/hint.css/2.6.0/hint.min.css')

            //  Retina.js
            ->load('https://cdnjs.cloudflare.com/ajax/libs/retina.js/1.3.0/retina.min.js')

            //  Bootstrap
            ->load('https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js')

            //  Fontawesome
            ->load('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/fontawesome.min.css')
            ->load('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/solid.min.css')

            //  Bundled libraries
            ->jqueryui()
            ->select2()
            ->ckeditor()
            ->knockout()
            ->moment()
            ->mustache();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Autoload component items
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    protected function autoLoad(): self
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');

        foreach (Components::available() as $oComponent) {
            if (!empty($oComponent->data->{Constants::MODULE_SLUG}->autoload)) {

                $oAutoLoad = $oComponent->data->{Constants::MODULE_SLUG}->autoload;

                //  Services
                if (!empty($oAutoLoad->services)) {
                    foreach ($oAutoLoad->services as $sService) {
                        Factory::service($sService, $oComponent->slug);
                    }
                }

                //  Models
                if (!empty($oAutoLoad->models)) {
                    foreach ($oAutoLoad->models as $sModel) {
                        Factory::model($sModel, $oComponent->slug);
                    }
                }

                //  Helpers
                if (!empty($oAutoLoad->helpers)) {
                    foreach ($oAutoLoad->helpers as $sHelper) {
                        Factory::helper($sHelper, $oComponent->slug);
                    }
                }

                //  Javascript
                if (!empty($oAutoLoad->assets->js)) {
                    foreach ($oAutoLoad->assets->js as $sAsset) {
                        $oAsset->load($sAsset, $oComponent->slug, 'JS');
                    }
                }

                //  Inline Javascript
                if (!empty($oAutoLoad->assets->jsInline)) {
                    foreach ($oAutoLoad->assets->jsInline as $sAsset) {
                        $oAsset->inline($sAsset, 'JS');
                    }
                }

                //  CSS
                if (!empty($oAutoLoad->assets->css)) {
                    foreach ($oAutoLoad->assets->css as $sAsset) {
                        $oAsset->load($sAsset, $oComponent->slug, 'CSS');
                    }
                }

                //  Inline CSS
                if (!empty($oAutoLoad->assets->cssInline)) {
                    foreach ($oAutoLoad->assets->cssInline as $sAsset) {
                        $oAsset->inline($sAsset, 'CSS');
                    }
                }
            }
        }

        return $this;
    }
}
