<?php

add_action( 'after_setup_theme', 'wp_listings_init' );
/**
 * Initialize IMPress Listings.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.1.0
 */
function wp_listings_init() {

	global $_wp_listings, $_wp_listings_taxonomies, $_wp_listings_templates;

	define( 'BASE_PLUGINS_DIR', plugin_dir_path( __DIR__ ) );
	define( 'WP_LISTINGS_URL', plugin_dir_url( __FILE__ ) );
	define( 'WP_LISTINGS_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WP_LISTINGS_VERSION', '2.4.1' );

	/** Load textdomain for translation */
	load_plugin_textdomain( 'wp-listings', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	/** Includes */
	require_once( dirname( __FILE__ ) . '/includes/helpers.php' );
	require_once( dirname( __FILE__ ) . '/includes/functions.php' );
	require_once( dirname( __FILE__ ) . '/includes/shortcodes.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-listings.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-listing-import.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-taxonomies.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-listing-template.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-listings-search-widget.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-featured-listings-widget.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-admin-notice.php' );
	require_once( dirname( __FILE__ ) . '/includes/wp-api.php' );
	require_once( dirname( __FILE__ ) . '/includes/integrations/wpl-google-my-business.php' );
	WPL_Google_My_Business::get_instance();

	/** Add theme support for post thumbnails if it does not exist */
	if(!current_theme_supports('post-thumbnails')) {
		add_theme_support( 'post-thumbnails' );
	}

	/** Registers and enqueues scripts for single listings */
	add_action('wp_enqueue_scripts', 'add_wp_listings_scripts');
	function add_wp_listings_scripts() {
		wp_register_script( 'wp-listings-single', IMPRESS_IDX_URL . 'assets/js/single-listing.min.js', [ 'jquery' ], null, true ); // enqueued only on single listings.
		wp_register_script( 'jquery-validate', IMPRESS_IDX_URL . 'assets/js/jquery.validate.min.js', [ 'jquery' ], null, true ); // enqueued only on single listings.
		wp_register_script( 'fitvids', IMPRESS_IDX_URL . 'assets/js/jquery.fitvids.min.js', [ 'jquery' ], null, true ); // enqueued only on single listings.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs', [ 'jquery' ] );
		// Add nonce to wp-listing-single script.
		wp_localize_script(
			'wp-listings-single',
			'impressSingleListing',
			[ 'nonce-listing-inquiry' => wp_create_nonce( 'impress_listing_inquiry_nonce' ) ]
		);
	}

	/** Enqueues wp-listings.min.css style file if it exists and is not deregistered in settings */
	add_action('wp_enqueue_scripts', 'add_wp_listings_main_styles');
	function add_wp_listings_main_styles() {

		$options = get_option('plugin_wp_listings_settings');

		/** Register single styles but don't enqueue them **/
		wp_register_style('wp-listings-single', IMPRESS_IDX_URL . 'assets/css/wp-listings-single.min.css', '', null, 'all');

		/** Register Font Awesome icons but don't enqueue them */
		wp_register_style( 'font-awesome-5.8.2', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css', array(), '5.8.2', 'all' );


		/** Register Properticons but don't enqueue them */
		wp_register_style('properticons', '//s3.amazonaws.com/properticons/css/properticons.css', '', null, 'all');

		if ( !isset($options['wp_listings_stylesheet_load']) ) {
			$options['wp_listings_stylesheet_load'] = 0;
		}

		if ('1' == $options['wp_listings_stylesheet_load'] ) {
			return;
		}

        if ( file_exists( IMPRESS_IDX_DIR . 'assets/css/wp-listings.min.css') ) {
        	wp_register_style('wp_listings', IMPRESS_IDX_URL . 'assets/css/wp-listings.min.css', '', null, 'all');
            wp_enqueue_style('wp_listings');
        }
    }

	/** Enqueues wp-listings-widgets.min.css style file if it exists and is not deregistered in settings */
	add_action('wp_enqueue_scripts', 'add_wp_listings_widgets_styles');
	function add_wp_listings_widgets_styles() {

		$options = get_option('plugin_wp_listings_settings');

		if ( !isset($options['wp_listings_widgets_stylesheet_load']) ) {
			$options['wp_listings_widgets_stylesheet_load'] = 0;
		}

		if ('1' == $options['wp_listings_widgets_stylesheet_load'] ) {
			return;
		}

		if ( file_exists( IMPRESS_IDX_DIR . 'assets/css/wp-listings-widgets.min.css') ) {
			wp_register_style( 'wp_listings_widgets', IMPRESS_IDX_URL . 'assets/css/wp-listings-widgets.min.css', '', null, 'all' );
			wp_enqueue_style( 'wp_listings_widgets' );
		}
	}

	/** Add admin scripts and styles */
	function wp_listings_admin_scripts_styles() {
		$screen_id = get_current_screen();
		if ( 'listing_page_wp-listings-settings' === $screen_id->id || 'listing_page_wp-listings-gmb-settings' === $screen_id->id ) {
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
		}

		if ( 'listing_page_wp-listings-gmb-settings' === $screen_id->id ) {
			$gmb_options = WPL_Google_My_Business::get_instance()->wpl_get_gmb_settings_options();

			wp_enqueue_media();
			wp_register_script( 'impress-gmb-settings', IMPRESS_IDX_URL . 'assets/js/google-my-business-settings.min.js', [], '1.0', true );
			wp_localize_script(
				'impress-gmb-settings',
				'impressGmbAdmin',
				[
					'wp_resource_url'                  => WP_LISTINGS_URL,
					'nonce-gmb-post-now'               => wp_create_nonce( 'impress_gmb_post_now_nonce' ),
					'nonce-gmb-clear-scheduled-posts'  => wp_create_nonce( 'wpl_clear_scheduled_posts_nonce' ),
					'nonce-gmb-get-listing-posts'      => wp_create_nonce( 'impress_gmb_get_listing_posts_nonce' ),
					'nonce-gmb-remove-from-schedule'   => wp_create_nonce( 'impress_gmb_remove_from_schedule_nonce' ),
					'nonce-gmb-update-post-frequency'  => wp_create_nonce( 'impress_gmb_change_posting_frequency_nonce' ),
					'nonce-gmb-dismiss-banner'         => wp_create_nonce( 'impress_gmb_dismiss_banner_nonce' ),
					'nonce-gmb-save-custom-post'       => wp_create_nonce( 'impress_gmb_save_custom_post_nonce' ),
					'nonce-gmb-delete-custom-post'     => wp_create_nonce( 'impress_gmb_delete_custom_post_nonce' ),
					'nonce-gmb-get-posts-data'         => wp_create_nonce( 'impress_gmb_get_posts_data_nonce' ),
					'nonce-gmb-update-scheduled-posts' => wp_create_nonce( 'impress_gmb_update_scheduled_posts_nonce' ),
					// Initial values for frontend.
					'next-scheduled-post-date'         => wp_next_scheduled( 'wp_listings_gmb_auto_post' ),
					'auto-post-frequency'              => $gmb_options['posting_frequency'],
					'instruction-banner-dismissed'     => ( ! empty( $gmb_options['banner_dismissed'] ) ? true : false ),
				]
			);

		}

		wp_enqueue_style( 'wp_listings_admin_css', IMPRESS_IDX_URL . 'assets/css/wp-listings-admin.min.css' );

		/** Enqueue Font Awesome in the Admin if IDX Broker is not installed */
		if (!class_exists( 'Idx_Broker_Plugin' )) {
			wp_enqueue_style( 'font-awesome-5.8.2', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css', array(), '5.8.2' );
			wp_enqueue_style( 'upgrade-icon', IMPRESS_IDX_URL . 'assets/css/wp-listings-upgrade.min.css' );
		}

		global $wp_version;
		$nonce_action = 'wp_listings_admin_notice';

		wp_enqueue_style( 'wp-listings-admin-notice', IMPRESS_IDX_URL . 'assets/css/wp-listings-admin-notice.min.css' );
		wp_enqueue_script( 'wp-listings-admin', IMPRESS_IDX_URL . 'assets/js/listings-admin.min.js', 'media-views' );
		wp_localize_script( 'wp-listings-admin', 'wp_listings_adminL10n', array(
			'ajaxurl'                            => admin_url( 'admin-ajax.php' ),
			'nonce'                              => wp_create_nonce( $nonce_action ),
			'wp_version'                         => $wp_version,
			'dismiss'                            => __( 'Dismiss this notice', 'wp-listings' ),
			'nonce-gmb-logout'                   => wp_create_nonce( 'impress_gmb_logout_nonce' ),
			'nonce-gmb-update-location-settings' => wp_create_nonce( 'impress_gmb_update_location_settings_nonce' ),
			'nonce-gmb-reset-post-time'          => wp_create_nonce( 'wpl_reset_next_post_time_request_nonce' ),
			'nonce-gmb-clear-last-post-status'   => wp_create_nonce( 'wpl_clear_last_post_status_nonce' ),
			'nonce-impress-listings-data-optout' => wp_create_nonce( 'impress_listings_data_optout_nonce' ),
		) );

		$localize_script = array(
			'title'        => __( 'Set Term Image', 'wp-listings' ),
			'button'       => __( 'Set term image', 'wp-listings' )
		);

		/* Pass custom variables to the script. */
		wp_localize_script( 'wp-listings-admin', 'wpl_term_image', $localize_script );

		wp_enqueue_media();

	}
	add_action( 'admin_enqueue_scripts', 'wp_listings_admin_scripts_styles' );



	/** Instantiate */
	$_wp_listings = new WP_Listings;
	$_wp_listings_taxonomies = new WP_Listings_Taxonomies;
	$_wp_listings_templates = new Single_Listing_Template;

	add_action( 'widgets_init', 'wp_listings_register_widgets' );

	/**
	 * Function to add admin notices
	 * @param  string  $message    the error messag text
	 * @param  boolean $error      html class - true for error false for updated
	 * @param  string  $cap_check  required capability
	 * @param  boolean $ignore_key ignore key
	 * @return string              HTML of admin notice
	 *
	 * @since  1.3
	 */
	function wp_listings_admin_notice( $message, $error = false, $cap_check = 'activate_plugins', $ignore_key = false ) {
		$_wp_listings_admin = new WP_Listings_Admin_Notice;
		return $_wp_listings_admin->notice( $message, $error, $cap_check, $ignore_key );
	}

	/**
	 * Admin notice AJAX callback
	 * @since  1.3
	 */
	add_action( 'wp_ajax_wp_listings_admin_notice', 'wp_listings_admin_notice_cb' );
	function wp_listings_admin_notice_cb() {
		$_wp_listings_admin = new WP_Listings_Admin_Notice;
		return $_wp_listings_admin->ajax_cb();
	}

}

/**
 * Register Widgets that will be used in the WP Listings plugin
 *
 * @since 0.1.0
 */
function wp_listings_register_widgets() {

	$widgets = array( 'WP_Listings_Featured_Listings_Widget', 'WP_Listings_Search_Widget' );

	foreach ( (array) $widgets as $widget ) {
		register_widget( $widget );
	}

}

/**
 * Google My Business feature notification for Platinum IDXB users.
 *
 * @since 2.6.0
 */
function gmb_dashboard_notice() {
	if ( ! class_exists( 'Idx_Broker_Plugin' ) ) {
		return;
	}
	global $pagenow;
	$idx_api = new \IDX\Idx_Api();
	if ( 'index.php' === $pagenow && $idx_api->platinum_account_type() ) {
		echo wp_listings_admin_notice( __( '<strong><span style="color:green;">New!</span> Connect IMPress Listings to your verified Google My Business profile to generate and schedule timely posts and photos of your listings. <a href="https://wordpress.org/plugins/wp-listings/" target="_blank">Learn more!</a></strong>', 'wp-listings' ), false, 'manage_categories', 'wpl_gmb_feature_notice' );
	}
}
add_action( 'admin_notices', 'gmb_dashboard_notice' );
