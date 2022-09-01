<?php
/**
 * This file contains the WP_Listings class.
 */

/**
 * This class handles the creation of the "Listings" post type, and creates a
 * UI to display the Listing-specific data on the admin screens.
 *
 */
class WP_Listings {

	public $gmb_settings_page = 'wp-listings-gmb-settings';
	public $adv_fields_page   = 'wp-listings-adv-fields-settings';
	public $settings_field    = 'wp_listings_taxonomies';
	public $menu_page         = 'register-taxonomies';

	public $options;

	/**
	 * Property details array.
	 */
	public $property_details;

	/**
	 * Construct Method.
	 */
	public function __construct() {

		$this->options = get_option( 'plugin_wp_listings_settings' );

		$this->property_details = apply_filters(
			'wp_listings_property_details',
			[
				'col1' => [
					__( 'Price:', 'wp-listings' )                  => '_listing_price',
					__( 'Address:', 'wp-listings' )                => '_listing_address',
					__( 'City:', 'wp-listings' )                   => '_listing_city',
					__( 'County:', 'wp-listings' )                 => '_listing_county',
					__( 'State:', 'wp-listings' )                  => '_listing_state',
					__( 'Country:', 'wp-listings' )                => '_listing_country',
					__( 'ZIP:', 'wp-listings' )                    => '_listing_zip',
					__( 'Subdivision:', 'wp-listings' )            => '_listing_subdivision',
					__( 'MLS #:', 'wp-listings' )                  => '_listing_mls',
					__( 'Open House Time & Date:', 'wp-listings' ) => '_listing_open_house',
				],
				'col2' => [
					__( 'Year Built:', 'wp-listings' )      => '_listing_year_built',
					__( 'Floors:', 'wp-listings' )          => '_listing_floors',
					__( 'Square Feet:', 'wp-listings' )     => '_listing_sqft',
					__( 'Acres:', 'wp-listings' )           => '_listing_acres',
					__( 'Lot Square Feet:', 'wp-listings' ) => '_listing_lot_sqft',
					__( 'Bedrooms:', 'wp-listings' )        => '_listing_bedrooms',
					__( 'Bathrooms:', 'wp-listings' )       => '_listing_bathrooms',
					__( 'Half Bathrooms:', 'wp-listings' )  => '_listing_half_bath',
					__( 'Garage:', 'wp-listings' )          => '_listing_garage',
					__( 'Pool:', 'wp-listings' )            => '_listing_pool',
				],
			]
		);

		$this->extended_property_details = apply_filters(
			'wp_listings_extended_property_details',
			[
				'col1' => [
					__( 'Property Type:', 'wp-listings' ) => '_listing_proptype',
					__( 'Condo:', 'wp-listings' )         => '_listing_condo',
					__( 'Financial:', 'wp-listings' )     => '_listing_financial',
					__( 'Condition:', 'wp-listings' )     => '_listing_condition',
					__( 'Construction:', 'wp-listings' )  => '_listing_construction',
					__( 'Exterior:', 'wp-listings' )      => '_listing_exterior',
					__( 'Fencing:', 'wp-listings' )       => '_listing_fencing',
					__( 'Interior:', 'wp-listings' )      => '_listing_interior',
					__( 'Flooring:', 'wp-listings' )      => '_listing_flooring',
					__( 'Heat/Cool:', 'wp-listings' )     => '_listing_heatcool',
				],
				'col2' => [
					__( 'Lot size:', 'wp-listings' )   => '_listing_lotsize',
					__( 'Location:', 'wp-listings' )   => '_listing_location',
					__( 'Scenery:', 'wp-listings' )    => '_listing_scenery',
					__( 'Community:', 'wp-listings' )  => '_listing_community',
					__( 'Recreation:', 'wp-listings' ) => '_listing_recreation',
					__( 'General:', 'wp-listings' )    => '_listing_general',
					__( 'Inclusions:', 'wp-listings' ) => '_listing_inclusions',
					__( 'Parking:', 'wp-listings' )    => '_listing_parking',
					__( 'Rooms:', 'wp-listings' )      => '_listing_rooms',
					__( 'Laundry:', 'wp-listings' )    => '_listing_laundry',
					__( 'Utilities:', 'wp-listings' )  => '_listing_utilities',
				],
			]
		);

		add_action( 'init', array( $this, 'create_post_type' ) );

		add_filter( 'manage_edit-listing_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );

		add_action( 'admin_menu', array( $this, 'register_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );

		add_action( 'save_post', array( $this, 'save_post' ), 1, 3 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_notices', array( $this, 'hide_empty_thumbnails' ), 10 );

		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_init', array( &$this, 'add_options' ) );
		add_action( 'admin_menu', array( &$this, 'settings_init' ), 15 );
		add_action( 'wp_ajax_update_adv_fields', [ $this, 'update_adv_fields' ] );

	}

	/**
	 * Registers the option to load the stylesheet
	 */
	public function register_settings() {
		register_setting( 'wp_listings_options', 'plugin_wp_listings_settings' );
		register_setting( 'wp_listings_options', 'wp_listings_advanced_field_display_options' );
	}

	/**
	 * Sets default slug and default post number in options
	 */
	public function add_options() {

		$new_options = array(
			'wp_listings_archive_posts_num' => 9,
			'wp_listings_slug' => 'listings'
		);

		if ( empty($this->options['wp_listings_slug']) && empty($this->options['wp_listings_archive_posts_num']) )  {
			add_option( 'plugin_wp_listings_settings', $new_options );
		}

	}

	/**
	 * Adds settings page and IDX Import page to admin menu
	 */
	public function settings_init() {

		// Add Google My Business (gmb) settings page settings page for Platinum accounts.
		if ( class_exists( 'Idx_Broker_Plugin' ) ) {
			$idx_api = new \IDX\Idx_Api();
			if ( $idx_api->platinum_account_type() ) {
				add_submenu_page( 'edit.php?post_type=listing', __( 'IMPress Listings - Google My Business', 'wp-listings' ), __( 'Google My Business', 'wp-listings' ), 'manage_options', $this->gmb_settings_page, array( &$this, 'gmb_settings_page' ) );
			}
		}

		// Advanced Fields menu item.
		$options = get_option( 'plugin_wp_listings_settings' );
		if ( ! empty( $options['wp_listings_import_advanced_fields'] ) ) {
			add_submenu_page( 'edit.php?post_type=listing', __( 'IMPress Listings - Advanced Fields', 'wp-listings' ), __( 'Advanced Fields', 'wp-listings' ), 'manage_options', $this->adv_fields_page, array( &$this, 'adv_fields_page' ) );
		}
	}

	public function update_adv_fields() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( 'Check user permissions' );
		}

		if ( isset( $_POST['formdata'], $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'impress_adv_fields_settings_nonce' ) ) {
			$params = [];
			parse_str( $_POST['formdata'], $params );

			$adv_field_settings = filter_var_array( $params['wp_listings_advanced_field_display_options'], FILTER_SANITIZE_STRING );
			update_option( 'wp_listings_advanced_field_display_options', $adv_field_settings );

			wp_send_json( 'Success' );
		}
		wp_send_json( 'Nonce verification failed' );
	}

	public function gmb_settings_page() {
		include dirname( __FILE__ ) . '/views/wp-listings-gmb-settings.php';
	}

	public function adv_fields_page() {
		include dirname( __FILE__ ) . '/views/wp-listings-adv-fields-settings.php';
	}

	/**
	 * Creates our "Listing" post type.
	 */
	public function create_post_type() {

		$args = apply_filters(
			'wp_listings_post_type_args',
			[
				'labels'                => [
					'name'                  => __( 'Listings', 'wp-listings' ),
					'singular_name'         => __( 'Listing', 'wp-listings' ),
					'add_new'               => __( 'Add New', 'wp-listings' ),
					'add_new_item'          => __( 'Add New Listing', 'wp-listings' ),
					'edit'                  => __( 'Edit', 'wp-listings' ),
					'edit_item'             => __( 'Edit Listing', 'wp-listings' ),
					'new_item'              => __( 'New Listing', 'wp-listings' ),
					'view'                  => __( 'View Listing', 'wp-listings' ),
					'view_item'             => __( 'View Listing', 'wp-listings' ),
					'search_items'          => __( 'Search Listings', 'wp-listings' ),
					'not_found'             => __( 'No listings found', 'wp-listings' ),
					'not_found_in_trash'    => __( 'No listings found in Trash', 'wp-listings' ),
					'filter_items_list'     => __( 'Filter Listings', 'wp-listings' ),
					'items_list_navigation' => __( 'Listings navigation', 'wp-listings' ),
					'items_list'            => __( 'Listings list', 'wp-listings' ),
				],
				'public'                => true,
				'query_var'             => true,
				'show_in_rest'          => true,
				'rest_base'             => 'listing',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'menu_position'         => 5,
				'menu_icon'             => 'dashicons-admin-home',
				'has_archive'           => true,
				'supports'              => [ 'title', 'editor', 'author', 'comments', 'excerpt', 'thumbnail', 'revisions', 'equity-layouts', 'equity-cpt-archives-settings', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars', 'genesis-cpt-archives-settings', 'publicize', 'wpcom-markdown' ],
				'rewrite'               => [ 'slug' => $this->options['wp_listings_slug'], 'feeds' => true, 'with_front' => false ],
			]
		);

		register_post_type( 'listing', $args );

	}

	public function register_meta_boxes() {
		add_meta_box( 'listing_details_metabox', __( 'Property Details', 'wp-listings' ), array( &$this, 'listing_details_metabox' ), 'listing', 'normal', 'high' );
		add_meta_box( 'listing_features_metabox', __( 'Additional Details', 'wp-listings' ), array( &$this, 'listing_features_metabox' ), 'listing', 'normal', 'high' );
		if ( ! class_exists( 'Idx_Broker_Plugin' ) ) {
			add_meta_box( 'idx_metabox', __( 'IDX Broker', 'wp-listings' ), array( &$this, 'idx_metabox' ), 'wp-listings-options', 'side', 'core' );
		}

	}

	public function listing_details_metabox() {
		include dirname( __FILE__ ) . '/views/listing-details-metabox.php';
	}

	public function listing_features_metabox() {
		include dirname( __FILE__ ) . '/views/listing-features-metabox.php';
	}

	public function idx_metabox() {
		include dirname( __FILE__ ) . '/views/idx-metabox.php';
	}

	public function metabox_save( $post_id, $post ) {

		/** Run only on listing post type save */
		if ( 'listing' !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST['wp_listings_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['wp_listings_metabox_nonce'], 'wp_listings_metabox_save' ) ) {
			return $post_id;
		}

		/** Don't try to save the data under autosave, ajax, or future post */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		/** Check permissions */
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$property_details = $_POST['wp_listings'];

		if ( ! isset( $property_details['_listing_hide_price'] ) ) {
				$property_details['_listing_hide_price'] = 0;
		}

		/** Store the property details custom fields */
		foreach ( (array) $property_details as $key => $value ) {
			/** Save/Update/Delete */
			if ( $value ) {
				update_post_meta( $post->ID, $key, $value );
			} else {
				delete_post_meta( $post->ID, $key );
			}
		}
	}

	/**
	 * Filter the columns in the "Listings" screen, define our own.
	 */
	public function columns_filter ( $columns ) {

		$columns = [
			'cb'                => '<input type="checkbox" />',
			'listing_thumbnail' => __( 'Thumbnail', 'wp-listings' ),
			'title'             => __( 'Listing Title', 'wp-listings' ),
			'listing_details'   => __( 'Details', 'wp-listings' ),
			'listing_tags'      => __( 'Tags', 'wp-listings' ),
		];

		return $columns;

	}

	/**
	 * Filter the data that shows up in the columns in the "Listings" screen, define our own.
	 */
	public function columns_data( $column ) {

		global $post, $wp_taxonomies;

		$image_size = 'max-width: 115px;';

		apply_filters( 'wp_listings_admin_listing_details', $admin_details = $this->property_details['col1'] );

		if ( isset( $_GET["mode"] ) && trim( $_GET["mode"] ) == 'excerpt' ) {
			apply_filters( 'wp_listings_admin_extended_details', $admin_details = $this->property_details['col1'] + $this->property_details['col2'] );
			$image_size = 'max-width: 150px;';
		}

		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );

		switch ( $column ) {
			case 'listing_thumbnail':
				echo '<p><img src="' . esc_url( $image[0] ) . '" alt="listing-thumbnail" style="' . esc_attr( $image_size ) . '" /></p>';
				break;
			case 'listing_details':
				foreach ( (array) $admin_details as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta( $post->ID, $key, true ) ) );
				}
				break;
			case 'listing_tags':
				echo '<b>Status</b>: ' . get_the_term_list( $post->ID, 'status', '', ', ', '' ) . '<br />';
				echo '<b>Property Type:</b> ' . get_the_term_list( $post->ID, 'property-types', '', ', ', '' ) . '<br />';
				echo '<b>Location:</b> ' . get_the_term_list( $post->ID, 'locations', '', ', ', '' ) . '<br />';
				echo '<b>Features:</b> ' . get_the_term_list( $post->ID, 'features', '', ', ', '' );
				break;
		}

	}

	/**
	 * Adds query var on saving post to show notice
	 * @param  [type] $post_id [description]
	 * @param  [type] $post    [description]
	 * @param  [type] $update  [description]
	 * @return [type]          [description]
	 */
	public function save_post( $post_id, $post, $update ) {

		if ( 'listing' !== $post->post_type ) {
			return;
		}

		add_filter( 'redirect_post_location', array( &$this, 'add_notice_query_var' ), 99 );
	}

	public function add_notice_query_var( $location ) {
		remove_filter( 'redirect_post_location', array( &$this, 'add_notice_query_var' ), 99 );
		return add_query_arg( array( 'wp-listings' => 'show-notice' ), $location );
	}

	/**
	 * Displays admin notices if show-notice url param exists or edit listing page
	 * @return object current screen
	 * @uses  wp_listings_admin_notice
	 */
	public function admin_notices() {

		$screen = get_current_screen();

		if ( isset( $_GET['wp-listings'] ) || $screen->id == 'edit-listing' ) {
			if ( ! class_exists( 'Idx_Broker_Plugin' ) ) {
				echo wp_kses_post( wp_listings_admin_notice( __( '<strong>Integrate your MLS Listings into WordPress with IDX Broker!</strong> <a href="http://www.idxbroker.com/features/idx-wordpress-plugin">Find out how</a>', 'wp-listings' ), false, 'activate_plugins', ( isset( $_GET['wp-listings'] ) ) ? 'wpl_listing_notice_idx' : 'wpl_notice_idx' ) );
			}
			if( get_option( 'wp_listings_import_progress' ) == true ) {
				echo wp_kses_post( wp_listings_admin_notice( __( '<strong>Your listings are being imported in the background. This notice will dismiss when all selected listings have been imported.</strong>', 'wp-listings' ), false, 'activate_plugins', 'wpl_notice_import_progress' ) );
			}
		}

		return $screen;
	}

	public function hide_empty_thumbnails() {
		echo '<style>.listing_thumbnail>p>img[src=""]{content:url("' . esc_url( IMPRESS_IDX_URL ) . 'assets/images/noPhotoFull.png")}</style>';
	}

}
