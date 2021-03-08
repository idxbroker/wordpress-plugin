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
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get' ),
				'permission_callback' => array( $this, 'agents_enabled' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents/enable' ),
			[
				'methods' 				=> 'GET',
				'callback' 				=> [ $this, 'get_enabled' ],
				'permission_callback' 	=> [ $this, 'admin_check' ]
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents/enable' ),
			[
				'methods'				=> 'POST',
				'callback'				=> [ $this, 'post_enabled' ],
				'permission_callback'	=> [ $this, 'admin_check' ],
				'args'					=> [
					'enabled' => [
						'type' => 'boolean'
					]
				]
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/agents' ),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'post' ),
				'permission_callback' => array( $this, 'agents_enabled' ),
				'args'                => array(
					'deregisterMainCss' => array(
						'type' => 'boolean',
					),
					'numberOfPosts'     => array(
						'type' => 'integer',
					),
					'directorySlug'     => array(
						'type' => 'string',
					),
					'wrapperEnabled'    => array(
						'type' => 'boolean',
					),
					'wrapperStart'      => array(
						'type' => 'string',
					),
					'wrapperEnd'        => array(
						'type' => 'string',
					),
				),
			)
		);
	}

	/**
	 * GET request
	 *
	 * @return WP_REST_Response
	 */
	public function get() {
		$defaults = array(
			'impress_agents_stylesheet_load'   => 0,
			'impress_agents_archive_posts_num' => 9,
			'impress_agents_slug'              => 'employees',
			'impress_agents_custom_wrapper'    => 0,
			'impress_agents_start_wrapper'     => '',
			'impress_agents_end_wrapper'       => '',
		);
		$existing = get_option( 'plugin_impress_agents_settings', array() );
		$settings = array_merge( $defaults, $existing );

		return rest_ensure_response(
			array(
				'deregisterMainCss' => boolval( $settings['impress_agents_stylesheet_load'] ),
				'numberOfPosts'     => (int) $settings['impress_agents_archive_posts_num'],
				'directorySlug'     => $settings['impress_agents_slug'],
				'wrapperEnabled'    => boolval( $settings['impress_agents_custom_wrapper'] ),
				'wrapperStart'      => $settings['impress_agents_start_wrapper'],
				'wrapperEnd'        => $settings['impress_agents_end_wrapper'],
			)
		);
	}

	/**
	 * GET Feature enabled request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_enabled() {
		$existing = boolval(get_option( 'idx_broker_agents_enabled', false ));
		return rest_ensure_response(
			[
				'enabled' => $existing
			]
		);
	}

	/**
	 * POST Enabled feature request.
	 *
	 * @param string $payload Update the enabled status of the feature.
	 * @return WP_REST_Response
	 */
	public function post_enabled( $payload ) {
		if ( isset( $payload['enabled'] ) ) {
			$value = (int) filter_var( $payload['enabled'], FILTER_VALIDATE_BOOLEAN );

			update_option( 'idx_broker_agents_enabled', $value );
		}
		return rest_ensure_response( null );
	}

	/**
	 * POST request
	 *
	 * @param string $payload Settings to update.
	 * @return WP_REST_Response
	 */
	public function post( $payload ) {
		$existing = get_option( 'plugin_impress_agents_settings', array() );

		if ( isset( $payload['deregisterMainCss'] ) ) {
			$existing['impress_agents_stylesheet_load'] = (int) filter_var( $payload['deregisterMainCss'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['numberOfPosts'] ) ) {
			$existing['impress_agents_archive_posts_num'] = filter_var( $payload['numberOfPosts'], FILTER_VALIDATE_INT );
		}

		if ( isset( $payload['directorySlug'] ) ) {
			$existing['impress_agents_slug'] = $payload['directorySlug'];
		}

		if ( isset( $payload['wrapperEnabled'] ) ) {
			$existing['impress_agents_custom_wrapper'] = (int) filter_var( $payload['wrapperEnabled'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['wrapperStart'] ) ) {
			$existing['impress_agents_start_wrapper'] = $payload['wrapperStart'];
		}

		if ( isset( $payload['wrapperEnd'] ) ) {
			$existing['impress_agents_end_wrapper'] = $payload['wrapperEnd'];
		}

		update_option( 'plugin_impress_agents_settings', $existing );

		return rest_ensure_response( null );
	}
}

new Agents_Settings();
