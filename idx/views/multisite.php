<?php
namespace IDX\Views;

/**
 * Multisite settings page.
 */
class Multisite {

	function __construct() {
		$this->_idx_api = new \IDX\Idx_Api();
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'plugins_loaded', array( $this, 'apply_agent_id_filters' ) );
	}

	/**
	 * Add submenu under Equity menu and register settings
	 */
	function admin_menu() {
		// Register settings to store options
		register_setting( 'impress_multisite_options', 'impress_multisite_settings' );

		// Only display if not main site.
		if ( is_main_site() ) {
			return;
		}
		add_submenu_page(
			'idx-broker',
			'Multisite Settings',
			'Multisite',
			apply_filters( 'impress_multisite_admin_cap', 'manage_options' ),
			'impress-multisite',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Output Multisite settings page
	 */
	function settings_page() {
		$options = get_blog_option( get_current_blog_id(), 'impress_multisite_settings' );

		$defaults = array(
			'agent_id' => false,
		);

		foreach ( $defaults as $name => $value ) {
			if ( ! isset( $options[ $name ] ) ) {
				$options[ $name ] = $value;
			}
		}

		echo '<form action="options.php" method="post" id="impress-multisite-settings-form">';
			settings_fields( 'impress_multisite_options' );

			_e( '<h3>Agent ID</h3>', 'idx-broker-platinum' );
			_e(
				'<p><label for="impress_multisite_settings[agent_id]">Select the Agent ID for leads from this site to be attributed to.</p> <p><strong>Note: Selecting none will use default rules and no agent ID will be appended to any IMPress widgets or forms.</strong></label><br />
				<select name="impress_multisite_settings[agent_id]" id="agent_id" class="agent-id">'
			);
			echo $this->get_agent_select_options( $options['agent_id'] );
			_e( '</select></p>', 'idx-broker-platinum' );

			submit_button( __( 'Save Settings', 'idx-broker-platinum' ) );

		echo '</form>';
	}


	/**
	 * Return agents in option tags for selection.
	 *
	 * @param  $agent_id   The saved agentID
	 * @return string HTML of agents
	 */
	public function get_agent_select_options( $agent_id ) {
		$agents_array = $this->_idx_api->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( ! is_array( $agents_array ) ) {
			return '<option value="">None available</option>';
		}

		$agents_list = '<option value="0">None - Use Default Contact Routing</option>';
		foreach ( $agents_array['agent'] as $agent ) {
			$agents_list .= '<option value="' . $agent['agentID'] . '" ' . selected( $agent['agentID'], $agent_id ) . '>' . $agent['agentDisplayName'] . '</option>';
		}

		return $agents_list;
	}

	/**
	 * Add filters to add an agentID URL param if we have an agent ID set for this site
	 *
	 * @return void
	 */
	public function apply_agent_id_filters() {

		$options = get_blog_option( get_current_blog_id(), 'impress_multisite_settings' );

		if ( isset( $options['agent_id'] ) && ! empty( $options['agent_id'] ) && ! is_main_site() ) {
			add_filter( 'impress_showcase_property_url_suffix', array( $this, 'multisite_add_property_widget_suffix' ), 10, 3 );
			add_filter( 'impress_carousel_property_url_suffix', array( $this, 'multisite_add_property_widget_suffix' ), 10, 3 );
			add_filter( 'impress_lead_signup_agent_id_field', array( $this, 'multisite_lead_signup_agent_id_field' ), 10, 1 );
			add_filter( 'impress_city_links_url_suffix', array( $this, 'multisite_url_with_agent_header_id' ), 10, 2 );
			add_filter( 'impress_idx_page_insert_post_name', array( $this, 'multisite_url_with_agent_header_id' ), 10, 2 );
		}
	}

	/**
	 * Returns a url encoded string including the agentID key and value
	 *
	 * @param  array $suffix    The URL suffix(es). Default empty array.
	 * @param  array $prop      The property in the loop
	 * @param  obj   $idx       The IDX API object
	 *
	 * @return string $suffix   url encoded query string suffix
	 */
	public function multisite_add_property_widget_suffix( $suffix, $prop, $idx ) {
		$options = get_blog_option( get_current_blog_id(), 'impress_multisite_settings' );

		$suffix = array(
			'agentHeaderID' => $options['agent_id'],
		);

		return '?' . http_build_query( $suffix );
	}

	/**
	 * Returns a url encoded string including the agentOwner key and agentID value
	 *
	 * @param  array $field The field. Default empty.
	 * @return string $field The field to return.
	 */
	public function multisite_lead_signup_agent_id_field( $field ) {
		$options = get_blog_option( get_current_blog_id(), 'impress_multisite_settings' );
		return '<input type="hidden" name="agentOwner" value="' . $options['agent_id'] . '">';
	}

	/**
	 * Returns any URL with agentHeaderID param appended
	 *
	 * @param  string $url  The IDX page URL. required
	 * @param  array  $link  The IDX page data. (optional)
	 * @return string        The modified URL.
	 */
	public function multisite_url_with_agent_header_id( $url, $link ) {
		$options = get_blog_option( get_current_blog_id(), 'impress_multisite_settings' );
		return $url . '?agentHeaderID=' . $options['agent_id'];
	}

}
