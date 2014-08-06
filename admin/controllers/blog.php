<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Admin: Blog
* Description:	Blog Manager
*
*/

//	Include Admin_Controller; executes common admin functionality.
require_once '_admin.php';

/**
 * OVERLOADING NAILS' ADMIN MODULES
 *
 * Note the name of this class; done like this to allow apps to extend this class.
 * Read full explanation at the bottom of this file.
 *
 **/

class NAILS_Blog extends NAILS_Admin_Controller
{

	/**
	 * Announces this module's details to those in the know.
	 *
	 * @access static
	 * @param none
	 * @return void
	 **/
	static function announce()
	{
		if ( ! module_is_enabled( 'blog' ) ) :

			return FALSE;

		endif;

		// --------------------------------------------------------------------------

		$d = new stdClass();

		// --------------------------------------------------------------------------

		//	Configurations
		$d->name = 'Blog';
		$d->icon = 'fa fa-pencil-square-o';

		// --------------------------------------------------------------------------

		//	Navigation options
		$d->funcs			= array();
		$d->funcs['index']	= 'Manage Posts';
		$d->funcs['create']	= 'Create New Post';

		get_instance()->load->helper( 'blog_helper' );

		if ( app_setting( 'categories_enabled', 'blog' ) ) :

			$d->funcs['manage/category'] = 'Manage Categories';

		endif;

		if ( app_setting( 'tags_enabled', 'blog' ) ) :

			$d->funcs['manage/tag'] = 'Manage Tags';

		endif;

		// --------------------------------------------------------------------------

		//	Only announce the controller if the user has permission to know about it
		return self::_can_access( $d, __FILE__ );
	}


	// --------------------------------------------------------------------------


	static function permissions()
	{
		$_permissions = array();

		// --------------------------------------------------------------------------

		//	Posts
		$_permissions['post_create']		= 'Posts: Create';
		$_permissions['post_edit']			= 'Posts: Edit';
		$_permissions['post_delete']		= 'Posts: Delete';
		$_permissions['post_restore']		= 'Posts: Restore';

		//	Categories
		$_permissions['category_create']	= 'Category: Create';
		$_permissions['category_edit']		= 'Category: Edit';
		$_permissions['category_delete']	= 'Category: Delete';

		//	Tags
		$_permissions['tag_create']			= 'Tag: Create';
		$_permissions['tag_edit']			= 'Tag: Edit';
		$_permissions['tag_delete']			= 'Tag: Delete';

		// --------------------------------------------------------------------------

		return $_permissions;
	}


