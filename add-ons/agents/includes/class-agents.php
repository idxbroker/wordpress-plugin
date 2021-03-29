<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * This file contains the IMPress_Agents class.
 */

/**
 * This class handles the creation of the "Employees" post type, and creates a
 * UI to display the Employee-specific data on the admin screens.
 *
 */
class IMPress_Agents {

	var $settings_page = 'impress-agents-settings';
	var $settings_field = 'impress_agents_taxonomies';
	var $menu_page = 'impress-agents-taxonomies';

	var $options;

	/**
	 * Property details array.
	 */
	var $employee_details;
	var $employee_social;

	/**
	 * Construct Method.
	 */
	function __construct() {

		$this->options = get_option('plugin_impress_agents_settings');

		$this->employee_details = apply_filters( 'impress_agents_employee_details', array(
			'col1' => array(
				__( 'First Name:', 'impress_agents' ) 		=> '_employee_first_name',
				__( 'Last Name:', 'impress_agents' ) 		=> '_employee_last_name',
				__( 'Title:', 'impress_agents' ) 			=> '_employee_title',
				__( 'Email:', 'impress_agents' )			=> '_employee_email',
				__( 'Website:', 'impress_agents' )			=> '_employee_website',
				__( 'Phone:', 'impress_agents' ) 			=> '_employee_phone',
				__( 'Mobile:', 'impress_agents' ) 			=> '_employee_mobile'
			),
			'col2' => array(
				__( 'License #:', 'impress_agents' ) 		=> '_employee_license',
				__( 'Agent ID:', 'impress_agents' ) 		=> '_employee_agent_id',
				__( 'Designations:', 'impress_agents' ) 	=> '_employee_designations',
				__( 'Address:', 'impress_agents' ) 			=> '_employee_address',
				__( 'City:', 'impress_agents' )				=> '_employee_city',
				__( 'State:', 'impress_agents' )			=> '_employee_state',
				__( 'Zip:', 'impress_agents' )				=> '_employee_zip'
			),
		) );

		$this->employee_social = apply_filters( 'impress_agents_employee_social', array(
				__( 'Facebook URL:', 'impress_agents' ) 	=> '_employee_facebook',
				__( 'Twitter URL:', 'impress_agents' )		=> '_employee_twitter',
				__( 'LinkedIn URL:', 'impress_agents' )		=> '_employee_linkedin',
				__( 'Google+ URL:', 'impress_agents' )		=> '_employee_googleplus',
				__( 'Pinterest URL:', 'impress_agents' )	=> '_employee_pinterest',
				__( 'YouTube URL:', 'impress_agents' )		=> '_employee_youtube',
				__( 'Instagram URL:', 'impress_agents' )	=> '_employee_instagram'
			)
		);

		add_action( 'init', array( $this, 'create_post_type' ) );

		add_filter( 'manage_edit-employee_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );

		add_action( 'admin_menu', array( $this, 'register_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );

		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_init', array( &$this, 'add_options' ) );

	}

	/**
	 * Registers the option to load the stylesheet
	 */
	function register_settings() {
		register_setting( 'impress_agents_options', 'plugin_impress_agents_settings' );
	}

	/**
	 * Sets default slug and default post number in options
	 */
	function add_options() {

		$new_options = array(
			'impress_agents_archive_posts_num' => 9,
			'impress_agents_slug' => 'employees'
		);

		if ( empty($this->options['impress_agents_slug']) && empty($this->options['impress_agents_archive_posts_num']) )  {
			add_option( 'plugin_impress_agents_settings', $new_options );
		}

	}

	/**
	 * Creates our "Employee" post type.
	 */
	function create_post_type() {

		$args = apply_filters( 'impress_agents_post_type_args',
			array(
				'labels' => array(
					'name'					=> __( 'Employees', 'impress_agents' ),
					'singular_name'			=> __( 'Employee', 'impress_agents' ),
					'add_new'				=> __( 'Add New', 'impress_agents' ),
					'add_new_item'			=> __( 'Add New Employee', 'impress_agents' ),
					'edit'					=> __( 'Edit', 'impress_agents' ),
					'edit_item'				=> __( 'Edit Employee', 'impress_agents' ),
					'new_item'				=> __( 'New Employee', 'impress_agents' ),
					'view'					=> __( 'View Employee', 'impress_agents' ),
					'view_item'				=> __( 'View Employee', 'impress_agents' ),
					'search_items'			=> __( 'Search Employees', 'impress_agents' ),
					'not_found'				=> __( 'No employees found', 'impress_agents' ),
					'not_found_in_trash'	=> __( 'No employees found in Trash', 'impress_agents' ),
					'filter_items_list'     => __( 'Filter Employees', 'impress_agents' ),
					'items_list_navigation' => __( 'Employees navigation', 'impress_agents' ),
					'items_list'            => __( 'Employees list', 'impress_agents' )
				),
				'public'		=> true,
				'query_var'		=> true,
				'show_in_rest'  => true,
				'rest_base'     => 'employee',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'menu_position'	=> 5,
				'menu_icon'		=> 'dashicons-groups',
				'has_archive'	=> true,
				'supports'		=> array( 'title', 'editor', 'author', 'comments', 'excerpt', 'thumbnail', 'revisions', 'equity-layouts', 'equity-cpt-archives-settings', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars', 'genesis-cpt-archives-settings', 'publicize', 'wpcom-markdown'),
				'rewrite'		=> array( 'slug' => $this->options['impress_agents_slug'], 'feeds' => true, 'with_front' => false ),
			)
		);

		register_post_type( 'employee', $args );

	}

	function register_meta_boxes() {
		add_meta_box( 'employee_details_metabox', __( 'Employee Info', 'impress_agents' ), array( &$this, 'employee_details_metabox' ), 'employee', 'normal', 'high' );
	}

	function employee_details_metabox() {
		include( dirname( __FILE__ ) . '/views/employee-details-metabox.php' );
	}

	function metabox_save( $post_id, $post ) {

		/** Run only on employees post type save */
		if ( 'employee' != $post->post_type )
			return;

		if ( !isset( $_POST['impress_agents_metabox_nonce'] ) || !wp_verify_nonce( $_POST['impress_agents_metabox_nonce'], 'impress_agents_metabox_save' ) )
	        return $post_id;

	    /** Don't try to save the data under autosave, ajax, or future post */
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	    if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;

	    /** Check permissions */
	    if ( ! current_user_can( 'edit_post', $post_id ) )
	        return;

	    $employee_details = $_POST['impress_agents'];

	    /** Store the employee details custom fields */
	    foreach ( (array) $employee_details as $key => $value ) {

	    	$key = sanitize_key($key);

	    	if($key == '_employee_email') {
	    		$value = sanitize_email($value);
	    	} else {
	    		$value = sanitize_text_field($value);
	    	}

	        /** Save/Update/Delete */
	        if ( $value ) {
	            update_post_meta($post->ID, $key, $value);
	        } else {
	            delete_post_meta($post->ID, $key);
	        }

	    }

	}

	/**
	 * Filter the columns in the "Employees" screen, define our own.
	 */
	function columns_filter ( $columns ) {

		$columns = array(
			'cb'					=> '<input type="checkbox" />',
			'employee_thumbnail'	=> __( 'Thumbnail', 'impress_agents' ),
			'title'					=> __( 'Employee Name', 'impress_agents' ),
			'employee_details'		=> __( 'Details', 'impress_agents' ),
			'employee_tags'			=> __( 'Categories', 'impress_agents' )
		);

		return $columns;

	}

	/**
	 * Filter the data that shows up in the columns in the "Employees" screen, define our own.
	 */
	function columns_data( $column ) {

		global $post, $wp_taxonomies;

		$image_size = 'style="max-width: 115px;"';

		apply_filters( 'impress_agents_admin_employee_details', $admin_details = $this->employee_details['col1']);

		if (isset($_GET["mode"]) && trim($_GET["mode"]) == 'excerpt' ) {
			apply_filters( 'impress_agents_admin_extended_details', $admin_details = $this->employee_details['col1'] + $this->employee_details['col2']);
			$image_size = 'style="max-width: 150px;"';
		}

		$image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');

		switch( $column ) {
			case "employee_thumbnail":
				echo '<p><img src="' . $image[0] . '" alt="employee-thumbnail" ' . $image_size . '/></p>';
				break;
			case "employee_details":
				foreach ( (array) $admin_details as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta($post->ID, $key, true) ) );
				}
				break;
			case "employee_tags":
				_e('<b>Job Type:</b> ' . get_the_term_list( $post->ID, 'job-types', '', ', ', '' ) . '<br />', 'impress_agents');
				_e('<b>Office:</b> ' . get_the_term_list( $post->ID, 'offices', '', ', ', '' ) . '<br />', 'impress_agents');				break;
		}

	}

}
