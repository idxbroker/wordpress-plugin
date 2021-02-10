<?php

add_action( 'after_setup_theme', 'impress_agents_init' );
/**
 * Initialize IMPress Agents.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.9.0
 */
function impress_agents_init() {

	global $_impress_agents, $_impress_agents_taxonomies;

	/** Load textdomain for translation */
	load_plugin_textdomain( 'impress_agents', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	/** Includes */
	require_once( dirname( __FILE__ ) . '/includes/helpers.php' );
	require_once( dirname( __FILE__ ) . '/includes/functions.php' );
	require_once( dirname( __FILE__ ) . '/includes/shortcodes.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-agents.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-taxonomies.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-employee-widget.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-agent-import.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-migrate-old-posts.php' );

	/** Add theme support for post thumbnails if it does not exist */
	if(!current_theme_supports('post-thumbnails')) {
		add_theme_support( 'post-thumbnails' );
	}

	/** Enqueues impress-agents.css style file if it exists and is not deregistered in settings */
	add_action('wp_enqueue_scripts', 'add_impress_agents_main_styles');
	function add_impress_agents_main_styles() {

		$options = get_option('plugin_impress_agents_settings');

		/** Register Font Awesome icons but don't enqueue them */
		wp_register_style( 'font-awesome-5.8.2', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css', array(), '5.8.2' );
		
		if ( !isset($options['impress_agents_stylesheet_load']) ) {
			$options['impress_agents_stylesheet_load'] = 0;
		}

		if ('1' == $options['impress_agents_stylesheet_load'] ) {
			return;
		}

        if ( file_exists( IMPRESS_IDX_DIR . 'assets/css/impress-agents.css') ) {
        	wp_register_style('impress_agents', IMPRESS_IDX_URL . 'assets/css/impress-agents.css', '', null, 'all');
            wp_enqueue_style('impress_agents');
        }
    }

    /** Add admin scripts and styles */
    function impress_agents_admin_scripts_styles() {
        wp_enqueue_style( 'impress_agents_admin_css', IMPRESS_IDX_URL . 'assets/css/impress-agents-admin.css' );

		wp_enqueue_script( 'impress-agents-admin', IMPRESS_IDX_URL . 'assets/js/agents-admin.js', 'media-views' );
		wp_localize_script(
			'impress-agents-admin',
			'impressAgentsAdmin',
			[
				'nonce-impress-agents-data-optout' => wp_create_nonce( 'impress_agents_data_optout_nonce' ),
			]
		);

		$localize_script = array(
			'title'        => __( 'Set Term Image', 'impress_agents' ),
			'button'       => __( 'Set term image', 'impress_agents' )
		);

		/* Pass custom variables to the script. */
		wp_localize_script( 'impress-agents-admin', 'impa_term_image', $localize_script );

		wp_enqueue_media();

	}
	add_action( 'admin_enqueue_scripts', 'impress_agents_admin_scripts_styles' );

	/** Instantiate */
	$_impress_agents = new IMPress_Agents;
	$_impress_agents_taxonomies = new IMPress_Agents_Taxonomies;

	add_action( 'widgets_init', 'impress_agents_register_widgets' );

	/** Make sure is_plugin_active() can be called */
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if(is_plugin_active('genesis-agent-profiles/plugin.php')) {
		add_action( 'wp_loaded', 'impress_agents_migrate' );
	}
}

function impress_agents_migrate() {
	new IMPress_Agents_Migrate();
}

/**
 * Register Widgets that will be used in the IMPress Agents plugin
 *
 * @since 0.9.0
 */
function impress_agents_register_widgets() {

	$widgets = array( 'IMPress_Agents_Widget' );

	foreach ( (array) $widgets as $widget ) {
		register_widget( $widget );
	}

}

/**
 * IMPress Agent Get Install Info.
 *
 * @since 1.1.5
 */
function impress_agents_get_install_info() {
	// Return early if IMPress for IDXB or IMPress Listings is installed and active or if optout is enabled.
	if ( class_exists( 'IDX_Broker_Plugin' ) || class_exists( 'WP_Listings' ) || get_option( 'impress_data_optout' ) ) {
		return;
	}

	$current_info_version         = '1.0.0';
	$previously_sent_info_version = get_option( 'impress_data_sent' );
	if ( empty( $previously_sent_info_version ) || version_compare( $previously_sent_info_version, $current_info_version ) < 0 ) {
		global $wpdb;
		$install_info = [
			'php_version'       => phpversion(),
			'wordpress_version' => get_bloginfo( 'version' ),
			'theme_name'        => wp_get_theme()->get( 'Name' ),
			'db_version'        => $wpdb->dbh->server_info,
			'memory_limit'      => WP_MEMORY_LIMIT,
			'api_key'           => get_option( 'idx_broker_apikey' ),
			'site_url'          => get_site_url(),
			'impress_listings'  => false,
			'impress_agents'    => true,
			'impress_idxb'      => false,
		];

		$response = wp_remote_post(
			'https://hsstezluih.execute-api.us-east-1.amazonaws.com/v1/wp-data',
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $install_info ),
			]
		);

		if ( ! is_wp_error( $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $response_code ) {
				update_option( 'impress_data_sent', $current_info_version );
			}
		}
	}
}
add_action( 'admin_init', 'impress_agents_get_install_info' );

/**
 * IMPress Agent Data Opt-Out.
 *
 * @since 1.1.5
 */
function impress_agents_data_optout() {
	// User capability check.
	if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
		echo 'check permissions';
		wp_die();
	}
	// Validate and process request.
	if ( isset( $_POST['nonce'], $_POST['optout'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_agents_data_optout_nonce' ) ) {
		update_option( 'impress_data_optout', rest_sanitize_boolean( wp_unslash( $_POST['optout'] ) ) );
		echo 'success';
	}
	wp_die();
}
add_action( 'wp_ajax_impress_agents_data_optout', 'impress_agents_data_optout' );
