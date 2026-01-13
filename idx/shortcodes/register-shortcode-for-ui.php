<?php
namespace IDX\Shortcodes;

/**
 * Register_Shortcode_For_Ui class.
 */
class Register_Shortcode_For_Ui {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		add_action( 'wp_ajax_idx_shortcode_options', array( $this, 'get_shortcode_options' ) );
		add_action( 'wp_ajax_idx_shortcode_preview', array( $this, 'shortcode_preview' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'idx_set_shortcode_preview_nonce' ) );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Idx_set_shortcode_preview_nonce function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_set_shortcode_preview_nonce() {
		wp_localize_script( 'idx-shortcode', 'IDXShortcodePreviewNonce', [ wp_create_nonce( 'idx-shortcode-preview-nonce' ) ] );
		wp_localize_script( 'idx-shortcode', 'IDXShortcodeOptionsNonce', [ wp_create_nonce( 'idx-shortcode-options-nonce' ) ] );
	}

	/**
	 * Default_shortcodes function.
	 *
	 * @access public
	 * @return array
	 */
	public function default_shortcodes() {
		$shortcode_types = array(
			'system_links'              => array(
				'name'       => 'System Links',
				'short_name' => 'system_links',
				'icon'       => 'fas fa-star',
			),
			'saved_links'               => array(
				'name'       => 'Saved Links',
				'short_name' => 'saved_links',
				'icon'       => 'fas fa-save',
			),
			'widgets'                   => array(
				'name'       => 'IDX Widgets',
				'short_name' => 'widgets',
				'icon'       => 'fas fa-cog',
			),
			// omnibar extra included as option.
			'omnibar'                   => array(
				'name'       => 'IMPress Omnibar Search',
				'short_name' => 'omnibar',
				'icon'       => 'fas fa-search',
			),
			'impress_city_links'        => array(
				'name'       => 'IMPress City Links',
				'short_name' => 'impress_city_links',
				'icon'       => 'fas fa-link',
			),
			'impress_property_showcase' => array(
				'name'       => 'IMPress Property Showcase',
				'short_name' => 'impress_property_showcase',
				'icon'       => 'fas fa-home',
			),
			'impress_property_carousel' => array(
				'name'       => 'IMPress Property Carousel',
				'short_name' => 'impress_property_carousel',
				'icon'       => 'dashicons dashicons-admin-multisite',
			),
			'impress_lead_login'        => array(
				'name'       => 'IMPress Lead Login Widget',
				'short_name' => 'impress_lead_login',
				'icon'       => 'fas fa-users',
			),
			'idx_wrapper_tags'          => array(
				'name'       => 'IDX Wrapper Tags',
				'short_name' => 'idx_wrapper_tags',
				'icon'       => 'fas fa-code',
			),
		);
		// Only add lead signup shortcode if the account type is Platinum.
		if ( $this->idx_api->engage_account_type() ) {
			$shortcode_types['impress_lead_signup'] = array(
				'name'       => 'IMPress Lead Signup Widget',
				'short_name' => 'impress_lead_signup',
				'icon'       => 'fas fa-user-plus',
			);
		}
		return $shortcode_types;
	}

