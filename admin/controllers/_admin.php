<?php

/**
 * This class provides some common admin controller functionality
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class NAILS_Admin_Controller extends NAILS_Controller
{
    protected $loadedModules;
    protected $currentModule;

    // --------------------------------------------------------------------------

    /**
     * Construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  IP whitelist?
        $whitelistIp = (array) app_setting('whitelist', 'admin');

        if ($whitelistIp) {

            if (!isIpInRange($this->input->ip_address(), $whitelistIp)) {

                show_404();
            }
        }

        // --------------------------------------------------------------------------

        //  Admins only please, log in or bog off.
        if (!$this->user_model->is_admin()) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        /**
         * Handle the blank admin route, redirect to the dashboard which will always
         * be available.
         */

        if ($this->uri->segment(2, 'BLANKADMINROUTE') == 'BLANKADMINROUTE') {

            $this->session->keep_flashdata();
            redirect('admin/dashboard');
        }

        // --------------------------------------------------------------------------

        //  Load up the generic admin langfile
        $this->lang->load('admin_generic');

        // --------------------------------------------------------------------------

        //  Check that admin is running on the SECURE_BASE_URL url
        if (APP_SSL_ROUTING) {

            $_host1 = $this->input->server('HTTP_HOST');
            $_host2 = parse_url(SECURE_BASE_URL);

            if (!empty($_host2['host']) && $_host2['host'] != $_host1) {

                //  Not on the secure URL, redirect with message
                $_redirect = $this->input->server('REQUEST_URI');

                if ($_redirect) {

                    $this->session->set_flashdata('message', lang('admin_not_secure'));
                    redirect($_redirect);
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Load admin helper and config
        $this->load->model('admin_model');
        $this->config->load('admin/admin');

        //  App admin config
        if (file_exists(FCPATH . APPPATH . 'config/admin.php')) {

            $this->config->load(FCPATH . APPPATH . 'config/admin.php');
        }

        // --------------------------------------------------------------------------

        /**
         * Fetch all available modules for this installation and get the user's ACL.
         * Make sure the user has permission to access this module.
         */

        $this->loadedModules        = array();
        $this->data['loaded_modules'] =& $this->loadedModules;

        //  Fetch all available modules for this installation and user
        $this->loadedModules     = $this->admin_model->get_active_modules();
        $this->data['has_modules'] = count($this->loadedModules) ? true : false;

        //  Fetch the current module, if this is null then it means no access
        $this->currentModule = $this->admin_model->get_current_module();

        if (is_null($this->currentModule)) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Load libraries
        $this->load->library('cdn/cdn');

        // --------------------------------------------------------------------------

        //  Add the current module to the $page variable (for convenience)
        $this->data['page']->module = $this->currentModule;

        // --------------------------------------------------------------------------

        //  Unload any previously loaded assets, admin handles its own assets
        $this->asset->clear();

        //  CSS
        $this->asset->load('fancybox/source/jquery.fancybox.css', 'BOWER');
        $this->asset->load('jquery-toggles/css/toggles.css', 'BOWER');
        $this->asset->load('jquery-toggles/css/themes/toggles-modern.css', 'BOWER');
        $this->asset->load('tipsy/src/stylesheets/tipsy.css', 'BOWER');
        $this->asset->load('fontawesome/css/font-awesome.min.css', 'BOWER');
        $this->asset->load('nails.admin.css', true);

        //  JS
        $this->asset->load('jquery/dist/jquery.min.js', 'BOWER');
        $this->asset->load('fancybox/source/jquery.fancybox.pack.js', 'BOWER');
        $this->asset->load('jquery-toggles/toggles.min.js', 'BOWER');
        $this->asset->load('tipsy/src/javascripts/jquery.tipsy.js', 'BOWER');
        $this->asset->load('jquery.scrollTo/jquery.scrollTo.min.js', 'BOWER');
        $this->asset->load('jquery-cookie/jquery.cookie.js', 'BOWER');
        $this->asset->load('retina.js/dist/retina.min.js', 'BOWER');
        $this->asset->load('nails.default.min.js', true);
        $this->asset->load('nails.admin.min.js', true);
        $this->asset->load('nails.forms.min.js', true);
        $this->asset->load('nails.api.min.js', true);

        //  Libraries
        $this->asset->library('jqueryui');
        $this->asset->library('select2');
        $this->asset->library('ckeditor');
        $this->asset->library('uploadify');

        //  Look for any Admin styles provided by the app
        if (file_exists(FCPATH . 'assets/css/admin.css')) {

            $this->asset->load('admin.css');
        }

        //  Inline assets
        $_js  = 'var _nails,_nails_admin,_nails_forms;';
        $_js .= '$(function(){';

        $_js .= 'if (typeof(NAILS_JS) === \'function\'){';
        $_js .= '_nails = new NAILS_JS();';
        $_js .= '_nails.init();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_Admin) === \'function\'){';
        $_js .= '_nails_admin = new NAILS_Admin();';
        $_js .= '_nails_admin.init();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_Forms) === \'function\'){';
        $_js .= '_nails_forms = new NAILS_Forms();';
        $_js .= '}';

        $_js .= 'if (typeof(NAILS_API) === \'function\'){';
        $_js .= '_nails_api = new NAILS_API();';
        $_js .= '}';

        $_js .= '});';

        $this->asset->inline('<script>' . $_js . '</script>');

        // --------------------------------------------------------------------------

        //  Initialise the admin models
        $this->load->model('admin_help_model');
        $this->load->model('admin_changelog_model');
    }

    // --------------------------------------------------------------------------

    /**
     * Basic definition of the announce() static method
     * @return null
     */
    public static function announce()
    {
        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Basic definition of the notifications() static method
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function notifications($classIndex = null)
    {
        return array();
    }

    // --------------------------------------------------------------------------

    /**
     * Basic definition of the permissions() static method
     * @param  string $classIndex The class_index value, used when multiple admin instances are available
     * @return array
     */
    public static function permissions($classIndex = null)
    {
        return array();
    }
}
