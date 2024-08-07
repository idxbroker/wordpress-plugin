<?php
namespace IDX;

/**
 * Initiate_Plugin class.
 */
class Initiate_Plugin {

	/** @var Idx_Api $idx_api */
	private $idx_api;

	/** @var array Admin notices for the admin page dashboard. */
	private $notices;

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
		// Setting the priority to 9 for admin_menu makes the Wrappers post type UI below the Settings link.
		add_action( 'admin_menu', array( $this, 'add_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'idx_broker_platinum_options_init' ) );
		add_action( 'admin_bar_init', array( $this, 'load_admin_menu_styles' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 999 );
		add_action( 'admin_init', array( $this, 'disable_original_plugin' ) );
		add_action( 'admin_init', [ $this, 'get_install_info' ] );
		add_action( 'admin_enqueue_scripts', array( $this, 'idx_inject_script_and_style' ) );

		add_action( 'wp_loaded', [ $this, 'register_scripts_and_styles' ] );

		add_action( 'wp_ajax_idx_update_recaptcha_setting', [ $this, 'idx_update_recaptcha_setting' ] );
		add_action( 'wp_ajax_idx_update_data_optout_setting', [ $this, 'idx_update_data_optout_setting' ] );
		add_action( 'wp_ajax_idx_update_dev_partner_key', [ $this, 'idx_update_dev_partner_key' ] );

		add_action( 'wp_loaded', array( $this, 'schedule_omnibar_update' ) );
		add_action( 'idx_omnibar_get_locations', array( $this, 'idx_omnibar_get_locations' ) );
		add_action( 'idx_update_location_data', array( $this, 'idx_update_location_data' ) );
		add_action( 'idx_migrate_old_table', array( $this, 'migrate_old_table' ) );
		add_action( 'wp_loaded', array( $this, 'legacy_functions' ) );

		add_action( 'plugins_loaded', array( $this, 'idx_extensions' ) );
		add_action( 'plugins_loaded', array( $this, 'add_notices' ) );

		add_action( 'rest_api_init', array( $this, 'idx_broker_register_rest_routes' ) );

		add_action( 'wp_print_scripts', [ $this, 'dequeue_conflicts' ] );

		$social_pro = new \IDX\Social_Pro();
		$social_pro->initialize_hooks();
		$social_pro->setup_cron();

		$this->instantiate_classes();
	}

	/**
	 * Dequeue Conflicts.
	 * Used to deal with conflicting plugin scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function dequeue_conflicts() {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			// Only dequeues scripts on the IDX IMPress > General Settings page.
			if ( ! empty( $current_screen->id ) && 'toplevel_page_idx-broker' === $current_screen->id ) {
				// uListings plugin.
				wp_dequeue_script( 'vue.js' );
				wp_deregister_script( 'vue.js' );
				wp_dequeue_script( 'stm-listing-admin' );
				wp_deregister_script( 'stm-listing-admin' );
				wp_dequeue_script( 'stm-map-settings' );
				wp_deregister_script( 'stm-map-settings' );
				// Graphs & Charts plugin.
				wp_dequeue_script( 'Graphs & Charts' );
				wp_deregister_script( 'Graphs & Charts' );
				// Feeds for YouTube Pro Personal
				wp_dequeue_script('feed-builder-vue');
			}
		}
	}

	/**
	 * Instantiate_classes function.
	 *
	 * @access public
	 * @return void
	 */
	public function instantiate_classes() {
		// Custom cron schedules need to be registered before classses are instantiated, otherwise some cron tasks won't be scheduled if a custom cron schedule is selected.
		$this->register_cron_schedules();
		
		new Wrappers();
		new Idx_Pages();
		new Shortcodes\Register_Idx_Shortcodes();
		new Widgets\Create_Impress_Widgets();
		new Shortcodes\Register_Impress_Shortcodes();
		new Widgets\Omnibar\Create_Omnibar();
		new Shortcodes\Shortcode_Ui();
		new Dashboard_Widget();
		new Backward_Compatibility\Add_Uid_To_Idx_Pages();
		new \IDX\Views\Lead_Management();
		new \IDX\Views\Search_Management();
		if ( is_multisite() ) {
			new \IDX\Views\Multisite();
		}
		// Register blocks if Gutenberg is present.
		if ( function_exists( 'register_block_type' ) ) {
			new Register_Blocks();
		}
		// Check if reCAPTCHA option has been set. If it does not exist, a default of 1 will be set.
		get_option( 'idx_recaptcha_enabled', 1 );
	}

	/**
	 * Idx_extensions function.
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
	 * Set_defaults function.
	 *
	 * @access private
	 * @return void
	 */
	private function set_defaults() {
		// Prevent script timeout when API response is slow.
		set_time_limit( 0 );

		// The function below adds a settings link to the plugin page.
		$plugin    = plugin_basename( __FILE__ );
		$api_error = false;
	}

	/**
	 * Schedule_migrate_old_table function.
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
	 * Migrate_old_table function.
	 *
	 * @access public
	 * @return void
	 */
	public function migrate_old_table() {
		new \IDX\Backward_Compatibility\Migrate_Old_Table();
	}

	/**
	 * Plugin_updated function.
	 *
	 * @access public
	 * @return mixed
	 */
	public function plugin_updated() {
		if ( ! get_option( 'idx_plugin_version' ) || get_option( 'idx_plugin_version' ) < \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION ) {
			return true;
		}
	}

	/**
	 * Update_triggered function.
	 *
	 * @access public
	 * @return mixed
	 */
	public function update_triggered() {
		if ( $this->plugin_updated() ) {
			// update db option and update omnibar data.
			update_option( 'idx_plugin_version', \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION, false );
			// clear old api cache.
			$this->idx_api->idx_clean_transients();
			$this->idx_omnibar_get_locations();
			return add_action( 'wp_loaded', array( $this, 'schedule_migrate_old_table' ) );
		}
	}

	/**
	 * Schedule_omnibar_update function.
	 *
	 * @access public
	 * @return void
	 */
	public function schedule_omnibar_update() {
		if ( ! wp_get_schedule( 'idx_omnibar_get_locations' ) ) {
			// refresh omnibar fields once a day.
			wp_schedule_event( time(), 'daily', 'idx_omnibar_get_locations' );
		}
	}

	/**
	 * Idx_omnibar_get_locations function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_omnibar_get_locations() {
		new \IDX\Widgets\Omnibar\Get_Locations();
	}

	/**
	 * Update the location data from the type of update.
	 *
	 * @param  string $type Type of update. ('all', 'address', 'custom')
	 * @access public
	 * @return void
	 */
	public function idx_update_location_data($type) {
		new \IDX\Widgets\Omnibar\Get_Locations($type);
	}

	/**
	 * Display wp version for support troubleshooting.
	 */
	public function display_wpversion() {
		echo "\n\n<!-- WordPress Version ";
		echo bloginfo( 'version' );
		echo ' -->';
	}

	/**
	 * Adds a comment declaring the version of the IDX Broker plugin if it is activated.
	 */
	public function idx_broker_activated() {
		echo "\n<!-- IDX Broker WordPress Plugin " . esc_html( \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION ) . " Activated -->\n";

		echo "<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
		global $post;
		// If wrapper, add noindex tag which is stripped out by our system.
		if ( $post && 'idx-wrapper' === $post->post_type ) {
			// If html is being modified we offer filters for developers to modify this tag as needed.
			echo wp_kses(
				apply_filters( 'idx_activation_meta_tags', "<meta name='idx-robot'>\n<meta name='robots' content='noindex,nofollow'>\n" ),
				[
					'meta' => [
						'name' => [],
						'content' => [],
					],
				]
			);
		}
	}

	/**
	 * Idx_broker_platinum_plugin_actlinks function.
	 *
	 * @access public
	 * @param array $links - array of links.
	 * @return array
	 */
	public function idx_broker_platinum_plugin_actlinks( $links ) {
		// Add a link to this plugin's settings page.
		array_unshift( $links, '<a href="admin.php?page=idx-broker#/settings/general">Settings</a>' );

		// Add guided setup link if no API key is set.
		if ( empty( $this->idx_api->api_key ) ) {
			array_unshift( $links, '<a href="admin.php?page=idx-broker#/guided-setup/welcome">Guided Setup</a>' );
		}

		return $links;
	}

	/**
	 * Idx_broker_platinum_options_init function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_broker_platinum_options_init() {
		global $api_error;
		// register our settings.
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
	 * Function to update recaptcha setting on admin settings page.
	 *
	 * @return void
	 */
	public function idx_update_recaptcha_setting() {
		// User capability check.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['enable_recaptcha'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'idx-settings-recaptcha-nonce' ) ) {
			if ( ! empty( $_POST['enable_recaptcha'] ) ) {
				update_option( 'idx_recaptcha_enabled', 1 );
				echo 'success';
			} else {
				update_option( 'idx_recaptcha_enabled', 0 );
				echo 'success';
			}
		}
		wp_die();
	}

	/**
	 * Function to update data collection optout option on admin settings page.
	 *
	 * @return void
	 */
	public function idx_update_data_optout_setting() {
		// User capability check.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['optout'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'idx-settings-data-optout-nonce' ) ) {
			update_option( 'impress_data_optout', rest_sanitize_boolean( wp_unslash( $_POST['optout'] ) ) );
			echo 'success';
		}
		wp_die();
	}