	/**
	 * Get_shortcode_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_shortcode_options() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) && ! current_user_can( 'publish_pages' ) ) {
			wp_die();
		}
		// Exit early if missing parameters or nonce check fails.
		if ( ! isset( $_POST['idx_shortcode_type'], $_POST['nonce'][0] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'][0] ), 'idx-shortcode-options-nonce' ) ) {
			return;
		}

		$shortcode_type     = sanitize_text_field( $_POST['idx_shortcode_type'] );
		$system_links_check = $this->idx_api->idx_api_get_systemlinks();
		if ( empty( $system_links_check ) || ! empty( $system_links_check->errors ) ) {
			if ( empty( $system_links_check ) ) {
				echo '<p class="error" style="display:block;">No Links to Display</p>';
			} else {
				echo '<p class="error" style="display:block;">' . esc_html( $system_links_check->get_error_message() ) . '</p>';
			}
			wp_die();
		}

		switch ( $shortcode_type ) {
			case 'system_links':
				$this->show_link_short_codes( 0 );
				break;
			case 'saved_links':
				$this->show_link_short_codes( 1 );
				break;
			case 'widgets':
				$this->get_widget_html();
				break;
			case 'omnibar':
				$this->get_omnibar( 'idx-omnibar' );
				break;
			case 'omnibar_extra':
				$this->get_omnibar_extra( 'idx-omnibar-extra' );
				break;
			case 'impress_lead_login':
				$this->get_lead_login( 'impress_lead_login' );
				break;
			case 'impress_lead_signup':
				$this->get_lead_signup( 'impress_lead_signup' );
				break;
			case 'impress_city_links':
				$this->get_city_links( 'impress_city_links' );
				break;
			case 'impress_property_showcase':
				$this->get_property_showcase( 'impress_property_showcase' );
				break;
			case 'impress_property_carousel':
				$this->get_property_carousel( 'impress_property_carousel' );
				break;
			case 'idx_wrapper_tags':
				$this->idx_wrapper_tags();
				break;
		}
		// return html for the desired type for 3rd party plugins.
		do_action( 'idx-get-shortcode-options' );
		wp_die();
	}

	/**
	 * Shortcode_preview function.
	 *
	 * @access public
	 * @return void
	 */
	public function shortcode_preview() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) && ! current_user_can( 'publish_pages' ) ) {
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['idx_shortcode'], $_POST['nonce'][0] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'][0] ), 'idx-shortcode-preview-nonce' ) ) {
			// Output shortcode for shortcode preview.
			$shortcode = stripslashes( sanitize_text_field( $_POST['idx_shortcode'] ) );
			echo wp_kses_post( do_shortcode( $shortcode ) );
		}
		wp_die();
	}

	/**
	 * Idx_wrapper_tags function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_wrapper_tags() {
		echo '<div class="idx-modal-shortcode-field" data-shortcode="idx-wrapper-tags"></div>';
	}

	/**
	 * Get_shortcodes_for_ui function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_shortcodes_for_ui() {
		// add any other types from 3rd party plugins to this interface
		// mimic the default_shortcodes array to make it work.
		$other_shortcodes = do_action( 'idx-register-shortcode-ui' );
		if ( empty( $other_shortcodes ) ) {
			$other_shortcodes = array();
		}
		return array_merge( $this->default_shortcodes(), $other_shortcodes );

	}

	/**
	 * Show_link_short_codes function.
	 *
	 * @access public
	 * @param int $link_type - (default: 0).
	 * @return string
	 */
	public function show_link_short_codes( $link_type = 0 ) {


		if ( 0 === $link_type ) {
			$short_code = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
			$idx_links  = $this->idx_api->idx_api_get_systemlinks();
		} elseif ( 1 === $link_type ) {
			$short_code = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
			$idx_links  = $this->idx_api->idx_api_get_savedlinks();
		} else {
			return false;
		}

		if ( count( $idx_links ) > 0 && is_array( $idx_links ) ) {
			echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $short_code ) . '"><label for="saved-link">Select a Link</label><select id="idx-select-subtype" data-short-name="id" style="width: 100%;">';
			foreach ( $idx_links as $idx_link ) {
				if ( 0 === $link_type ) {
					$this->get_system_link_html( $idx_link );
				}
				if ( 1 === $link_type ) {
					$this->get_saved_link_html( $idx_link );
				}
			}
			echo '</select></div><div class="idx-modal-shortcode-field"><label for="title">Change the Title?</label><input type="text" name="title" id="title" data-short-name="title"></div>';
		} else {
			echo '<div class="each_shortcode_row">No shortcodes available.<br>For instructions on creating Saved Links, see <a href="http://support.idxbroker.com/customer/portal/articles/1913083" target="_blank">this article</a> from our knowledgebase.</div>';
		}

	}

	/**
	 * Get_system_link_html function.
	 *
	 * @access public
	 * @param mixed $idx_link - IDX Links.
	 * @return void
	 */
	public function get_system_link_html( $idx_link ) {
		if ( 1 != $idx_link->systemresults ) {
			$link_short_code = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
			echo '<option id="' . esc_attr( $link_short_code ) . '" value="' . esc_attr( $idx_link->uid ) . '">';
			echo esc_html( $idx_link->name ) . '</option>';
		}
	}

	/**
	 * Get_saved_link_html function.
	 *
	 * @access public
	 * @param mixed $idx_link - IDX Links.
	 * @return void
	 */
	public function get_saved_link_html( $idx_link ) {
		$link_short_code = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
		echo '<option id="' . esc_attr( $link_short_code ) . '" value="' . esc_attr( $idx_link->uid ) . '">';
		echo esc_html( $idx_link->linkTitle ) . '</option>';
	}

	/**
	 * Get_widget_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_widget_html() {
		$idx_widgets      = $this->idx_api->idx_api_get_widgetsrc();
		$widget_shortcode = Register_Idx_Shortcodes::SHORTCODE_WIDGET;

		if ( $idx_widgets ) {
			echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $widget_shortcode ) . '"><label for="widget">Select a Widget</label><select id="idx-select-subtype" data-short-name="id" style="width: 100%;">';
			foreach ( $idx_widgets as $widget ) {
				echo '<option id="' . esc_attr( $widget_shortcode ) . '" value="' . esc_attr( $widget->uid ) . '">' . esc_html( $widget->name ) . '</option>';
			}
			echo '</select></div>';

		} else {
			echo '<div class="each_shortcode_row">No shortcodes available.</div>';
		}
	}

	/**
	 * Get_omnibar function.
	 *
	 * @access public
	 * @param mixed $shortcode - IDX Shortcode.
	 * @return void
	 */
	public function get_omnibar( $shortcode ) {
		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="extra" data-short-name="extra">';
		echo '<label for="extra">Extra Fields?</label>';
		echo '</div>';
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="min_price" data-short-name="min_price">';
		echo '<label for="min_price">Include Min Price? (If Extra Fields is enabled)</label>';
		echo '</div>';
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="remove_price_validation" data-short-name="remove_price_validation">';
		echo '<label for="remove_price_validation">Remove Price Validation (min/step attributes)?</label>';
		echo '</div>';
		echo '<div class="idx-modal-shortcode-field" data-shortcode="idx-omnibar"></div>';
		// Styles and Scripts for Preview.
		echo '<script>';
		// empty url array so styles can be disabled and enabled as expected.
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/idx-omnibar.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';
	}

	/**
	 * Get_lead_login function.
	 *
	 * @access public
	 * @param mixed $shortcode - Shortcode.
	 * @return void
	 */
	public function get_lead_login( $shortcode ) {
		$defaults = array(
			'styles'         => 1,
			'new_window'     => 0,
			'password_field' => false,
		);

		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';
		// New Window.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="new_window" data-short-name="new_window">';
		echo '<label for="new_window">Open in a New Window?</label>';
		echo '</div>';
		// Password field.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="password_field" data-short-name="password_field">';
		echo '<label for="password_field">Add password form field?</label>';
		echo '</div>';
		// Styles and Scripts for Preview.
		echo '<script>';
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/impress-lead-login.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';
	}

	/**
	 * Get_lead_signup function.
	 *
	 * @access public
	 * @param mixed $shortcode - IDX Shortcode.
	 * @return void
	 */
	public function get_lead_signup( $shortcode ) {
		$defaults = array(
			'phone'          => 0,
			'styles'         => 1,
			'new_window'     => 0,
			'agent_id'       => '',
			'password_field' => false,
			'button_text'    => 'Sign Up!',
		);

		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="idx-phone-number" data-short-name="phone">';
		echo '<label for="idx-phone-number">Show phone number field?</label>';
		echo '</div>';

		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';

		// New Window.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="new_window" data-short-name="new_window">';
		echo '<label for="new_window">Open in a New Window?</label>';
		echo '</div>';

		// Styles and Scripts for Preview.
		echo '<script>';
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/impress-lead-signup.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';

		// Password field.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="password_field" data-short-name="password_field">';
		echo '<label for="password_field">Add password form field?</label>';
		echo '</div>';

		// Agent select.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="agent_id">Route to Agent:</label>';
		echo '<select id="agent_id" data-short-name="agent_id">';
		$this->idx_api->get_agents_select_list( $defaults['agent_id'] );
		echo '</select>';
		echo '</div>';

		// Button text.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="button_text">Sign up button text:</label>';
		echo '<input type="text" id="button_text" data-short-name="button_text" value="' . esc_attr( $defaults['button_text'] ) . '">';
		echo '</div>';
	}

	/**
	 * Get_city_links function.
	 *
	 * @access public
	 * @param mixed $shortcode - IDX Shortcode.
	 * @return void
	 */
	public function get_city_links( $shortcode ) {
		$defaults = array(
			'city_list'      => 'combinedActiveMLS',
			'mls'            => '',
			'use_columns'    => 0,
			'number_columns' => 4,
			'styles'         => 1,
			'show_count'     => 0,
			'new_window'     => 0,
		);

		// MLS.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="mls">MLS to use for city links</label>';
		echo '<select id="mls" data-short-name="mls">';
		\IDX\Widgets\Impress_City_Links_Widget::mls_options( $defaults, $this->idx_api );
		echo '</select>';
		echo '</div>';
		// City List.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="city-list">Select a city list</label>';
		echo '<select id="city-list" class="city-list-options" data-short-name="city_list">';
		\IDX\Widgets\Impress_City_Links_Widget::city_list_options( $defaults, $this->idx_api );
		echo '</select>';
		echo '</div>';
		// Use Columns.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="use-columns" data-short-name="use_columns">';
		echo '<label for="use-columns">Split links into columns?</label>';
		echo '</div>';
		// Number of Columns.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="number-columns">Number of columns</label>';
		echo '<select id="number-columns" data-short-name="number_columns">';
		echo '<option value="2">2</option>';
		echo '<option value="3">3</option>';
		echo '<option value="4" selected="selected">4</option>';
		echo '</select>';
		echo '</div>';
		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';
		// Show Count.
		echo '<div class="idx-modal-shortcode-field checkbox show-count" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="show_count" data-short-name="show_count" checked>';
		echo '<label for="show_count">Show Number of Listings for each city?</label>';
		echo '</div>';
		// New Window.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="new_window" data-short-name="new_window">';
		echo '<label for="new_window">Open Links in a New Window?</label>';
		echo '</div>';

		echo '<p>Don&apos;t have any city lists? Go create some in your <a href="http://middleware.idxbroker.com/mgmt/citycountyziplists.php" target="_blank">IDX dashboard.</a></p>';
		// Styles and Scripts for Preview.
		echo '<script>';
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/impress-city-links.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';
	}

	/**
	 * Get_property_showcase function.
	 *
	 * @access public
	 * @param mixed $shortcode - Shortcode.
	 * @return void
	 */
	public function get_property_showcase( $shortcode ) {
		$defaults = array(
			'max'           => 4,
			'use_rows'      => 1,
			'num_per_row'   => 4,
			'show_image'    => 1,
			'order'         => 'default',
			'property_type' => 'featured',
			'saved_link_id' => '',
			'agent_id'      => '',
			'colistings'    => 1,
			'styles'        => 1,
			'new_window'    => 0,
		);

		$output = '';
		// Property type.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="property-type">Properties to Display</label>';
		echo '<select id="property-type" data-short-name="property_type">';
		echo '<option value="featured" selected="selected">Featured</option>';
		echo '<option value="soldpending">Sold/Pending</option>';
		echo '<option value="supplemental">Supplemental</option>';
		echo '<option value="savedlinks">Use Saved Link</option>';
		echo '</select>';
		echo '</div>';

		// Saved link ID.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="saved-link-id">Choose a saved link (if selected above):</label>';
		echo '<select id="saved-link-id" data-short-name="saved_link_id">';
		\IDX\Widgets\Impress_Carousel_Widget::saved_link_options( $defaults, $this->idx_api );
		echo '</select>';
		echo '</div>';

		// Agent select.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="agent_id">Limit by Agent:</label>';
		echo '<select id="agent_id" data-short-name="agent_id">';
		$this->idx_api->get_agents_select_list( $defaults['agent_id'] );
		echo '</select>';
		echo '</div>';

		// Colistings.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="colistings" data-short-name="colistings">';
		echo '<label for="colistings">Include colistings for selected agent?</label>';
		echo '</div>';

		// Images.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="show-image" data-short-name="show_image" checked>';
		echo '<label for="show-image">Show image?</label>';
		echo '</div>';
		// Rows.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="use-rows" data-short-name="use_rows" checked>';
		echo '<label for="use-rows">Use rows?</label>';
		echo '</div>';
		// Per row.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="property-type">Listings per row</label>';
		echo '<select id="property-type" data-short-name="num_per_row">';
		echo '<option value="2">2</option>';
		echo '<option value="3">3</option>';
		echo '<option value="4" selected="selected">4</option>';
		echo '</select>';
		echo '</div>';
		// Max Listings.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="max">Max number of listings to show</label>';
		echo '<input type="number" id="max" data-short-name="max" value="4">';
		echo '</div>';
		// Sort order.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="order">Sort order</label>';
		echo '<select id="order" data-short-name="order">';
		echo '<option value="default" selected="selected">Default</option>';
		echo '<option value="high-low">Highest to Lowest Price</option>';
		echo '<option value="low-high">Lowest to Highest Price</option>';
		echo '</select>';
		echo '</div>';
		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';
		// New Window.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="new_window" data-short-name="new_window">';
		echo '<label for="new_window">Open Listings in a New Window?</label>';
		echo '</div>';

		// Styles and Scripts for Preview.
		echo '<script>';
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/impress-showcase.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';

	}

	/**
	 * Get_property_carousel function.
	 *
	 * @access public
	 * @param mixed $shortcode - Shortcode.
	 * @return void
	 */
	public function get_property_carousel( $shortcode ) {
		$defaults = array(
			'max'           => 15,
			'display'       => 3,
			'autoplay'      => 1,
			'order'         => 'default',
			'property_type' => 'featured',
			'saved_link_id' => '',
			'agent_id'      => '',
			'colistings'    => 1,
			'styles'        => 1,
			'new_window'    => 0,
		);

		$output = '';
		// Property type.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="property-type">Properties to Display</label>';
		echo '<select id="property-type" data-short-name="property_type">';
		echo '<option value="featured" selected="selected">Featured</option>';
		echo '<option value="soldpending">Sold/Pending</option>';
		echo '<option value="supplemental">Supplemental</option>';
		echo '<option value="savedlinks">Use Saved Link</option>';
		echo '</select>';
		echo '</div>';

		// Saved Link ID.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="saved-link-id">Choose a saved link (if selected above):</label>';
		echo '<select id="saved-link-id" data-short-name="saved_link_id">';
		\IDX\Widgets\Impress_Carousel_Widget::saved_link_options( $defaults, $this->idx_api );
		echo '</select>';
		echo '</div>';

		// Agent select.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="agent_id">Limit by Agent:</label>';
		echo '<select id="agent_id" data-short-name="agent_id">';
		$this->idx_api->get_agents_select_list( $defaults['agent_id'] );
		echo '</select>';
		echo '</div>';

		// Colistings.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="colistings" data-short-name="colistings">';
		echo '<label for="colistings">Include colistings for selected agent?</label>';
		echo '</div>';

		// Per row.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="display">Listings to show without scrolling</label>';
		echo '<input type="number" id="display" data-short-name="display" value="3">';
		echo '</div>';
		// Max Listings.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="max">Max number of listings to show</label>';
		echo '<input type="number" id="max" data-short-name="max" value="15">';
		echo '</div>';
		// Sort order.
		echo '<div class="idx-modal-shortcode-field" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<label for="order">Sort order</label>';
		echo '<select id="order" data-short-name="order">';
		echo '<option value="default" selected="selected">Default</option>';
		echo '<option value="high-low">Highest to Lowest Price</option>';
		echo '<option value="low-high">Lowest to Highest Price</option>';
		echo '</select>';
		echo '</div>';
		// Autoplay.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="autoplay" data-short-name="autoplay" checked>';
		echo '<label for="autoplay">Autoplay?</label>';
		echo '</div>';
		// Default Styles.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		echo '<label for="styles">Default Styles?</label>';
		echo '</div>';
		// New Window.
		echo '<div class="idx-modal-shortcode-field checkbox" data-shortcode="' . esc_attr( $shortcode ) . '">';
		echo '<input type="checkbox" id="new_window" data-short-name="new_window">';
		echo '<label for="new_window">Open Listings in a New Window?</label>';
		echo '</div>';

		// Styles and Scripts for Preview.
		echo '<script>';
		echo 'styleSheetUrls = ["' . esc_js( plugins_url( '../assets/css/widgets/owl2.carousel.min.css', dirname( __FILE__ ) ) ) . '", "' . esc_js( plugins_url( '../assets/css/widgets/impress-carousel.min.css', dirname( __FILE__ ) ) ) . '"];';
		echo '</script>';

	}

}
