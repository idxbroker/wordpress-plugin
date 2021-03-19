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
		$this->register_route( 'social-pro' );
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

		update_option( $option_name, $enabled );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Register add-on routes.
	 *
	 * @param string $addon Add-on name.
	 * @param string $prefix Route prefix.
	 * @return void
	 */
	private function register_route( $addon, $prefix = 'settings' ) {
		$wp_option_name = 'idx_broker_' . $addon . '_enabled';

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
	}
}

new Enable_Addons();