	// --------------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		$this->load->model( 'blog/blog_model' );
		$this->load->model( 'blog/blog_post_model' );
		$this->load->model( 'blog/blog_category_model' );
		$this->load->model( 'blog/blog_tag_model' );
	}


	// --------------------------------------------------------------------------


	/**
	 * Post overview
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function index()
	{
		//	Set method info
		$this->data['page']->title = 'Manage Posts';

		// --------------------------------------------------------------------------

		//	Define the $_data variable, this'll be passed to the get_all() and count_all() methods
		$_data = array( 'where' => array(), 'sort' => array() );

		// --------------------------------------------------------------------------

		//	Set useful vars
		$_page			= $this->input->get( 'page' )		? $this->input->get( 'page' )		: 0;
		$_per_page		= $this->input->get( 'per_page' )	? $this->input->get( 'per_page' )	: 50;
		$_sort_on		= $this->input->get( 'sort_on' )	? $this->input->get( 'sort_on' )	: 'bp.published';
		$_sort_order	= $this->input->get( 'order' )		? $this->input->get( 'order' )		: 'desc';
		$_search		= $this->input->get( 'search' )		? $this->input->get( 'search' )		: '';

		//	Set sort variables for view and for $_data
		$this->data['sort_on']		= $_data['sort']['column']	= $_sort_on;
		$this->data['sort_order']	= $_data['sort']['order']	= $_sort_order;
		$this->data['search']		= $_data['search']			= $_search;

		//	Define and populate the pagination object
		$this->data['pagination']				= new stdClass();
		$this->data['pagination']->page			= $_page;
		$this->data['pagination']->per_page		= $_per_page;
		$this->data['pagination']->total_rows	= $this->blog_post_model->count_all( $_data );

		//	Fetch all the items for this page
		$this->data['posts'] = $this->blog_post_model->get_all( $_page, $_per_page, $_data );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/blog/index',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Create a new post
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function create()
	{
		//	Set method info
		$this->data['page']->title = 'Create New Post';

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'is_published',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'published',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'title',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'excerpt',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'image_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'body',				'',	'required' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				//	Prepare data
				$_data						= array();
				$_data['title']				= $this->input->post( 'title' );
				$_data['excerpt']			= $this->input->post( 'excerpt' );
				$_data['image_id']			= $this->input->post( 'image_id' );
				$_data['body']				= $this->input->post( 'body' );
				$_data['seo_description']	= $this->input->post( 'seo_description' );
				$_data['seo_keywords']		= $this->input->post( 'seo_keywords' );
				$_data['is_published']		= (bool) $this->input->post( 'is_published' );
				$_data['published']			= $this->input->post( 'published' );
				$_data['associations']		= $this->input->post( 'associations' );
				$_data['gallery']			= $this->input->post( 'gallery' );

				if ( app_setting( 'categories_enabled', 'blog' ) ) :

					$_data['categories'] = $this->input->post( 'categories' );

				endif;

				if ( app_setting( 'tags_enabled', 'blog' ) ) :

					$_data['tags'] = $this->input->post( 'tags' );

				endif;

				$_post_id = $this->blog_post_model->create( $_data );

				if ( $_post_id ) :

					//	Update admin changelog
					$this->admin_changelog_model->add( 'created', 'a', 'blog post', $_post_id, $_data['title'], 'admin/blog/edit/' . $_post_id );

					// --------------------------------------------------------------------------

					//	Set flashdata and redirect
					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Post was created.' );
					redirect( 'admin/blog' );
					return;

				else :

					$this->data['error'] = lang( 'fv_there_were_errors' );

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load Categories and Tags
		if ( app_setting( 'categories_enabled', 'blog' ) ) :

			$this->data['categories'] = $this->blog_category_model->get_all();

		endif;

		if ( app_setting( 'tags_enabled', 'blog' ) ) :

			$this->data['tags'] = $this->blog_tag_model->get_all();

		endif;

		// --------------------------------------------------------------------------

		//	Load associations
		$this->data['associations'] = $this->blog_model->get_associations();

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->library( 'uploadify' );
		$this->asset->load( 'jquery-serialize-object/jquery.serialize-object.min.js',	'BOWER' );
		$this->asset->load( 'mustache.js/mustache.js',									'BOWER' );
		$this->asset->load( 'nails.admin.blog.create_edit.js',							TRUE );


		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/blog/edit',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	/**
	 * Edit an existing post
	 *
	 * @access public
	 * @param none
	 * @return void
	 **/
	public function edit()
	{
		//	Fetch and check post
		$_post_id = $this->uri->segment( 4 );

		$this->data['post'] = $this->blog_post_model->get_by_id( $_post_id );

		if ( ! $this->data['post'] ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I could\'t find a post by that ID.' );
			redirect( 'admin/blog' );
			return;

		endif;

		// --------------------------------------------------------------------------

		//	Set method info
		$this->data['page']->title = 'Edit Post &rsaquo; ' . $this->data['post']->title;

		// --------------------------------------------------------------------------

		//	Process POST
		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'is_published',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'published',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'title',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'excerpt',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'image_id',			'',	'xss_clean' );
			$this->form_validation->set_rules( 'body',				'',	'required' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean' );

			$this->form_validation->set_message( 'required', lang( 'fv_required' ) );

			if ( $this->form_validation->run() ) :

				//	Prepare data
				$_data						= array();
				$_data['title']				= $this->input->post( 'title' );
				$_data['excerpt']			= $this->input->post( 'excerpt' );
				$_data['image_id']			= $this->input->post( 'image_id' );
				$_data['body']				= $this->input->post( 'body' );
				$_data['seo_description']	= $this->input->post( 'seo_description' );
				$_data['seo_keywords']		= $this->input->post( 'seo_keywords' );
				$_data['is_published']		= (bool) $this->input->post( 'is_published' );
				$_data['published']			= $this->input->post( 'published' );
				$_data['associations']		= $this->input->post( 'associations' );
				$_data['gallery']			= $this->input->post( 'gallery' );

				if ( app_setting( 'categories_enabled', 'blog' ) ) :

					$_data['categories'] = $this->input->post( 'categories' );

				endif;

				if ( app_setting( 'tags_enabled', 'blog' ) ) :

					$_data['tags'] = $this->input->post( 'tags' );

				endif;

				if ( $this->blog_post_model->update( $_post_id, $_data ) ) :

					//	Update admin change log
					foreach ( $_data AS $field => $value ) :

						if ( isset( $this->data['post']->$field ) ) :

							switch( $field ) :

								case 'associations' :

									//	TODO: changelog associations

								break;

								case 'categories' :

									$_old_categories = array();
									$_new_categories = array();

									foreach( $this->data['post']->$field AS $v ) :

										$_old_categories[] = $v->label;

									endforeach;

									if ( is_array( $value ) ) :

										foreach( $value AS $v ) :

											$_temp = $this->blog_category_model->get_by_id( $v );

											if ( $_temp ) :

												$_new_categories[] = $_temp->label;

											endif;

										endforeach;

									endif;

									asort( $_old_categories );
									asort( $_new_categories );

									$_old_categories = implode( ',', $_old_categories );
									$_new_categories = implode( ',', $_new_categories );

									$this->admin_changelog_model->add( 'updated', 'a', 'blog post', $_post_id,  $_data['title'], 'admin/accounts/edit/' . $_post_id, $field, $_old_categories, $_new_categories, FALSE );

								break;

								case 'tags' :

									$_old_tags = array();
									$_new_tags = array();

									foreach( $this->data['post']->$field AS $v ) :

										$_old_tags[] = $v->label;

									endforeach;

									if ( is_array( $value ) ) :

										foreach( $value AS $v ) :

											$_temp = $this->blog_tag_model->get_by_id( $v );

											if ( $_temp ) :

												$_new_tags[] = $_temp->label;

											endif;

										endforeach;

									endif;

									asort( $_old_tags );
									asort( $_new_tags );

									$_old_tags = implode( ',', $_old_tags );
									$_new_tags = implode( ',', $_new_tags );

									$this->admin_changelog_model->add( 'updated', 'a', 'blog post', $_post_id,  $_data['title'], 'admin/accounts/edit/' . $_post_id, $field, $_old_tags, $_new_tags, FALSE );

								break;

								default :

									$this->admin_changelog_model->add( 'updated', 'a', 'blog post', $_post_id,  $_data['title'], 'admin/accounts/edit/' . $_post_id, $field, $this->data['post']->$field, $value, FALSE );

								break;

							endswitch;

						endif;

					endforeach;

					// --------------------------------------------------------------------------

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Post was updated.' );
					redirect( 'admin/blog' );
					return;

				else :

					$this->data['error'] = lang( 'fv_there_were_errors' );

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Load Categories and Tags
		if ( app_setting( 'categories_enabled', 'blog' ) ) :

			$this->data['categories'] = $this->blog_category_model->get_all();

		endif;

		if ( app_setting( 'tags_enabled', 'blog' ) ) :

			$this->data['tags'] = $this->blog_tag_model->get_all();

		endif;

		// --------------------------------------------------------------------------

		//	Load associations
		$this->data['associations'] = $this->blog_model->get_associations( $this->data['post']->id );

		// --------------------------------------------------------------------------

		//	Load assets
		$this->asset->library( 'uploadify' );
		$this->asset->load( 'jquery-serialize-object/jquery.serialize-object.min.js',	'BOWER' );
		$this->asset->load( 'mustache.js/mustache.js',										'BOWER' );
		$this->asset->load( 'nails.admin.blog.create_edit.js',							TRUE );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',	$this->data );
		$this->load->view( 'admin/blog/edit',	$this->data );
		$this->load->view( 'structure/footer',	$this->data );
	}


	// --------------------------------------------------------------------------


	public function delete()
	{
		//	Fetch and check post
		$_post_id = $this->uri->segment( 4 );

		$_post = $this->blog_post_model->get_by_id( $_post_id );

		if ( ! $_post ) :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I could\'t find a post by that ID.' );
			redirect( 'admin/blog' );
			return;

		endif;

		// --------------------------------------------------------------------------

		if ( $this->blog_post_model->delete( $_post_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Post was deleted successfully. ' . anchor( 'admin/blog/restore/' . $_post_id, 'Undo?' ) );

			//	Update admin changelog
			$this->admin_changelog_model->add( 'deleted', 'a', 'blog post', $_post_id, $_post->title );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I failed to delete that post.' );

		endif;

		redirect( 'admin/blog' );
		return;

	}


	// --------------------------------------------------------------------------


	public function restore()
	{
		//	Fetch and check post
		$_post_id = $this->uri->segment( 4 );

		// --------------------------------------------------------------------------

		if ( $this->blog_post_model->restore( $_post_id ) ) :

			$_post = $this->blog_post_model->get_by_id( $_post_id );

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Post was restored successfully. ' );

			//	Update admin changelog
			$this->admin_changelog_model->add( 'restored', 'a', 'blog post', $_post_id, $_post->title, 'admin/blog/edit/' . $_post_id );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> I failed to restore that post.' );

		endif;

		redirect( 'admin/blog' );
		return;

	}


	// --------------------------------------------------------------------------


	public function manage()
	{
		$_method = $this->uri->segment( 4 ) ? $this->uri->segment( 4 ) : 'index';

		if ( method_exists( $this, '_manage_' . $_method ) ) :

			//	Is fancybox?
			$this->data['is_fancybox']	= $this->input->get( 'is_fancybox' ) ? '?is_fancybox=1' : '';

			//	Override the header and footer
			if ( $this->data['is_fancybox'] ) :

				$this->data['header_override'] = 'structure/header/nails-admin-blank';
				$this->data['footer_override'] = 'structure/footer/nails-admin-blank';

			endif;

			//	Start the page title
			$this->data['page']->title = 'Manage &rsaquo; ';

			//	Call method
			$this->{'_manage_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_category()
	{
		//	Load model
		$this->load->model( 'blog/blog_category_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_category_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Categories ';

			$this->{'_manage_category_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_index()
	{
		$_data = array( 'include_count' => TRUE );
		$this->data['categories'] = $this->blog_category_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/blog/manage/category/index',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_create()
	{
		if ( ! user_has_permission( 'admin.blog.category_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->blog_category_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category created successfully.' );
					redirect( 'admin/blog/manage/category' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Category. ' . $this->blog_category_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['categories'] = $this->blog_category_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/blog/manage/category/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_edit()
	{
		if ( ! user_has_permission( 'admin.blog.category_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['category'] = $this->blog_category_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['category'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->blog_category_model->update( $this->data['category']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category saved successfully.' );
					redirect( 'admin/blog/manage/category' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Category. ' . $this->blog_category_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title = 'Edit &rsaquo; ' . $this->data['category']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['categories'] = $this->blog_category_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',					$this->data );
		$this->load->view( 'admin/blog/manage/category/edit',	$this->data );
		$this->load->view( 'structure/footer',					$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_category_delete()
	{
		if ( ! user_has_permission( 'admin.blog.category_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->blog_category_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Category was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Category. ' . $this->blog_category_model->last_error() );

		endif;

		redirect( 'admin/blog/manage/category' . $this->data['is_fancybox'] );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag()
	{
		//	Load model
		$this->load->model( 'blog/blog_tag_model' );

		$_method = $this->uri->segment( 5 ) ? $this->uri->segment( 5 ) : 'index';

		if ( method_exists( $this, '_manage_tag_' . $_method ) ) :

			//	Extend the title
			$this->data['page']->title .= 'Tags ';

			$this->{'_manage_tag_' . $_method}();

		else :

			show_404();

		endif;
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_index()
	{
		$_data = array( 'include_count' => TRUE );
		$this->data['tags'] = $this->blog_tag_model->get_all( NULL, NULL, $_data );

		// --------------------------------------------------------------------------

		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/blog/manage/tag/index',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_create()
	{
		if ( ! user_has_permission( 'admin.blog.tag_create' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->blog_tag_model->create( $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag created successfully.' );
					redirect( 'admin/blog/manage/tag' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem creating the Tag. ' . $this->blog_tag_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title .= '&rsaquo; Create';

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['categories'] = $this->blog_tag_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/blog/manage/tag/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_edit()
	{
		if ( ! user_has_permission( 'admin.blog.tag_edit' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$this->data['tag'] = $this->blog_tag_model->get_by_id( $this->uri->segment( 6 ) );

		if ( empty( $this->data['tag'] ) ) :

			show_404();

		endif;

		// --------------------------------------------------------------------------

		if ( $this->input->post() ) :

			$this->load->library( 'form_validation' );

			$this->form_validation->set_rules( 'label',				'',	'xss_clean|required' );
			$this->form_validation->set_rules( 'description',		'',	'xss_clean' );
			$this->form_validation->set_rules( 'seo_title',			'',	'xss_clean|max_length[150]' );
			$this->form_validation->set_rules( 'seo_description',	'',	'xss_clean|max_length[300]' );
			$this->form_validation->set_rules( 'seo_keywords',		'',	'xss_clean|max_length[150]' );

			$this->form_validation->set_message( 'required',	lang( 'fv_required' ) );
			$this->form_validation->set_message( 'max_length',	lang( 'fv_max_length' ) );

			if ( $this->form_validation->run() ) :

				$_data					= new stdClass();
				$_data->label			= $this->input->post( 'label' );
				$_data->description		= $this->input->post( 'description' );
				$_data->seo_title		= $this->input->post( 'seo_title' );
				$_data->seo_description	= $this->input->post( 'seo_description' );
				$_data->seo_keywords	= $this->input->post( 'seo_keywords' );

				if ( $this->blog_tag_model->update( $this->data['tag']->id, $_data ) ) :

					$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag saved successfully.' );
					redirect( 'admin/blog/manage/tag' . $this->data['is_fancybox'] );

				else :

					$this->data['error'] = '<strong>Sorry,</strong> there was a problem saving the Tag. ' . $this->blog_tag_model->last_error();

				endif;

			else :

				$this->data['error'] = lang( 'fv_there_were_errors' );

			endif;

		endif;

		// --------------------------------------------------------------------------

		//	Page data
		$this->data['page']->title = 'Edit &rsaquo; ' . $this->data['tag']->label;

		// --------------------------------------------------------------------------

		//	Fetch data
		$this->data['tags'] = $this->blog_tag_model->get_all();

		// --------------------------------------------------------------------------

		//	Load views
		$this->load->view( 'structure/header',				$this->data );
		$this->load->view( 'admin/blog/manage/tag/edit',	$this->data );
		$this->load->view( 'structure/footer',				$this->data );
	}


	// --------------------------------------------------------------------------


	protected function _manage_tag_delete()
	{
		if ( ! user_has_permission( 'admin.blog.tag_delete' ) ) :

			unauthorised();

		endif;

		// --------------------------------------------------------------------------

		$_id = $this->uri->segment( 6 );

		if ( $this->blog_tag_model->delete( $_id ) ) :

			$this->session->set_flashdata( 'success', '<strong>Success!</strong> Tag was deleted successfully.' );

		else :

			$this->session->set_flashdata( 'error', '<strong>Sorry,</strong> there was a problem deleting the Tag. ' . $this->blog_tag_model->last_error() );

		endif;

		redirect( 'admin/blog/manage/tag' . $this->data['is_fancybox'] );
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

if ( ! defined( 'NAILS_ALLOW_EXTENSION_BLOG' ) ) :

	class Blog extends NAILS_Blog
	{
	}

endif;


/* End of file blog.php */
/* Location: ./modules/admin/controllers/blog.php */