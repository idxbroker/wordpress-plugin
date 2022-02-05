<?php
/**
 * REST API: Agents_Settings
 *
 * Adds routes for the agents settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for agents/settings page routes.
 *
 * Supports GET and POST requests that return/set the agents settings.
 */
class Agents_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'agents_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'agents_enabled' ],
				'args'                => [
					'deregisterMainCss' => [
						'type' => 'boolean',
					],
					'numberOfPosts'     => [
						'type' => 'integer',
					],
					'directorySlug'     => [
						'type' => 'string',
					],
					'wrapperEnabled'    => [
						'type' => 'boolean',
					],
					'wrapperStart'      => [
						'type' => 'string',
					],
					'wrapperEnd'        => [
						'type' => 'string',
					],
				],
			]
		);
	}

	/**
	 * GET request
	 *
	 * @return WP_REST_Response
	 */
	public function get() {
		$defaults = [
			'impress_agents_stylesheet_load'   => 0,
			'impress_agents_archive_posts_num' => 9,
			'impress_agents_slug'              => 'employees',
			'impress_agents_custom_wrapper'    => 0,
			'impress_agents_start_wrapper'     => '',
			'impress_agents_end_wrapper'       => '',
		];
		$existing = get_option( 'plugin_impress_agents_settings', [] );
		$settings = array_merge( $defaults, $existing );

		return rest_ensure_response(
			[
				'deregisterMainCss' => boolval( $settings['impress_agents_stylesheet_load'] ),
				'numberOfPosts'     => (int) $settings['impress_agents_archive_posts_num'],
				'directorySlug'     => $settings['impress_agents_slug'],
				'wrapperEnabled'    => boolval( $settings['impress_agents_custom_wrapper'] ),
				'wrapperStart'      => $settings['impress_agents_start_wrapper'],
				'wrapperEnd'        => $settings['impress_agents_end_wrapper'],
			]
		);
	}

	/**
	 * POST request
	 *
	 * @param string $payload Settings to update.
	 * @return WP_REST_Response
	 */
	public function post( $payload ) {
		$settings = get_option( 'plugin_impress_agents_settings', [] );

		if ( isset( $payload['deregisterMainCss'] ) ) {
			$settings['impress_agents_stylesheet_load'] = (int) filter_var( $payload['deregisterMainCss'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['numberOfPosts'] ) ) {
			$settings['impress_agents_archive_posts_num'] = filter_var( $payload['numberOfPosts'], FILTER_VALIDATE_INT );
		}

		if ( isset( $payload['directorySlug'] ) ) {
			$settings['impress_agents_slug'] = $payload['directorySlug'];
		}

		if ( isset( $payload['wrapperEnabled'] ) ) {
			$settings['impress_agents_custom_wrapper'] = (int) filter_var( $payload['wrapperEnabled'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['wrapperStart'] ) ) {
			$settings['impress_agents_start_wrapper'] = $payload['wrapperStart'];
		}

		if ( isset( $payload['wrapperEnd'] ) ) {
			$settings['impress_agents_end_wrapper'] = $payload['wrapperEnd'];
		}

		update_option( 'plugin_impress_agents_settings', $settings );

		return new \WP_REST_Response( null, 204 );
	}
}

new Agents_Settings();
