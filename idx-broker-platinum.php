<?php
/**
Plugin Name: IMPress for IDX Broker
Plugin URI: http://www.idxbroker.com
Description: Over 600 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!
Version: 2.5.10
Author: IDX Broker
Contributors: IDX, LLC
Author URI: http://www.idxbroker.com/
License: GPLv2 or later
 */

new Idx_Broker_Plugin();

/**
 * Idx_Broker_Plugin class.
 */
class Idx_Broker_Plugin {

	// Placed here for convenient updating.
	const IDX_WP_PLUGIN_VERSION = '2.5.10';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		define( 'IMPRESS_IDX_URL', plugin_dir_url( __FILE__ ) );
		define( 'IMPRESS_IDX_DIR', plugin_dir_path( __FILE__ ) );

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
	}

	/**
	 * Check for versions less than PHP5.3 and display error.
	 */
	public function php_version_check() {
		if ( PHP_VERSION < 5.6 ) {
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
            of PHP 5.6.<br>For more information on what versions of PHP are
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

		flush_rewrite_rules();
	} // End idx_activate fn.


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
	}
}
