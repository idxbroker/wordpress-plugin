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
	}

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * default_shortcodes function.
	 *
	 * @access public
	 * @return void
	 */
	public function default_shortcodes() {
		$shortcode_types = array(
			'system_links'              => array(
				'name'       => 'System Links',
				'short_name' => 'system_links',
				'icon'       => 'fa fa-star',
			),
			'saved_links'               => array(
				'name'       => 'Saved Links',
				'short_name' => 'saved_links',
				'icon'       => 'fa fa-floppy-o',
			),
			'widgets'                   => array(
				'name'       => 'IDX Widgets',
				'short_name' => 'widgets',
				'icon'       => 'fa fa-cog',
			),
			// omnibar extra included as option
			'omnibar'                   => array(
				'name'       => 'IMPress Omnibar Search',
				'short_name' => 'omnibar',
				'icon'       => 'fa fa-search',
			),
			'impress_city_links'        => array(
				'name'       => 'IMPress City Links',
				'short_name' => 'impress_city_links',
				'icon'       => 'fa fa-link',
			),
			'impress_property_showcase' => array(
				'name'       => 'IMPress Property Showcase',
				'short_name' => 'impress_property_showcase',
				'icon'       => 'fa fa-home',
			),
			'impress_property_carousel' => array(
				'name'       => 'IMPress Property Carousel',
				'short_name' => 'impress_property_carousel',
				'icon'       => 'dashicons dashicons-admin-multisite',
			),
			'impress_lead_login'        => array(
				'name'       => 'IMPress Lead Login Widget',
				'short_name' => 'impress_lead_login',
				'icon'       => 'fa fa-users',
			),
			'idx_wrapper_tags'          => array(
				'name'       => 'IDX Wrapper Tags',
				'short_name' => 'idx_wrapper_tags',
				'icon'       => 'fa fa-cog',
			),
		);
		// Only add lead signup shortcode if the account type is Platinum
		if ( $this->idx_api->platinum_account_type() ) {
			$shortcode_types['impress_lead_signup'] = array(
				'name'       => 'IMPress Lead Signup Widget',
				'short_name' => 'impress_lead_signup',
				'icon'       => 'fa fa-user-plus',
			);
		}
		return $shortcode_types;
	}

	/**
	 * get_shortcode_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_shortcode_options() {
		$shortcode_type     = sanitize_text_field( $_POST['idx_shortcode_type'] );
		$system_links_check = $this->idx_api->idx_api_get_systemlinks();
		if ( empty( $system_links_check ) || ! empty( $system_links_check->errors ) ) {
			if ( empty( $system_links_check ) ) {
				echo '<p class="error" style="display:block;">No Links to Display</p>';
			} else {
				echo '<p class="error" style="display:block;">' . $system_links_check->get_error_message() . '</p>';
			}
			wp_die();
		}

		switch ( $shortcode_type ) {
			case 'system_links':
				echo $this->show_link_short_codes( 0 );
				break;
			case 'saved_links':
				echo $this->show_link_short_codes( 1 );
				break;
			case 'widgets':
				echo $this->get_widget_html();
				break;
			case 'omnibar':
				echo $this->get_omnibar( 'idx-omnibar' );
				break;
			case 'omnibar_extra':
				echo $this->get_omnibar_extra( 'idx-omnibar-extra' );
				break;
			case 'impress_lead_login':
				echo $this->get_lead_login( 'impress_lead_login' );
				break;
			case 'impress_lead_signup':
				echo $this->get_lead_signup( 'impress_lead_signup' );
				break;
			case 'impress_city_links':
				echo $this->get_city_links( 'impress_city_links' );
				break;
			case 'impress_property_showcase':
				echo $this->get_property_showcase( 'impress_property_showcase' );
				break;
			case 'impress_property_carousel':
				echo $this->get_property_carousel( 'impress_property_carousel' );
				break;
			case 'idx_wrapper_tags':
				echo $this->idx_wrapper_tags();
				break;
		}
		// return html for the desired type for 3rd party plugins
		do_action( 'idx-get-shortcode-options' );
		wp_die();
	}

	/**
	 * shortcode_preview function.
	 *
	 * @access public
	 * @return void
	 */
	public function shortcode_preview() {
		// output shortcode for shortcode preview
		$shortcode = sanitize_text_field( $_POST['idx_shortcode'] );
		echo do_shortcode( stripslashes( $shortcode ) );
		wp_die();
	}

	/**
	 * idx_wrapper_tags function.
	 *
	 * @access public
	 * @param mixed $shortcode
	 * @return void
	 */
	public function idx_wrapper_tags( $shortcode ) {
		$output = '<div class="idx-modal-shortcode-field" data-shortcode="idx-wrapper-tags"></div>';

		return $output;
	}

	/**
	 * get_shortcodes_for_ui function.
	 *
	 * @access public
	 * @return void
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
	 * show_link_short_codes function.
	 *
	 * @access public
	 * @param int $link_type (default: 0)
	 * @return void
	 */
	public function show_link_short_codes( $link_type = 0 ) {
		$available_shortcodes = '';

		if ( $link_type === 0 ) {
			$short_code = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
			$idx_links  = $this->idx_api->idx_api_get_systemlinks();
		} elseif ( $link_type == 1 ) {
			$short_code = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
			$idx_links  = $this->idx_api->idx_api_get_savedlinks();
		} else {
			return false;
		}

		if ( count( $idx_links ) > 0 && is_array( $idx_links ) ) {
			$available_shortcodes .= '<div class="idx-modal-shortcode-field" data-shortcode="' . $short_code . '"><label for="saved-link">Select a Link</label><select id="idx-select-subtype" data-short-name="id" style="width: 100%;">';
			foreach ( $idx_links as $idx_link ) {
				if ( $link_type === 0 ) {
					$available_shortcodes .= $this->get_system_link_html( $idx_link );
				}
				if ( $link_type == 1 ) {
					$available_shortcodes .= $this->get_saved_link_html( $idx_link );
				}
			}
			$available_shortcodes .= '</select></div><div class="idx-modal-shortcode-field"><label for="title">Change the Title?</label><input type="text" name="title" id="title" data-short-name="title"></div>';
		} else {
			$available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.<br>For instructions on creating Saved Links, see <a href="http://support.idxbroker.com/customer/portal/articles/1913083" target="_blank">this article</a> from our knowledgebase.</div>';
		}

		return $available_shortcodes;
	}

	/**
	 * get_system_link_html function.
	 *
	 * @access public
	 * @param mixed $idx_link
	 * @return void
	 */
	public function get_system_link_html( $idx_link ) {
		$available_shortcodes = '';

		if ( $idx_link->systemresults != 1 ) {
			$link_short_code       = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
			$available_shortcodes .= '<option id="' . $link_short_code . '" value="' . $idx_link->uid . '">';
			$available_shortcodes .= $idx_link->name . '</option>';
		}
		return $available_shortcodes;
	}

	/**
	 * get_saved_link_html function.
	 *
	 * @access public
	 * @param mixed $idx_link
	 * @return void
	 */
	public function get_saved_link_html( $idx_link ) {
		$available_shortcodes  = '';
		$link_short_code       = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
		$available_shortcodes .= '<option id="' . $link_short_code . '" value="' . $idx_link->uid . '">';
		$available_shortcodes .= $idx_link->linkTitle . '</option>';
		return $available_shortcodes;
	}

	/**
	 * get_widget_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_widget_html() {
		$idx_widgets          = $this->idx_api->idx_api_get_widgetsrc();
		$available_shortcodes = '';
		$widget_shortcode     = Register_Idx_Shortcodes::SHORTCODE_WIDGET;

		if ( $idx_widgets ) {
			$available_shortcodes .= '<div class="idx-modal-shortcode-field" data-shortcode="' . $widget_shortcode . '"><label for="widget">Select a Widget</label><select id="idx-select-subtype" data-short-name="id" style="width: 100%;">';
			foreach ( $idx_widgets as $widget ) {
				$available_shortcodes .= '<option id="' . $widget_shortcode . '" value="' . $widget->uid . '">' . $widget->name . '</option>';
			}
			$available_shortcodes .= '</select></div>';

		} else {
			$available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
		}
		return $available_shortcodes;
	}

	/**
	 * get_omnibar function.
	 *
	 * @access public
	 * @param mixed $shortcode
	 * @return void
	 */
	public function get_omnibar( $shortcode ) {
		// Default Styles
		$output  = "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="extra" data-short-name="extra">';
		$output .= '<label for"extra">Extra Fields?</label>';
		$output .= '</div>';
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="min_price" data-short-name="min_price">';
		$output .= '<label for"min_price">Include Min Price? (If Extra Fields is enabled)</label>';
		$output .= '</div>';
		$output .= '<div class="idx-modal-shortcode-field" data-shortcode="idx-omnibar"></div>';
		// Styles and Scripts for Preview
		$output .= '<script>';
		// empty url array so styles can be disabled and enabled as expected
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/idx-omnibar.min.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';
		return $output;
	}

	/**
	 * get_lead_login function.
	 *
	 * @access public
	 * @param mixed $shortcode
	 * @return void
	 */
	public function get_lead_login( $shortcode ) {
		$defaults = array(
			'styles'         => 1,
			'new_window'     => 0,
			'password_field' => false,
		);

		$output = '';
		// Default Styles
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';
		// New Window
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="new_window" data-short-name="new_window">';
		$output .= '<label for"new_window">Open in a New Window?</label>';
		$output .= '</div>';
		// Password field
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="password_field" data-short-name="password_field">';
		$output .= '<label for"password_field">Add password form field?</label>';
		$output .= '</div>';
		// Styles and Scripts for Preview
		$output .= '<script>';
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/impress-lead-login.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';

		return $output;
	}

	/**
	 * get_lead_signup function.
	 *
	 * @access public
	 * @param mixed $shortcode
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

		$output  = '';
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="idx-phone-number" data-short-name="phone">';
		$output .= '<label for"idx-phone-number">Show phone number field?</label>';
		$output .= '</div>';

		// Default Styles
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';

		// New Window
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="new_window" data-short-name="new_window">';
		$output .= '<label for"new_window">Open in a New Window?</label>';
		$output .= '</div>';

		// Styles and Scripts for Preview
		$output .= '<script>';
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/impress-lead-signup.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';

		// Password field
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="password_field" data-short-name="password_field">';
		$output .= '<label for"password_field">Add password form field?</label>';
		$output .= '</div>';

		// Agent select
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"agent_id">Route to Agent:</label>';
		$output .= '<select id="agent_id" data-short-name="agent_id">';
		$output .= $this->get_agents_select_list( $defaults['agent_id'] );
		$output .= '</select>';
		$output .= '</div>';

		// Button text
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"button_text">Sign up button text:</label>';
		$output .= '<input type="text" id="button_text" data-short-name="button_text" value="' . $defaults['button_text'] . '">';
		$output .= '</div>';

		return $output;
	}

	/**
	 * get_city_links function.
	 *
	 * @access public
	 * @param mixed $shortcode
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

		$approved_mls      = \IDX\Widgets\Impress_City_Links_Widget::mls_options( $defaults, $this->idx_api );
		$city_list_options = \IDX\Widgets\Impress_City_Links_Widget::city_list_options( $defaults, $this->idx_api );

		$output = '';
		// MLS
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"mls">MLS to use for city links</label>';
		$output .= '<select id="mls" data-short-name="mls">';
		$output .= $approved_mls;
		$output .= '</select>';
		$output .= '</div>';
		// City List
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"city-list">Select a city list</label>';
		$output .= '<select id="city-list" class="city-list-options" data-short-name="city_list">';
		$output .= $city_list_options;
		$output .= '</select>';
		$output .= '</div>';
		// Use Columns
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="use-columns" data-short-name="use_columns">';
		$output .= '<label for"use-columns">Split links into columns?</label>';
		$output .= '</div>';
		// Number of Columns
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"number-columns">Number of columns</label>';
		$output .= '<select id="number-columns" data-short-name="number_columns">';
		$output .= '<option value="2">2</option>';
		$output .= '<option value="3">3</option>';
		$output .= '<option value="4" selected="selected">4</option>';
		$output .= '</select>';
		$output .= '</div>';
		// Default Styles
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';
		// Show Count
		$output .= "<div class=\"idx-modal-shortcode-field checkbox show-count\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="show_count" data-short-name="show_count" checked>';
		$output .= '<label for"show_count">Show Number of Listings for each city?</label>';
		$output .= '</div>';
		// New Window
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="new_window" data-short-name="new_window">';
		$output .= '<label for"new_window">Open Links in a New Window?</label>';
		$output .= '</div>';

		$output .= "<p>Don't have any city lists? Go create some in your <a href=\"http://middleware.idxbroker.com/mgmt/citycountyziplists.php\" target=\"_blank\">IDX dashboard.</a></p>";
		// Styles and Scripts for Preview
		$output .= '<script>';
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/impress-city-links.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';

		return $output;
	}

	/**
	 * get_property_showcase function.
	 *
	 * @access public
	 * @param mixed $shortcode
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
			'styles'        => 1,
			'new_window'    => 0,
		);

		$output = '';
		// Property type
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"property-type">Properties to Display</label>';
		$output .= '<select id="property-type" data-short-name="property_type">';
		$output .= '<option value="featured" selected="selected">Featured</option>';
		$output .= '<option value="soldpending">Sold/Pending</option>';
		$output .= '<option value="supplemental">Supplemental</option>';
		$output .= '<option value="savedlinks">Use Saved Link</option>';
		$output .= '</select>';
		$output .= '</div>';

		// Saved link ID
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"saved-link-id">Choose a saved link (if selected above):</label>';
		$output .= '<select id="saved-link-id" data-short-name="saved_link_id">';
		$output .= \IDX\Widgets\Impress_Carousel_Widget::saved_link_options( $defaults, $this->idx_api );
		$output .= '</select>';
		$output .= '</div>';

		// Agent select
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"agent_id">Limit by Agent:</label>';
		$output .= '<select id="agent_id" data-short-name="agent_id">';
		$output .= $this->get_agents_select_list( $defaults['agent_id'] );
		$output .= '</select>';
		$output .= '</div>';

		// Images
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="show-image" data-short-name="show_image" checked>';
		$output .= '<label for"show-image">Show image?</label>';
		$output .= '</div>';
		// Rows
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="use-rows" data-short-name="use_rows" checked>';
		$output .= '<label for"use-rows">Use rows?</label>';
		$output .= '</div>';
		// Per row
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"property-type">Listings per row</label>';
		$output .= '<select id="property-type" data-short-name="num_per_row">';
		$output .= '<option value="2">2</option>';
		$output .= '<option value="3">3</option>';
		$output .= '<option value="4" selected="selected">4</option>';
		$output .= '</select>';
		$output .= '</div>';
		// Max Listings
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"max">Max number of listings to show</label>';
		$output .= '<input type="number" id="max" data-short-name="max" value="4">';
		$output .= '</div>';
		// Sort order
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"order">Sort order</label>';
		$output .= '<select id="order" data-short-name="order">';
		$output .= '<option value="default" selected="selected">Default</option>';
		$output .= '<option value="high-low">Highest to Lowest Price</option>';
		$output .= '<option value="low-high">Lowest to Highest Price</option>';
		$output .= '</select>';
		$output .= '</div>';
		// Default Styles
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';
		// New Window
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="new_window" data-short-name="new_window">';
		$output .= '<label for"new_window">Open Listings in a New Window?</label>';
		$output .= '</div>';

		// Styles and Scripts for Preview
		$output .= '<script>';
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/impress-showcase.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';

		return $output;
	}

	/**
	 * get_property_carousel function.
	 *
	 * @access public
	 * @param mixed $shortcode
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
			'styles'        => 1,
			'new_window'    => 0,
		);

		$output = '';
		// Property type
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"property-type">Properties to Display</label>';
		$output .= '<select id="property-type" data-short-name="property_type">';
		$output .= '<option value="featured" selected="selected">Featured</option>';
		$output .= '<option value="soldpending">Sold/Pending</option>';
		$output .= '<option value="supplemental">Supplemental</option>';
		$output .= '<option value="savedlinks">Use Saved Link</option>';
		$output .= '</select>';
		$output .= '</div>';

		// Saved Link ID
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"saved-link-id">Choose a saved link (if selected above):</label>';
		$output .= '<select id="saved-link-id" data-short-name="saved_link_id">';
		$output .= \IDX\Widgets\Impress_Carousel_Widget::saved_link_options( $defaults, $this->idx_api );
		$output .= '</select>';
		$output .= '</div>';

		// Agent select
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"agent_id">Limit by Agent:</label>';
		$output .= '<select id="agent_id" data-short-name="agent_id">';
		$output .= $this->get_agents_select_list( $defaults['agent_id'] );
		$output .= '</select>';
		$output .= '</div>';

		// Per row
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"display">Listings to show without scrolling</label>';
		$output .= '<input type="number" id="display" data-short-name="display" value="3">';
		$output .= '</div>';
		// Max Listings
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"max">Max number of listings to show</label>';
		$output .= '<input type="number" id="max" data-short-name="max" value="15">';
		$output .= '</div>';
		// Sort order
		$output .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"$shortcode\">";
		$output .= '<label for"order">Sort order</label>';
		$output .= '<select id="order" data-short-name="order">';
		$output .= '<option value="default" selected="selected">Default</option>';
		$output .= '<option value="high-low">Highest to Lowest Price</option>';
		$output .= '<option value="low-high">Lowest to Highest Price</option>';
		$output .= '</select>';
		$output .= '</div>';
		// Autoplay
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="autoplay" data-short-name="autoplay" checked>';
		$output .= '<label for"autoplay">Autoplay?</label>';
		$output .= '</div>';
		// Default Styles
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="styles" data-short-name="styles" checked>';
		$output .= '<label for"styles">Default Styles?</label>';
		$output .= '</div>';
		// New Window
		$output .= "<div class=\"idx-modal-shortcode-field checkbox\" data-shortcode=\"$shortcode\">";
		$output .= '<input type="checkbox" id="new_window" data-short-name="new_window">';
		$output .= '<label for"new_window">Open Listings in a New Window?</label>';
		$output .= '</div>';

		// Styles and Scripts for Preview
		$output .= '<script>';
		$output .= 'styleSheetUrls = ["' . plugins_url( '../assets/css/widgets/owl2.carousel.css', dirname( __FILE__ ) ) . '", "';
		$output .= plugins_url( '../assets/css/widgets/impress-carousel.css', dirname( __FILE__ ) ) . '"];';
		$output .= '</script>';
		$output .= '<script src="https://code.jquery.com/jquery-2.2.2.min.js"></script>';
		$output .= '<script src="' . plugins_url( '../assets/js/owl2.carousel.min.js', dirname( __FILE__ ) ) . '"></script>';

		return $output;
	}


	/**
	 * get_agents_select_list function.
	 *
	 * @access public
	 * @param mixed $agent_id
	 * @return void
	 */
	public function get_agents_select_list( $agent_id ) {
		$agents_array = $this->idx_api->idx_api( 'agents', \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( ! is_array( $agents_array ) ) {
			return;
		}

		if ( $agent_id != null ) {
			$agents_list = '<option value="" ' . selected( $agent_id, '', '' ) . '>Use default routing</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				$agents_list .= '<option value="' . $agent['agent_id'] . '" ' . selected( $agent_id, $agent['agent_id'], 0 ) . '>' . $agent['agentDisplayName'] . '</option>';
			}
		} else {
			$agents_list = '<option value="">All</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				$agents_list .= '<option value="' . $agent['agent_id'] . '">' . $agent['agentDisplayName'] . '</option>';
			}
		}

		return $agents_list;
	}
}
