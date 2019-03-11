<?php
namespace IDX;

/**
 * Initiate_Plugin class.
 */
class Initiate_Plugin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->idx_api = new Idx_Api();

		$this->set_defaults();

		add_action( 'init', array( $this, 'update_triggered' ) );
		add_action( 'wp_head', array( $this, 'display_wpversion' ) );
		add_action( 'wp_head', array( $this, 'idx_broker_activated' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/idx-broker-platinum.php', array( $this, 'idx_broker_platinum_plugin_actlinks' ) );
		// Setting the priority to 9 for admin_menu makes the Wrappers post type UI below the Settings link
		add_action( 'admin_menu', array( $this, 'add_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'idx_broker_platinum_options_init' ) );
		add_action( 'admin_bar_init', array( $this, 'load_admin_menu_styles' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 999.125 );
		add_action( 'admin_init', array( $this, 'disable_original_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'idx_inject_script_and_style' ) );

		add_action( 'wp_ajax_idx_refresh_api', array( $this, 'idx_refreshapi' ) );
		add_action( 'wp_ajax_idx_update_recaptcha_key', array( $this, 'idx_update_recaptcha_key' ) );

		add_action( 'wp_loaded', array( $this, 'schedule_omnibar_update' ) );
		add_action( 'idx_omnibar_get_locations', array( $this, 'idx_omnibar_get_locations' ) );
		add_action( 'idx_migrate_old_table', array( $this, 'migrate_old_table' ) );
		add_action( 'wp_loaded', array( $this, 'legacy_functions' ) );

		add_action( 'plugins_loaded', array( $this, 'idx_extensions' ) );
		add_action( 'plugins_loaded', array( $this, 'add_notices' ) );

		$this->instantiate_classes();
	}

	const IDX_API_DEFAULT_VERSION = '1.6.0';
	const IDX_API_URL             = 'https://api.idxbroker.com';

	/**
	 * instantiate_classes function.
	 *
	 * @access public
	 * @return void
	 */
	public function instantiate_classes() {
		new Wrappers();
		new Idx_Pages();
		new Shortcodes\Register_Idx_Shortcodes();
		new Widgets\Create_Impress_Widgets();
		new Shortcodes\Register_Impress_Shortcodes();
		new Widgets\Omnibar\Create_Omnibar();
		new Shortcodes\Shortcode_Ui();
		new Help();
		new \IDX\Views\Omnibar_Settings();
		new Dashboard_Widget();
		new Backward_Compatibility\Add_Uid_To_Idx_Pages();
		new Backward_Compatibility\Migrate_Legacy_Widgets();
		new \IDX\Views\Lead_Management();
		new \IDX\Views\Search_Management();
		if ( is_multisite() ) {
			 new \IDX\Views\Multisite();
		}
	}

	/**
	 * idx_extensions function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_extensions() {
		if ( class_exists( 'NF_Abstracts_Action' ) ) {
			require_once dirname( __FILE__ ) . '/leads/class-ninja-forms.php';
		}
		if ( class_exists( 'GFForms' ) ) {
			require_once dirname( __FILE__ ) . '/leads/class-gravity-forms.php';
		}
		if ( class_exists( 'WPCF7' ) ) {
			require_once dirname( __FILE__ ) . '/leads/class-contact-form-7.php';
		}
	}

	/**
	 * set_defaults function.
	 *
	 * @access private
	 * @return void
	 */
	private function set_defaults() {
		// Prevent script timeout when API response is slow
		set_time_limit( 0 );

		// The function below adds a settings link to the plugin page.
		$plugin    = plugin_basename( __FILE__ );
		$api_error = false;
	}

	/**
	 * schedule_migrate_old_table function.
	 *
	 * @access public
	 * @return void
	 */
	public function schedule_migrate_old_table() {
		global $wpdb;
		if ( $wpdb->get_var( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'" ) !== null ) {
			if ( ! wp_get_schedule( 'idx_migrate_old_table' ) ) {
				wp_schedule_single_event( time(), 'idx_migrate_old_table' );
			}
		} else {
			// Make sure IDX pages update if migration is not necessary.
			update_option( 'idx_migrated_old_table', true, false );
		}
	}

	/**
	 * migrate_old_table function.
	 *
	 * @access public
	 * @return void
	 */
	public function migrate_old_table() {
		new \IDX\Backward_Compatibility\Migrate_Old_Table();
	}

	/**
	 * plugin_updated function.
	 *
	 * @access public
	 * @return void
	 */
	public function plugin_updated() {
		if ( ! get_option( 'idx_plugin_version' ) || get_option( 'idx_plugin_version' ) < \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION ) {
			return true;
		}
	}

	/**
	 * update_triggered function.
	 *
	 * @access public
	 * @return void
	 */
	public function update_triggered() {
		if ( $this->plugin_updated() ) {
			// update db option and update omnibar data
			update_option( 'idx_plugin_version', \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION, false );
			// clear old api cache
			$this->idx_api->idx_clean_transients();
			$this->idx_omnibar_get_locations();
			return add_action( 'wp_loaded', array( $this, 'schedule_migrate_old_table' ) );
		}
	}

	/**
	 * schedule_omnibar_update function.
	 *
	 * @access public
	 * @return void
	 */
	public function schedule_omnibar_update() {
		if ( ! wp_get_schedule( 'idx_omnibar_get_locations' ) ) {
			// refresh omnibar fields once a day
			wp_schedule_event( time(), 'daily', 'idx_omnibar_get_locations' );
		}
	}

	/**
	 * idx_omnibar_get_locations function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_omnibar_get_locations() {
		new \IDX\Widgets\Omnibar\Get_Locations();
	}

	// Adds a comment declaring the version of the WordPress.
	public function display_wpversion() {
		echo "\n\n<!-- WordPress Version ";
		echo bloginfo( 'version' );
		echo ' -->';
	}

	// Adds a comment declaring the version of the IDX Broker plugin if it is activated.
	public function idx_broker_activated() {
		echo "\n<!-- IDX Broker WordPress Plugin " . \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION . " Activated -->\n";

		echo "<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
		global $post;
		// If wrapper, add noindex tag which is stripped out by our system
		if ( $post && $post->post_type === 'idx-wrapper' ) {
			// If html is being modified we offer filters for developers to modify this tag as needed.
			echo apply_filters( 'idx_activation_meta_tags', "<meta name='idx-robot'>\n<meta name='robots' content='noindex,nofollow'>\n" );
		}
	}

	/**
	 * idx_broker_platinum_plugin_actlinks function.
	 *
	 * @access public
	 * @param mixed $links
	 * @return void
	 */
	public function idx_broker_platinum_plugin_actlinks( $links ) {
		// Add a link to this plugin's settings page
		$settings_link = '<a href="admin.php?page=idx-broker">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * idx_broker_platinum_options_init function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_broker_platinum_options_init() {
		global $api_error;
		// register our settings
		register_setting( 'idx-platinum-settings-group', 'idx_broker_apikey' );
		register_setting( 'idx-platinum-settings-group', 'idx_broker_dynamic_wrapper_page_name' );
		register_setting( 'idx-platinum-settings-group', 'idx_broker_dynamic_wrapper_page_id' );

		/*
		 *  Since we have custom links that can be added and deleted inside
		 *  the IDX Broker admin, we need to grab them and set up the options
		 *  to control them here.  First let's grab them, if the API is not blank.
		 */

		if ( get_option( 'idx_broker_apikey' ) != '' ) {
			$systemlinks = $this->idx_api->idx_api_get_systemlinks();
			if ( is_wp_error( $systemlinks ) ) {
				$api_error = $systemlinks->get_error_message();
			}
		}
	}

	/**
	 * Function to delete existing cache. So API response in cache will be deleted
	 *
	 * @param void
	 * @return void
	 */
	public function idx_refreshapi() {
		$this->idx_api->clear_wrapper_cache();
		$this->idx_api->idx_clean_transients();
		update_option( 'idx_broker_apikey', $_REQUEST['idx_broker_apikey'], false );
		setcookie( 'api_refresh', 1, time() + 20 );
		$this->schedule_omnibar_update();
		wp_die();
	}

	/**
	 * Function to update recaptcha key on admin settings page
	 *
	 * @return void
	 */
	public function idx_update_recaptcha_key() {
		if ( $_POST['idx_recaptcha_site_key'] ) {
			update_option( 'idx_recaptcha_site_key', $_POST['idx_recaptcha_site_key'], false );
			echo 1;
		} else {
			delete_option( 'idx_recaptcha_site_key' );
			echo 'error';
		}
		die();
	}

	/**
	 * load_admin_menu_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function load_admin_menu_styles() {
		wp_enqueue_style( 'properticons', 'https://s3.amazonaws.com/properticons/css/properticons.css' );
		return wp_enqueue_style( 'idx-menus', plugins_url( '/assets/css/idx-menus.css', dirname( __FILE__ ) ) );
	}
	/**
	 * This adds the options page to the WP admin.
	 *
	 * @params void
	 * @return Admin Menu
	 */
	public function add_menu() {
		$notice_num = count( $this->notices );
		add_menu_page(
			'IMPress for IDX Broker Settings',
			\IDX\Views\Notice::menu_text_notice( 'IMPress', $notice_num ),
			'administrator',
			'idx-broker',
			array( $this, 'idx_broker_platinum_admin_page' ),
			'none',
			55.572
		);
		add_submenu_page( 'idx-broker', 'IMPress for IDX Broker Plugin Options', 'Initial Settings', 'administrator', 'idx-broker', array( $this, 'idx_broker_platinum_admin_page' ) );
		// Only add Omnibar page if no errors in API
		$systemlinks = $this->idx_api->idx_api_get_systemlinks();
		if ( ! is_object( $systemlinks ) && ! empty( $systemlinks ) ) {
			add_submenu_page( 'idx-broker', 'Omnibar Settings', 'Omnibar Settings', 'administrator', 'idx-omnibar-settings', array( $this, 'idx_omnibar_settings_interface' ) );
		}
		add_action( 'admin_footer', array( $this, 'add_upgrade_center_link' ) );
	}

	/**
	 * This adds the idx menu items to the admin bar for quick access.
	 *
	 * @params void
	 * @return Admin Menu
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$args = array(
			'id'     => 'idx_admin_bar_menu',
			'title'  => '<span class="ab-icon properticons-logo-idx"></span>IMPress',
			'parent' => false,
			'href'   => admin_url( 'admin.php?page=idx-broker' ),
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_1',
			'title'  => 'IDX Control Panel',
			'parent' => 'idx_admin_bar_menu',
			'href'   => 'https://middleware.idxbroker.com/mgmt/login.php',
			'meta'   => array( 'target' => '_blank' ),
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_2',
			'title'  => 'Knowledgebase',
			'parent' => 'idx_admin_bar_menu',
			'href'   => 'http://support.idxbroker.com',
			'meta'   => array( 'target' => '_blank' ),
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_3',
			'title'  => 'Initial Settings',
			'parent' => 'idx_admin_bar_menu',
			'href'   => admin_url( 'admin.php?page=idx-broker' ),
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_4',
			'title'  => 'Omnibar Settings',
			'parent' => 'idx_admin_bar_menu',
			'href'   => admin_url( 'admin.php?page=idx-omnibar-settings' ),
		);
		// Only add Omnibar page if no errors in API
		$systemlinks = $this->idx_api->idx_api_get_systemlinks();
		if ( ! is_object( $systemlinks ) && ! empty( $systemlinks ) ) {
			$wp_admin_bar->add_node( $args );
		}
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_5',
			'title'  => 'Upgrade Account<i class="fa fa-arrow-up update-plugins"></i>',
			'parent' => 'idx_admin_bar_menu',
			'href'   => 'https://middleware.idxbroker.com/mgmt/upgrade',
			'meta'   => array(
				'target' => '_blank',
			),
		);
		if ( ! $this->idx_api->platinum_account_type() ) {
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 *  Function to add javascript and css into idx setting page
	 *
	 *  @param string $page: the current page
	 */
	public function idx_inject_script_and_style( $page ) {

		wp_enqueue_style( 'idx-notice', IMPRESS_IDX_URL . '/assets/css/idx-notice.css' );

		if ( 'toplevel_page_idx-broker' !== $page ) {
			return;
		}
		wp_enqueue_script( 'idxjs', plugins_url( '/assets/js/idx-broker.min.js', dirname( __FILE__ ) ), 'jquery' );
		wp_localize_script(
			'idxjs',
			'IDXAdminAjax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_enqueue_style( 'idxcss', plugins_url( '/assets/css/idx-broker.css', dirname( __FILE__ ) ) );
	}

	/**
	 * legacy_functions function.
	 *
	 * @access public
	 * @return void
	 */
	public function legacy_functions() {
		// add legacy idx-start functions for older themes
		include 'backward-compatibility' . DIRECTORY_SEPARATOR . 'legacy-functions.php';
	}

	/**
	 * disable_original_plugin function.
	 *
	 * @access public
	 * @return void
	 */
	public function disable_original_plugin() {
		// disable IDX Original Plugin if enabled
		if ( is_plugin_active( 'idx-broker-wordpress-plugin/idx_broker.php' ) ) {
			deactivate_plugins( 'idx-broker-wordpress-plugin/idx_broker.php' );
		}
	}

	/**
	 * This is tiggered and is run by idx_broker_menu, it's the actual IDX Broker Admin page and display.
	 *
	 * @params void
	 * @return void
	 */
	public function idx_broker_platinum_admin_page() {
		include plugin_dir_path( __FILE__ ) . 'views/admin.php';
	}

	/**
	 * idx_omnibar_settings_interface function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_omnibar_settings_interface() {
		$omnibar_settings = new \IDX\Views\Omnibar_Settings();
		// preload current cczs for omnibar settings
		$omnibar_settings->idx_omnibar_settings_interface();
	}

	/**
	 * As WP does not allow external links in the admin menu, this JavaScript adds a link manually.
	 *
	 * @params void
	 * @return void
	 */
	public function add_upgrade_center_link() {
		// Only load if account is not Platinum level
		if ( ! $this->idx_api->platinum_account_type() ) {
			wp_enqueue_style( 'font-awesome-4.7.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );
			$html = '<li><a href="https://middleware.idxbroker.com/mgmt/upgrade" target="_blank">Upgrade Account<i class="fa fa-arrow-up update-plugins"></i></a>';
			echo <<<EOD
            <script>window.addEventListener('DOMContentLoaded',function(){
                document.querySelector('#toplevel_page_idx-broker ul').innerHTML += '$html';
            });</script>
EOD;
		}
	}

	/**
	 * Adds notices property and adds actions for the admin notices and ajax call.
	 *
	 * @access public
	 * @return void
	 */
	public function add_notices() {
		if ( ! is_admin() ) {
			return;
		}

		// Get all active notices and store in object, need this state for the sidebar notice icon
		$this->notices = Notice\Notice_Handler::get_all_notices();

		// If no notices, return
		if ( count( $this->notices ) < 1 ) {
			return;
		}

		// Create admin_notice box for each notice
		foreach ( $this->notices as $notice ) {
			add_action( 'admin_notices', array( $notice, 'create_notice' ) );
		}

		add_action( 'wp_ajax_idx_dismissed', array( '\IDX\Notice\Notice_Handler', 'dismissed' ) );
	}
}
