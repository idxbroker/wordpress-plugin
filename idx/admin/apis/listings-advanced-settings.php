<?php
/**
 * REST API: Listings_Advanced_Settings
 *
 * Adds routes for the listings advanced settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for listings/settings/advanced page routes.
 *
 * Supports GET and POST requests that return/set the listings advanced settings.
 */
class Listings_Advanced_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/advanced' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/advanced' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
				'args'                => [
					'deregisterMainCss'           => [
						'type' => 'boolean',
					],
					'deregisterWidgetCss'         => [
						'type' => 'boolean',
					],
					'sendFormSubmission'          => [
						'type' => 'boolean',
					],
					'formShortcode'               => [
						'type' => 'string',
					],
					'googleMapsAPIKey'            => [
						'type' => 'string',
					],
					'useCustomWrapper'            => [
						'type' => 'boolean',
					],
					'wrapperStart'                => [
						'type' => 'string',
					],
					'wrapperEnd'                  => [
						'type' => 'string',
					],
					'deletePluginDataOnUninstall' => [
						'type' => 'boolean',
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
			'wp_listings_stylesheet_load'         => 0,
			'wp_listings_widgets_stylesheet_load' => 0,
			'wp_listings_idx_lead_form'           => 1,
			'wp_listings_default_form'            => '',
			'wp_listings_gmaps_api_key'           => '',
			'wp_listings_custom_wrapper'          => 0,
			'wp_listings_start_wrapper'           => '',
			'wp_listings_end_wrapper'             => '',
			'wp_listings_uninstall_delete'        => 0,
		];
		$existing = get_option( 'plugin_wp_listings_settings', [] );
		$settings = array_merge( $defaults, $existing );
		return rest_ensure_response(
			[
				'deregisterMainCss'           => boolval( $settings['wp_listings_stylesheet_load'] ),
				'deregisterWidgetCss'         => boolval( $settings['wp_listings_widgets_stylesheet_load'] ),
				'sendFormSubmission'          => boolval( $settings['wp_listings_idx_lead_form'] ),
				'formShortcode'               => $settings['wp_listings_default_form'],
				'googleMapsAPIKey'            => $settings['wp_listings_gmaps_api_key'],
				'useCustomWrapper'            => boolval( $settings['wp_listings_custom_wrapper'] ),
				'wrapperStart'                => $settings['wp_listings_start_wrapper'],
				'wrapperEnd'                  => $settings['wp_listings_end_wrapper'],
				'deletePluginDataOnUninstall' => boolval( $settings['wp_listings_uninstall_delete'] ),
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
		$settings = get_option( 'plugin_wp_listings_settings', [] );

		if ( isset( $payload['deregisterMainCss'] ) ) {
			$settings['wp_listings_stylesheet_load'] = (int) filter_var( $payload['deregisterMainCss'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['deregisterWidgetCss'] ) ) {
			$settings['wp_listings_widgets_stylesheet_load'] = (int) filter_var( $payload['deregisterWidgetCss'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['sendFormSubmission'] ) ) {
			$settings['wp_listings_idx_lead_form'] = (int) filter_var( $payload['sendFormSubmission'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['formShortcode'] ) ) {
			$settings['wp_listings_default_form'] = $payload['formShortcode'];
		}

		if ( isset( $payload['googleMapsAPIKey'] ) ) {
			$settings['wp_listings_gmaps_api_key'] = $payload['googleMapsAPIKey'];
		}

		if ( isset( $payload['useCustomWrapper'] ) ) {
			$settings['wp_listings_custom_wrapper'] = (int) filter_var( $payload['useCustomWrapper'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['wrapperStart'] ) ) {
			$settings['wp_listings_custom_wrapper'] = $payload['wrapperStart'];
		}

		if ( isset( $payload['wrapperEnd'] ) ) {
			$settings['wp_listings_custom_wrapper'] = $payload['wrapperEnd'];
		}

		if ( isset( $payload['deletePluginDataOnUninstall'] ) ) {
			$settings['wp_listings_uninstall_delete'] = (int) filter_var( $payload['deletePluginDataOnUninstall'], FILTER_VALIDATE_BOOLEAN );
		}

		update_option( 'plugin_wp_listings_settings', $settings );

		return new \WP_REST_Response( null, 204 );
	}
}

new Listings_Advanced_Settings();
