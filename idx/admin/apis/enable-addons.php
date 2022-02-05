<?php
/**
 * REST API: Enable_Addons
 *
 * Enables/Disables addons.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for enabling addons.
 */
class Enable_Addons extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers the route $prefix/$addon/enable and sets the wp_option idx_broker_$addon_enabled.
	 * Converts kebab case (used in url) to snake case for wp_options.
	 */
	public function __construct() {
		$this->register_route( 'agents' );
		$this->register_route( 'listings' );
		$this->register_route( 'social-pro', 'settings', false, true );

		// Custom registrations.
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/social-pro/enable' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'social_pro_get' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);
	}

	/**
	 * Check add-on enable status.
	 *
	 * @param string $option_name Add-on wp_option enable flag.
	 * @return WP_REST_Response
	 */
	public function get( $option_name ) {
		$enabled = boolval( get_option( $option_name, 0 ) );

		return rest_ensure_response(
			[
				'enabled' => $enabled,
			]
		);
	}

	/**
	 * Enable/Disable add-on POST.
	 *
	 * @param string $option_name Add-on to enable/disable.
	 * @param string $payload To enable to disable.
	 * @return WP_REST_Response
	 */
	public function post( $option_name, $payload ) {
		$enabled = (int) filter_var( $payload, FILTER_VALIDATE_BOOLEAN );

		$this->deactivate_legacy_plugin( $option_name );

		update_option( $option_name, $enabled );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Register add-on routes.
	 *
	 * @param string $addon Add-on name.
	 * @param string $prefix Route prefix.
	 * @param bool   $get Register GET request.
	 * @param bool   $post Register POST request.
	 * @return void
	 */
	private function register_route( $addon, $prefix = 'settings', $get = true, $post = true ) {
		$wp_option_name = 'idx_broker_' . $addon . '_enabled';
		$wp_option_name = str_replace( '-', '_', $wp_option_name );

		if ( $get ) {
			register_rest_route(
				$this->namespace,
				$this->route_name( "$prefix/$addon/enable" ),
				[
					'methods'             => 'GET',
					'callback'            => function () use ( &$wp_option_name ) {
						return $this->get( $wp_option_name );
					},
					'permission_callback' => [ $this, 'admin_check' ],
				]
			);
		}

		if ( $post ) {
			register_rest_route(
				$this->namespace,
				$this->route_name( "settings/$addon/enable" ),
				[
					'methods'             => 'POST',
					'callback'            => function ( $payload ) use ( &$wp_option_name ) {
						return $this->post( $wp_option_name, $payload['enabled'] );
					},
					'permission_callback' => [ $this, 'admin_check' ],
					'args'                => [
						'enabled' => [
							'type'     => 'boolean',
							'required' => true,
						],
					],
				]
			);
			flush_rewrite_rules();
		}
	}


	/**
	 * Enable/Disable add-on POST.
	 *
	 * @return bool
	 */
	public function social_pro_get() {
		$enabled    = boolval( get_option( 'idx_broker_social_pro_enabled', 0 ) );
		$social_pro = new \IDX\Social_Pro();
		$beta_info  = $social_pro->get_beta_status();
		$output     = [
			'enabled'    => $enabled,
			'subscribed' => $social_pro->get_subscribed_status(),
		];
		$output     = array_merge( $output, $beta_info );

		return rest_ensure_response( $output );
	}

	/**
	 * Deactivates legacy IMPress plugins.
	 *
	 * @param string $addon wp_option enabled key.
	 * @return void
	 */
	private function deactivate_legacy_plugin( $addon ) {
		if ( 'idx_broker_listings_enabled' === $addon ) {
			deactivate_plugins( 'wp-listings/plugin.php', true );
		}
		if ( 'idx_broker_agents_enabled' === $addon ) {
			deactivate_plugins( 'impress-agents/plugin.php', true );
		}
	}
}

new Enable_Addons();
