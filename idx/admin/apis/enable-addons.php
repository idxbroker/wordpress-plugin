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
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents/enable' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'agents_get' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents/enable' ),
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'agents_post' ],
				'args'     => [
					'enabled' => [
						'type'     => 'boolean',
						'required' => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/enable' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'listings_get' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/enable' ),
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'listings_post' ],
				'args'     => [
					'enabled' => [
						'type'     => 'boolean',
						'required' => true,
					],
				],
			]
		);
	}

	/**
	 * IMPress Agents enabled GET request
	 *
	 * @return WP_REST_Response
	 */
	public function agents_get() {
		$enabled = boolval( get_option( 'idx_broker_agents_enabled', 0 ) );

		return rest_ensure_response(
			[
				'enabled' => $enabled,
			]
		);
	}

	/**
	 * IMPress Agents enabled POST request
	 *
	 * @param string $payload To enable to disable.
	 * @return WP_REST_Response
	 */
	public function agents_post( $payload ) {
		$enabled = (int) filter_var( $payload['enabled'], FILTER_VALIDATE_BOOLEAN );

		update_option( 'idx_broker_agents_enabled', $enabled );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * IMPress Listings enabled GET request
	 *
	 * @return WP_REST_Response
	 */
	public function listings_get() {
		$enabled = boolval( get_option( 'idx_broker_listings_enabled', 0 ) );

		return rest_ensure_response(
			[
				'enabled' => $enabled,
			]
		);
	}

	/**
	 * IMPress Listings enabled POST request
	 *
	 * @param string $payload To enable to disable.
	 * @return WP_REST_Response
	 */
	public function listings_post( $payload ) {
		$enabled = (int) filter_var( $payload['enabled'], FILTER_VALIDATE_BOOLEAN );

		update_option( 'idx_broker_listings_enabled', $enabled );

		return new \WP_REST_Response( null, 204 );
	}
}

new Enable_Addons();
