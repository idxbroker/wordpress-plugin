<?php
/**
Plugin Name: IMPress for IDX Broker
Plugin URI: https://idxbroker.com
Description: Over 600 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!
Version: 3.2.7
Author: IDX Broker
Contributors: IDX, LLC
Author URI: https://idxbroker.com
License: GPLv2 or later
 */

new Idx_Broker_Plugin();

/**
 * Idx_Broker_Plugin class.
 */
class Idx_Broker_Plugin {

	// Placed here for convenient updating.
	const IDX_WP_PLUGIN_VERSION = '3.2.7';
	const VUE_DEV_MODE          = false;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		define( 'IMPRESS_IDX_URL', plugin_dir_url( __FILE__ ) );
		define( 'IMPRESS_IDX_DIR', plugin_dir_path( __FILE__ ) );
		define( 'IDX_API_DEFAULT_VERSION', '1.8.0' );
		define( 'IDX_API_URL', 'https://api.idxbroker.com' );

		if ( $this->php_version_check() ) {
			// IDX Autoloader.
			require_once 'idx' . DIRECTORY_SEPARATOR . 'autoloader.php';
			// Composer autoload classes.
			require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

			new IDX\Initiate_Plugin();
			/** Function that is executed when plugin is activated. */
			register_activation_hook( __FILE__, array( $this, 'idx_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'idx_deactivate' ) );
		}

		// IMPress Listings.
		if ( boolval( get_option( 'idx_broker_listings_enabled', 0 ) ) && ! is_plugin_active( 'wp-listings/plugin.php' ) ) {
			include_once 'add-ons/listings/plugin.php';
		}
		// IMPress Agents.
		if ( boolval( get_option( 'idx_broker_agents_enabled', 0 ) ) && ! is_plugin_active( 'impress-agents/plugin.php' ) ) {
			include_once 'add-ons/agents/plugin.php';
		}

		// Hide legacy widgets from the legacy block to prevent double-entries when searching for widgets.
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', [ $this, 'hide_from_legacy_block' ] );
	}

	/**
	 * Hide from legacy block.
	 * Prevents existing block widgets from appearing as an option in the legacy widget block.
	 *
	 * @access public
	 * @param array $widget_types - Array of widget handles.
	 * @return array
	 */
	public function hide_from_legacy_block( $widget_types ) {
		$hidden_widget_list = [
			'impress_showcase',
			'impress_carousel',
			'impress_idx_dashboard_widget',
			'impress_city_links',
			'impress_lead_login',
			'impress_lead_signup',
			'idx_omnibar_widget',
		];
		return array_merge( $widget_types, $hidden_widget_list );
	}

	/**
	 * Check for versions less than PHP7.0 and display error.
	 */
	public function php_version_check() {
		if ( version_compare( PHP_VERSION, '7.1.8', '<' ) ) {
			add_action( 'admin_init', array( $this, 'idx_deactivate_plugin' ) );
			add_action( 'admin_notices', array( $this, 'incompatible_message' ) );
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Incompatible Message.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function incompatible_message() {
		echo "<div class=\"error\"><br><div>You are using a deprecated version
            of PHP. This is incompatable with the IDX Broker plugin.
            For security reasons, please contact your host and upgrade to the
            latest stable version of PHP they offer. We recommend a minimum
            of PHP 7.0.<br>For more information on what versions of PHP are
            supported with security updates, see <a
            href=\"http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin\">
            this knowledgebase article</a> and PHP.net's
            <a href=\"http://php.net/supported-versions.php\"
            target=\"_blank\">supported versions page.</a>
            </div><br></div>";
	}

	/**
	 * IDX Deactivate.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function idx_deactivate_plugin() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	/**
	 * IDX Activate.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function idx_activate() {
		if ( ! get_option( 'idx_results_url' ) ) {
			add_option( 'idx_results_url' );
		}

		if ( get_site_option( 'idx_dismiss_review_prompt' ) !== false ) {
			delete_option( 'idx_dismiss_review_prompt' );
		}

		if ( get_site_option( 'idx_review_prompt_time' ) !== false ) {
			delete_option( 'idx_review_prompt_time' );
		}

		// Avoid 404 errors on custom posts such as wrappers by registering them then refreshing the permalink rules.
		$idx_api  = new \IDX\Idx_Api();
		$wrappers = new \IDX\Wrappers( $idx_api );
		$wrappers->register_wrapper_post_type();

		// IMPress Listings.
		if ( boolval( get_option( 'idx_broker_listings_enabled', 0 ) ) && ! is_plugin_active( 'wp-listings/plugin.php' ) ) {
			wp_listings_init();
			global $_wp_listings, $_wp_listings_taxonomies, $_wp_listings_templates;
			$_wp_listings->create_post_type();
			$_wp_listings_taxonomies->register_taxonomies();
		}

		$notice_keys = [ 'wpl_notice_idx', 'wpl_listing_notice_idx' ];
		foreach ( $notice_keys as $notice ) {
			delete_user_meta( get_current_user_id(), $notice );
		}

		// IMPress Agents.
		if ( boolval( get_option( 'idx_broker_agents_enabled', 0 ) ) && ! is_plugin_active( 'impress-agents/plugin.php' ) ) {
			impress_agents_init();
			global $_impress_agents, $_impress_agents_taxonomies;
			$_impress_agents->create_post_type();
			$_impress_agents_taxonomies->register_taxonomies();
		}

		flush_rewrite_rules();
	}


	/**
	 * IDX Deactivate.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function idx_deactivate() {
		// Disable scheduled update for omnibar.
		wp_clear_scheduled_hook( 'idx_omnibar_get_locations' );

		// Disable scheduled IDX Page Update as well.
		\IDX\Idx_Pages::unschedule_idx_page_update();

		// IMPress Listings.
		flush_rewrite_rules();

		$notice_keys = [ 'wpl_notice_idx', 'wpl_listing_notice_idx' ];
		foreach ( $notice_keys as $notice ) {
			delete_user_meta( get_current_user_id(), $notice );
		}
	}
}