	/**
	 * Function to update developer account API key on admin settings page.
	 *
	 * @return void
	 */
	public function idx_update_dev_partner_key() {
		// User capability check.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			echo 'Check user permissions, activate plugins capabilities required.';
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['key'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'idx-settings-dev-key-update-nonce' ) ) {
			if ( is_string( $_POST['key'] ) ) {
				update_option( 'idx_broker_dev_partner_key', sanitize_text_field( wp_unslash( $_POST['key'] ) ) );
			}
			echo 'success';
		} else {
			echo 'API key or security nonce validation failed.';
		}
		wp_die();
	}


	/**
	 * Load_admin_menu_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function load_admin_menu_styles() {
		wp_enqueue_style( 'properticons' );
		wp_enqueue_style( 'idx-menus', IMPRESS_IDX_URL . 'assets/css/idx-menus.min.css', [], '1.0.0' );
	}

	/**
	 * This adds the options page to the WP admin.
	 *
	 * @return void
	 */
	public function add_menu() {
		$notice_num = count( $this->notices );
		add_menu_page(
			'IMPress for IDX Broker Settings',
			\IDX\Views\Notice::menu_text_notice( 'IMPress', $notice_num ),
			'manage_options',
			'idx-broker',
			array( $this, 'idx_broker_platinum_admin_page' ),
			'none',
			55.572
		);
		add_submenu_page( 'idx-broker', 'IMPress for IDX Broker Plugin Options', 'General Settings', 'manage_options', 'idx-broker', array( $this, 'idx_broker_platinum_admin_page' ) );
		add_action( 'admin_footer', array( $this, 'add_upgrade_center_link' ) );
	}

	/**
	 * This adds the idx menu items to the admin bar for quick access.
	 *
	 * @param mixed $wp_admin_bar - Admin bar.
	 * @return mixed
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Set top level URL to guided setup if API key is not set, general settings if set.
		$settings_url = empty( $this->idx_api->api_key ) ? 'admin.php?page=idx-broker#/guided-setup/welcome' : 'admin.php?page=idx-broker#/settings/general';
		$args = array(
			'id'     => 'idx_admin_bar_menu',
			'title'  => '<span class="ab-icon properticons-logo-idx"></span>IMPress',
			'parent' => false,
			'href'   => admin_url( $settings_url ),
		);
		$wp_admin_bar->add_node( $args );

		// Guided Setup page if no API key is set.
		if ( empty( $this->idx_api->api_key ) ) {
			$args = array(
				'id'     => 'idx_admin_bar_menu_item_0',
				'title'  => 'Guided Setup',
				'parent' => 'idx_admin_bar_menu',
				'href'   => admin_url( $settings_url ),
			);
			$wp_admin_bar->add_node( $args );
		}

		// General Settings page.
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_1',
			'title'  => 'General Settings',
			'parent' => 'idx_admin_bar_menu',
			'href'   => admin_url( 'admin.php?page=idx-broker#/settings/general' ),
		);
		$wp_admin_bar->add_node( $args );

		// Knowledge Base link.
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_2',
			'title'  => 'Knowledgebase',
			'parent' => 'idx_admin_bar_menu',
			'href'   => 'http://support.idxbroker.com',
			'meta'   => array( 'target' => '_blank' ),
		);
		$wp_admin_bar->add_node( $args );

		// IDXB Control Panel link.
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_3',
			'title'  => 'IDX Control Panel',
			'parent' => 'idx_admin_bar_menu',
			'href'   => 'https://middleware.idxbroker.com/mgmt/login',
			'meta'   => array( 'target' => '_blank' ),
		);
		$wp_admin_bar->add_node( $args );

		// Upgrade prompt link for Lite account users.
		$args = array(
			'id'     => 'idx_admin_bar_menu_item_5',
			'title'  => 'Upgrade Account <svg width="8" height="10" class="update-plugins" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1675 971q0 51-37 90l-75 75q-38 38-91 38-54 0-90-38l-294-293v704q0 52-37.5 84.5t-90.5 32.5h-128q-53 0-90.5-32.5t-37.5-84.5v-704l-294 293q-36 38-90 38t-90-38l-75-75q-38-38-38-90 0-53 38-91l651-651q35-37 90-37 54 0 91 37l651 651q37 39 37 91z" fill="#fff"/></svg>',
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
	 *  Function to add javascript and css into idx setting page.
	 *
	 *  @param string $page - the current page.
	 */
	public function idx_inject_script_and_style( $page ) {
		wp_enqueue_style( 'idx-notice', IMPRESS_IDX_URL . 'assets/css/idx-notice.min.css', [], '1.0.0' );
	}

	/**
	 * Register plugin scripts and styles.
	 */
	public function register_scripts_and_styles() {
		// Styles.
		wp_register_style( 'font-awesome-5.8.2', IMPRESS_IDX_URL . 'assets/css/font-awesome-5.8.2.all.min.css', [], '5.8.2' );
		wp_register_style( 'select2', IMPRESS_IDX_URL . 'assets/css/select2.min.css', [], '4.0.5' );
		wp_register_style( 'properticons', IMPRESS_IDX_URL . 'assets/css/properticons.min.css', '', '1.0.0' );
		wp_register_style( 'idx-material-datatable', IMPRESS_IDX_URL . 'assets/css/datatables.material.min.css', [], '1.10.12' );
		wp_register_style( 'idx-material-style', IMPRESS_IDX_URL . 'assets/css/material.min.css', [], '1.0.0' );
		wp_register_style( 'idx-admin', IMPRESS_IDX_URL . 'assets/css/idx-admin.min.css', [], '1.0.0' );
		wp_register_style( 'idx-material-font', IMPRESS_IDX_URL . 'assets/webfonts/roboto.css', [], '1.0.0' );
		wp_register_style( 'idx-material-icons', IMPRESS_IDX_URL . 'assets/webfonts/material-icons.css', [], '1.0.0' );
		wp_register_style( 'impress-showcase', IMPRESS_IDX_URL . 'assets/css/widgets/impress-showcase.min.css', [], '1.0.0' );
		wp_register_style( 'impress-carousel', IMPRESS_IDX_URL . 'assets/css/widgets/impress-carousel.min.css', [], '1.0.0' );
		wp_register_style( 'impress-city-links', IMPRESS_IDX_URL . 'assets/css/widgets/impress-city-links.min.css', [], '1.0.0' );
		wp_register_style( 'impress-lead-login', IMPRESS_IDX_URL . 'assets/css/widgets/impress-lead-login.min.css', [], '1.0' );
		wp_register_style( 'owl2-css', IMPRESS_IDX_URL . 'assets/css/widgets/owl2.carousel.min.css', [], '1.0.0' );
		// Scripts.
		wp_register_script( 'select2', IMPRESS_IDX_URL . 'assets/js/select2.min.js', [ 'jquery' ], '4.0.5', true );
		wp_register_script( 'idx-material-js', IMPRESS_IDX_URL . 'assets/js/material-1.2.1.min.js', [ 'jquery' ], '1.2.1', true );
		wp_register_script( 'jquery-datatables', IMPRESS_IDX_URL . 'assets/js/jquery.datatables.1.10.12.min.js', [ 'jquery' ], '1.10.12', false );
		wp_localize_script( 'jquery-datatables', 'datatablesajax', [ 'url' => admin_url( 'admin-ajax.php' ) ] );
		wp_register_script( 'dialog-polyfill', IMPRESS_IDX_URL . 'assets/js/dialog-polyfill.js', [], '1.0.0', false );
		wp_register_script( 'impress-lead-signup', IMPRESS_IDX_URL . 'assets/js/idx-lead-signup.min.js', [], '1.0.0', false );
		wp_register_script( 'idx-recaptcha', IMPRESS_IDX_URL . 'assets/js/idx-recaptcha.min.js', [], '1.0.0', false );
		wp_register_script( 'idx-google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=6LcUhOYUAAAAAF694SR5_qDv-ZdRHv77I6ZmSiij', [], '1.0.0', false );
		wp_register_script( 'owl2', IMPRESS_IDX_URL . 'assets/js/owl2.carousel.min.js', [ 'jquery' ], '1.0.0', false );
	}

	/**
	 * Legacy_functions function.
	 *
	 * @access public
	 * @return void
	 */
	public function legacy_functions() {
		// add legacy idx-start functions for older themes.
		include 'backward-compatibility' . DIRECTORY_SEPARATOR . 'legacy-functions.php';
	}

	/**
	 * Disable_original_plugin function.
	 *
	 * @access public
	 * @return void
	 */
	public function disable_original_plugin() {
		// disable IDX Original Plugin if enabled.
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

		$package_json = file_get_contents( IMPRESS_IDX_DIR . 'src/vue/backend/package.json' );
		$package_json = json_decode( $package_json );
		$version      = $package_json->version;
		$dir          = '/assets/vue/backend';
		if ( \Idx_Broker_Plugin::VUE_DEV_MODE ) {
			$dir = 'assets/vue-dev/backend';
		}
		wp_enqueue_script( 'idx-backend', plugins_url( "$dir/admin.js", dirname( __FILE__ ) ), [], $version, true );
		wp_enqueue_style( 'idx-backend', plugins_url( "$dir/admin.css", dirname( __FILE__ ) ), [], $version );
		include plugin_dir_path( __FILE__ ) . 'views/admin.php';
	}

	/**
	 * Idx_omnibar_settings_interface function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_omnibar_settings_interface() {
		$omnibar_settings = new \IDX\Views\Omnibar_Settings();
		// preload current cczs for omnibar settings.
		$omnibar_settings->idx_omnibar_settings_interface();
	}

	/**
	 * As WP does not allow external links in the admin menu, this JavaScript adds a link manually.
	 *
	 * @params void
	 * @return void
	 */
	public function add_upgrade_center_link() {
		// Only load if account is not Platinum level.
		if ( ! $this->idx_api->platinum_account_type() ) {
			wp_enqueue_script( 'idxb-pt-upgrade-options', IMPRESS_IDX_URL . 'assets/js/upgrade-option.min.js', [], '1.0.0', false );
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

		// Get all active notices and store in object, need this state for the sidebar notice icon.
		$this->notices = Notice\Notice_Handler::get_all_notices();

		// If no notices, return.
		if ( count( $this->notices ) < 1 ) {
			return;
		}

		// Create admin_notice box for each notice.
		foreach ( $this->notices as $notice ) {
			add_action( 'admin_notices', array( $notice, 'create_notice' ) );
		}

		add_action( 'wp_ajax_idx_dismissed', array( '\IDX\Notice\Notice_Handler', 'dismissed' ) );
	}

	/**
	 * Get Install Info.
	 *
	 * @since 2.6.7
	 */
	public function get_install_info() {
		// Return early if impress_data_optout is true.
		if ( get_option( 'impress_data_optout' ) ) {
			return;
		}

		$current_info_version         = '1.0.0';
		$previously_sent_info_version = get_option( 'impress_data_sent' );
		if ( empty( get_option( 'impress_data_sent' ) ) || version_compare( $previously_sent_info_version, $current_info_version ) < 0 ) {

			global $wpdb;
			$install_info = [
				'php_version'       => phpversion(),
				'wordpress_version' => get_bloginfo( 'version' ),
				'theme_name'        => wp_get_theme()->get( 'Name' ),
				'db_version'        => $wpdb->dbh->server_info,
				'memory_limit'      => WP_MEMORY_LIMIT,
				'api_key'           => get_option( 'idx_broker_apikey' ),
				'site_url'          => get_site_url(),
				'impress_listings'  => class_exists( 'WP_Listings' ),
				'impress_agents'    => class_exists( 'IMPress_Agents' ),
				'impress_idxb'      => true,
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

	/**
	 * Sets admin rest routes.
	 */
	public function idx_broker_register_rest_routes() {
		new \IDX\Admin\Rest_Controller();
	}

	/**
	 * Sets custom cron schedles.
	 *
	 * @param array $schedules Schedules array to update.
	 * @return array Updates schedules.
	 */
	public function custom_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['five_minutes'] ) ) {
			$schedules['five_minutes'] = [
				'interval' => 5 * 60,
				'display'  => __( 'Once every 5 minutes' ),
			];
		}
		if ( ! isset( $schedules['hourly'] ) ) {
			$schedules['hourly'] = [
				'interval' => 60 * 60,
				'display'  => __( 'Once every hour' ),
			];
		}
		if ( ! isset( $schedules['twice_daily'] ) ) {
			$schedules['twice_daily'] = [
				'interval' => 60 * 60 * 12,
				'display'  => __( 'Twice a day' ),
			];
		}
		if ( ! isset( $schedules['daily'] ) ) {
			$schedules['daily'] = [
				'interval' => 60 * 60 * 24,
				'display'  => __( 'Once every days' ),
			];
		}
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = [
				'interval' => 60 * 60 * 24 * 7,
				'display'  => __( 'Once every week' ),
			];
		}
		if ( ! isset( $schedules['two_weeks'] ) ) {
			$schedules['two_weeks'] = [
				'interval' => 60 * 60 * 24 * 7 * 2,
				'display'  => __( 'Once every two weeks' ),
			];
		}
		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 60 * 60 * 24 * 30,
				'display'  => __( 'Once every thirty days' ),
			];
		}
		return $schedules;
	}

	/**
	 * Register cron schedules filter.
	 *
	 * @return void.
	 */
	public function register_cron_schedules() {
		add_filter( 'cron_schedules', [ $this, 'custom_cron_schedules' ] );
	}
}
