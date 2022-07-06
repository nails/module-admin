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
    /** @var \stdClass[] */
    protected $adminControllers;

    /** @var \stdClass[] */
    protected $adminControllersNav;

    /**
     * Admin nav groupings are sortable by default, if you wish to make any
     * "sticky" then define them here. The order here will be respected i.e.,
     * The first item will stick to the very top, the next beneath it and so
     * forth.
     *
     * @var string[]
     */
    protected $adminControllersNavSticky = [
        'Dashboard',
        'Utilities',
        'Settings',
    ];

    /**
     * Sticky items by default stick to the top of the nav. If you wish for
     * any to stick to the bottom define them here. The order defined above
     * will be respected, i.e., the order below doesn't matter.
     *
     * @var string[]
     */
    protected $adminControllersNavStickyBottom = [
        'Utilities',
        'Settings',
    ];

    // --------------------------------------------------------------------------

    /**
     * Initial touch point for admin, all requests are routed through here.
     *
     * @throws FactoryException
     */
    public function index()
    {
        $this
            ->authenticateUser()
            ->findAdminControllers()
            ->prepAdminControllersNav();

        // --------------------------------------------------------------------------

        //  Save adminControllers to controller data so everyone can use it
        $this->data['adminControllers']    = $this->adminControllers;
        $this->data['adminControllersNav'] = $this->adminControllersNav;

        // --------------------------------------------------------------------------

        //  Route the user's request
        $this->routeRequest();
    }

    // --------------------------------------------------------------------------

    /**
     * Ensures the user has permission to access admin
     *
     * @return $this
     * @throws FactoryException
     */
    protected function authenticateUser(): self
    {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');

        $aWhitelistIp = (array) appSetting('whitelist', 'admin');

        if (!empty($aWhitelistIp) && !isIpInRange($oInput->ipAddress(), $aWhitelistIp)) {
            show404();

        } elseif (!isAdmin()) {
            unauthorised();
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Searches modules and the app for valid admin controllers which the active
     * user has permission to access.
     *
     * @return $this
     */
    protected function findAdminControllers(): self
    {
        foreach (Components::modules() as $oModule) {
            $this->loadAdminControllers(
                $oModule->moduleName,
                $oModule->path . 'admin/controllers/',
                NAILS_APP_PATH . 'application/modules/' . $oModule->moduleName . '/admin/controllers/'
            );
        }

        $this->loadAppAdminControllers();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for admin controllers in specific locations
     *
     * @param string $moduleName     The name of the module currently being searched
     * @param string $controllerPath The full path to the controllers
     * @param string $appPath        If the app can extend these controllers, this is where it will place the file
     * @param array  $ignore         An array of filenames to ignore
     *
     * @return $this
     */
    protected function loadAdminControllers($moduleName, $controllerPath, $appPath, $ignore = []): self
    {
        $files = \Nails\Common\Helper\Directory::map($controllerPath, 1, false);
        foreach ($files as $file) {
            if (in_array($file, $ignore)) {
                continue;
            }

            $this->loadAdminController($file, $moduleName, $controllerPath, $appPath);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for admin controllers in the app
     *
     * @return $this
     * @throws RouterException
     */
    protected function loadAppAdminControllers(): self
    {
        $appControllerPath = NAILS_APP_PATH . 'application/modules/admin/controllers/';

        //  Look for controllers
        $files = \Nails\Common\Helper\Directory::map($appControllerPath, 1, false);

        foreach ($files as $file) {

            //  Valid Admin file?
            if (!$this->isValidAdminFile($file)) {
                continue;
            }

            $fileName = substr($file, 0, strpos($file, '.php'));

            //  Valid file, load it up and define the full class path and name
            require_once $appControllerPath . $file;
            $classPath = $appControllerPath . $file;
            $className = 'App\Admin\\App\\' . ucfirst($fileName);

            //  Load and process the class
            $this->loadAdminClass($fileName, $className, $classPath, 'app');
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Loads a specific admin controller
     *
     * @param string $file           The file to load
     * @param string $moduleName     The name of the module currently being searched
     * @param string $controllerPath The full path to the controller
     * @param string $appPath        If the app can extend this controllers, this is where it will place the file
     *
     * @return $this
     * @throws RouterException
     */
    protected function loadAdminController($file, $moduleName, $controllerPath, $appPath): self
    {
        $fileName = substr($file, 0, strpos($file, '.php'));

        //  PHP file, no leading underscore
        if (!$this->isValidAdminFile($file)) {
            return $this;
        }

        //  Valid file, load it up and define the full class path and name
        require_once $controllerPath . $file;
        $classPath = $controllerPath . $file;
        $className = 'Nails\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);

        //  If there's an app version of this controller than we'll use that one instead.
        if (is_file($appPath . $file)) {

            require_once $appPath . $file;
            $classPath = $appPath . $file;
            $className = 'App\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);

            //  Does the expected class exist? If it doesn't fall back to the previous one
            if (!class_exists($className)) {
                $classPath = $controllerPath . $file;
                $className = 'Nails\Admin\\' . ucfirst($moduleName) . '\\' . ucfirst($fileName);
            }
        }

        //  Load and process the class
        $this->loadAdminClass($fileName, $className, $classPath, $moduleName);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the file being loaded has an acceptable filename.
     *
     * @param String $file The filename to test
     *
     * @return bool
     */
    protected function isValidAdminFile($file): bool
    {
        //  PHP file, no leading underscore
        return preg_match('/^[^_][a-zA-Z_]+\.php$/', $file);
    }

    // --------------------------------------------------------------------------

    /**
     * Attempts to load an AdminClass
     *
     * @param string $fileName   The filename of the class being loaded
     * @param string $className  The name of the class being loaded
     * @param string $classPath  The path of the class being loaded
     * @param string $moduleName The name of the module to which this class belongs
     *
     * @return bool
     * @throws RouterException
     */
    protected function loadAdminClass($fileName, $className, $classPath, $moduleName): bool
    {
        //  Does the expected class exist?
        if (!class_exists($className)) {
            return false;
        }

        //  Does it have an announce method?
        if (!method_exists($className, 'announce')) {
            return false;
        }

        //  Cool! We have a controller which is valid, Add it to the stack!
        if (!isset($this->adminControllers[$moduleName])) {
            $this->adminControllers[$moduleName]              = new \stdClass();
            $this->adminControllers[$moduleName]->controllers = [];
        }

        $aNavGroupings = $className::announce();

        if (!empty($aNavGroupings) && !is_array($aNavGroupings) && !($aNavGroupings instanceof \Nails\Admin\Factory\Nav)) {

            /**
             * @todo Use an admin specific exception class, and autoload it.
             */

            throw new RouterException('Admin Nav groupings returned by ' . $className . '::announce() were invalid', 1);

        } elseif (!is_array($aNavGroupings)) {
            $aNavGroupings = array_filter([$aNavGroupings]);
        }

        $this->adminControllers[$moduleName]->controllers[$fileName] = [
            'className' => (string) $className,
            'path'      => (string) $classPath,
            'groupings' => $aNavGroupings,
        ];

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a "view friendly" array of the admin controllers, takes into
     * consideration the user's order and state preferences
     *
     * @return $this
     * @throws FactoryException
     */
    protected function prepAdminControllersNav(): self
    {
        $adminControllersNav = [];

        foreach ($this->adminControllers as $module => $moduleDetails) {
            foreach ($moduleDetails->controllers as $controller => $controllerDetails) {
                foreach ($controllerDetails['groupings'] as $grouping) {

                    //  Begin by defining which group this action belongs in
                    $sGroupLabel = $grouping->getLabel();

                    if (!isset($adminControllersNav[md5($sGroupLabel)])) {
                        $adminControllersNav[md5($sGroupLabel)]           = new \stdClass();
                        $adminControllersNav[md5($sGroupLabel)]->label    = $sGroupLabel;
                        $adminControllersNav[md5($sGroupLabel)]->icon     = [];
                        $adminControllersNav[md5($sGroupLabel)]->sortable = true;
                        $adminControllersNav[md5($sGroupLabel)]->open     = false;
                        $adminControllersNav[md5($sGroupLabel)]->actions  = [];
                    }

                    /**
                     * Add the group icon to the icon array. Multiple calls to the same group
                     * will add their own icon, the most common icon will be used. In the event
                     * of a tie then the first icon defined will be used, unless one has been
                     * marked as !important.
                     */

                    $moduleIcon = $grouping->getIcon();

                    if (!empty($moduleIcon)) {

                        $adminControllersNav[md5($sGroupLabel)]->icon[] = $moduleIcon;
                    }

                    //  Group actions
                    $groupActions = $grouping->getActions();

                    foreach ($groupActions as $actionUrl => $actionDetails) {

                        $url = $module . '/' . lcfirst($controller);
                        $url .= empty($actionUrl) ? '' : '/';
                        $url .= $actionUrl;

                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]              = new \stdClass();
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->order       = $actionDetails->order;
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->label       = $actionDetails->label;
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->searchTerms = $actionDetails->searchTerms;
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->alerts      = $actionDetails->alerts;
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->class       = $controllerDetails['className'];
                        $adminControllersNav[md5($sGroupLabel)]->actions[$url]->path        = $controllerDetails['path'];
                    }
                }
            }
        }

        //  Reset the indexes
        $adminControllersNav = array_values($adminControllersNav);

        //  Remove navGroups with no actions and set as sortable
        $numGroups = count($adminControllersNav);
        for ($i = 0; $i < $numGroups; $i++) {

            //  Remove if empty
            if (empty($adminControllersNav[$i]->actions)) {
                $adminControllersNav[$i] = null;
                continue;
            }

            //  Set sortable
            if (in_array($adminControllersNav[$i]->label, $this->adminControllersNavSticky)) {
                $adminControllersNav[$i]->sortable = false;
            }
        }

        //  Filter and reset the indexes, again
        $adminControllersNav = array_filter($adminControllersNav);
        $adminControllersNav = array_values($adminControllersNav);

        //  Split the groups
        $stickyTop    = [];
        $middle       = [];
        $stickyBottom = [];

        foreach ($this->adminControllersNavSticky as $sticky) {
            foreach ($adminControllersNav as $group) {
                if ($group->label == $sticky) {
                    if (in_array($sticky, $this->adminControllersNavStickyBottom)) {
                        $stickyBottom[] = $group;
                    } else {
                        $stickyTop[] = $group;
                    }
                }
            }
        }

        foreach ($adminControllersNav as $group) {

            if (!in_array($group->label, $this->adminControllersNavSticky)) {
                $middle[] = $group;
            }
        }

        /**
         * Sort everything alphabetically, then loop through the user's prefs and sort
         * by their preferences. If any modules are left over they'll appear at the end,
         * in alphabetical order. Also, set the open state of the module.
         */

        //  Sort alphabetically
        arraySortMulti($middle, 'label');

        //  Get user's prefs
        /** @var \Nails\Admin\Model\Admin $oAdminModel */
        $oAdminModel = Factory::model('Admin', Constants::MODULE_SLUG);
        $userNavPref = (array) $oAdminModel->getAdminData('nav_state');
        arraySortMulti($userNavPref, 'order');

        if (!empty($userNavPref)) {

            $temp = [];

            foreach ($userNavPref as $groupMd5 => $state) {
                for ($i = 0; $i < count($middle); $i++) {

                    if (empty($middle[$i])) {
                        continue;
                    }

                    if ($groupMd5 == md5($middle[$i]->label)) {
                        if (!in_array($middle[$i]->label, $this->adminControllersNavSticky)) {
                            $temp[]     = $middle[$i];
                            $middle[$i] = null;
                        }
                    }
                }
            }

            //  Filter out the blanks
            $middle = array_filter($middle);
            $middle = array_values($middle);

            //  Merge
            $middle = array_merge($temp, $middle);
        }

        //  Save to the class
        $this->adminControllersNav = array_merge($stickyTop, $middle, $stickyBottom);

        //  Set the open states of the modules
        $this->setSidebarOpenStates($userNavPref);

        /**
         * Finally, now that everything has been sorted, go through all the groupings
         * and sort their methods alphabetically so there's some feeling of order in
         * amongst all this chaos.
         */
        $this->sortSidebarActions();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the open states for each sidebar modukle
     *
     * @param $userNavPref
     *
     * @return $this
     */
    protected function setSidebarOpenStates($userNavPref): self
    {
        if (!empty($userNavPref)) {
            foreach ($userNavPref as $groupMd5 => $state) {
                for ($i = 0; $i < count($this->adminControllersNav); $i++) {
                    if ($groupMd5 == md5($this->adminControllersNav[$i]->label)) {
                        $this->adminControllersNav[$i]->open = $state->open;
                    }
                }
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sorts the sidebar actions into a defined order
     *
     * @return $this
     */
    protected function sortSidebarActions(): self
    {
        foreach ($this->adminControllersNav as $grouping) {

            $aUnordered = [];
            $aOrdered   = [];
            foreach ($grouping->actions as $sKey => $oAction) {
                if (is_null($oAction->order)) {
                    $aUnordered[$sKey] = $oAction;
                } else {
                    if (!array_key_exists($oAction->order, $aOrdered)) {
                        $aOrdered[$oAction->order] = [];
                    }
                    $aOrdered[$oAction->order][$sKey] = $oAction;
                }
            }

            //  Ensure ordered items are ordered by priority, then label
            ksort($aOrdered);
            foreach ($aOrdered as $aItems) {
                arraySortMulti($aItems, 'label');
            }

            //  Ensure unordered items are ordered by labe;
            arraySortMulti($aUnordered, 'label');

            //  Merge everything together
            $aFinal = [];
            foreach ($aOrdered as $aItems) {
                $aFinal = array_merge($aFinal, $aItems);
            }
            $aFinal = array_merge($aFinal, $aUnordered);

            $grouping->actions = $aFinal;
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Routes the request to the appropriate controller
     *
     * @return void
     * @throws FactoryException
     */
    protected function routeRequest()
    {
        /** @var \Nails\Common\Service\Uri $oUri */
        $oUri        = Factory::service('Uri');
        $sModule     = $oUri->rsegment(3) ? $oUri->rsegment(3) : '';
        $sController = ucfirst($oUri->rsegment(4) ? $oUri->rsegment(4) : $sModule);
        $sMethod     = $oUri->rsegment(5) ? $oUri->rsegment(5) : 'index';

        if (empty($sModule)) {

            /** @var \Nails\Common\Service\Session $oSession */
            $oSession = Factory::service('Session');
            $oSession->keepFlashData();
            redirect('admin/admin/dashboard');

        } elseif (isset($this->adminControllers[$sModule]->controllers[$sController])) {

            $aRequestController           = $this->adminControllers[$sModule]->controllers[$sController];
            $this->data['currentRequest'] = $aRequestController;
            $sControllerName              = $aRequestController['className'];
            $oController                  = new $sControllerName();

            if (method_exists($sControllerName, '_remap')) {
                $oController->_remap();

            } elseif (is_callable([$sControllerName, $sMethod])) {
                $oController->$sMethod();

            } else {
                show404();
            }

        } else {
            show404();
        }
    }
}
