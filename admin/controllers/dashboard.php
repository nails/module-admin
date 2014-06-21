<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Name:		Admin: Dashboard
 * Description:	Admin dashboard
 *
 **/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Dashboard extends NAILS_Admin_Controller
{

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access	static
	 * @param	none
	 * @return	void
	 **/
	static function announce()
	{
		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Load the laguage file
		get_instance()->lang->load( 'admin_dashboard' );

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = lang( 'dashboard_module_name' );

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs				= array();
		$d->funcs['index']		= lang( 'dashboard_nav_index' );

		//	Only show the help option if there are videos available
		get_instance()->load->model( 'admin_help_model' );

		if ( get_instance()->admin_help_model->count_all() ) :

			$d->funcs['help']		= lang( 'dashboard_nav_help' );

		endif;

		// --------------------------------------------------------------------------

		//	Only announce the controller if the user has permisison to know about it
		return self::_can_access( $d, __FILE__ );
	}


	// --------------------------------------------------------------------------


	/**
	 * Administration homepage / dashboard
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function index()
	{
		//	Page Data
		$this->data['page']->title	= lang( 'dashboard_welcome_title' );
		$this->data['has_modules']	= $this->_loaded_modules ? TRUE : FALSE;

		// --------------------------------------------------------------------------

		//	Fetch recent admin changelog events
		//	TODO: widgitize this and use the API

		$this->data['changelog'] = $this->admin_changelog_model->get_recent();

		// --------------------------------------------------------------------------

		//	Assets
		$this->asset->load( 'nails.admin.dashboard.min.js', TRUE );

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/dashboard/index', $this->data );
		$this->load->view( 'structure/footer', $this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Administration help
	 *
	 * @access	public
	 * @param	none
	 * @return	void
	 **/
	public function help()
	{
		//	Page Title
		$this->data['page']->title = lang( 'dashboard_help_title' );

		// --------------------------------------------------------------------------

		//	Get data
		$this->data['videos'] = $this->admin_help_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/dashboard/help/overview',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}

}


// --------------------------------------------------------------------------


/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * The following block of code makes it simple to extend one of the core admin
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if ( ! defined( 'NAILS_ALLOW_EXTENSION_DASHBOARD' ) ) :

	class Dashboard extends NAILS_Dashboard
	{
	}

endif;


/* End of file dashboard.php */
/* Location: ./modules/admin/controllers/dashboard.php */