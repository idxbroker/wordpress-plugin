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
	require_once dirname( __FILE__ ) . '/includes/helpers.php';
	require_once dirname( __FILE__ ) . '/includes/functions.php';
	require_once dirname( __FILE__ ) . '/includes/shortcodes.php';
	require_once dirname( __FILE__ ) . '/includes/class-agents.php';
	require_once dirname( __FILE__ ) . '/includes/class-taxonomies.php';
	require_once dirname( __FILE__ ) . '/includes/class-employee-widget.php';
	require_once dirname( __FILE__ ) . '/includes/class-agent-import.php';
	require_once dirname( __FILE__ ) . '/includes/class-migrate-old-posts.php';

	/** Add theme support for post thumbnails if it does not exist */
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
	}

	/** Enqueues impress-agents.min.css style file if it exists and is not deregistered in settings */
	add_action( 'wp_enqueue_scripts', 'add_impress_agents_main_styles' );
	function add_impress_agents_main_styles() {

		$options = get_option( 'plugin_impress_agents_settings' );

		if ( ! isset( $options['impress_agents_stylesheet_load'] ) ) {
			$options['impress_agents_stylesheet_load'] = 0;
		}

		if ( '1' == $options['impress_agents_stylesheet_load'] ) {
			return;
		}

		if ( file_exists( IMPRESS_IDX_DIR . 'assets/css/impress-agents.min.css' ) ) {
			wp_register_style( 'impress_agents', IMPRESS_IDX_URL . 'assets/css/impress-agents.min.css', [], '1.0.0' );
			wp_enqueue_style( 'impress_agents' );
		}
	}

	/** Add admin scripts and styles */
	function impress_agents_admin_scripts_styles() {
		wp_enqueue_style( 'impress_agents_admin_css', IMPRESS_IDX_URL . 'assets/css/impress-agents-admin.min.css', [], '1.0.0' );

		wp_enqueue_script( 'impress-agents-admin', IMPRESS_IDX_URL . 'assets/js/agents-admin.min.js', 'media-views' );

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
	$_impress_agents            = new IMPress_Agents();
	$_impress_agents_taxonomies = new IMPress_Agents_Taxonomies();

	add_action( 'widgets_init', 'impress_agents_register_widgets' );

	/** Make sure is_plugin_active() can be called */
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( 'genesis-agent-profiles/plugin.php' ) ) {
		add_action( 'wp_loaded', 'impress_agents_migrate' );
	}
}

/**
 * IMPress agents migrate
 */
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
