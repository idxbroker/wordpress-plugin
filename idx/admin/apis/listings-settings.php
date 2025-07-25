<?php
/**
 * REST API: Listings_Settings
 *
 * Adds routes for the listings general settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for listings/settings/general page routes.
 *
 * Supports GET and POST requests that return/set the listings settings.
 */
class Listings_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/general' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/general' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
				'args'                => [
					'defaultState'           => [
						'type' => 'string',
					],
					'currencySymbolSelected' => [
						'type' => 'string',
					],
					'currencyCodeSelected'   => [
						'type' => 'string',
					],
					'displayCurrencyCode'    => [
						'type' => 'boolean',
					],
					'numberOfPosts'          => [
						'type' => 'string',
					],
					'defaultDisclaimer'      => [
						'type' => 'string',
					],
					'listingSlug'            => [
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
			'wp_listings_default_state'         => '',
			'wp_listings_currency_symbol'       => '$',
			'wp_listings_currency_code'         => 'USD',
			'wp_listings_display_currency_code' => 0,
			'wp_listings_archive_posts_num'     => 9,
			'wp_listings_global_disclaimer'     => '',
			'wp_listings_slug'                  => 'listings',
		];
		$existing = get_option( 'plugin_wp_listings_settings', [] );
		$settings = array_merge( $defaults, $existing );

		return rest_ensure_response(
			[
				'defaultState'           => $settings['wp_listings_default_state'],
				'currencySymbolSelected' => $settings['wp_listings_currency_symbol'],
				'currencyCodeSelected'   => $settings['wp_listings_currency_code'],
				'displayCurrencyCode'    => boolval( $settings['wp_listings_display_currency_code'] ),
				'numberOfPosts'          => (int) $settings['wp_listings_archive_posts_num'],
				'defaultDisclaimer'      => $settings['wp_listings_global_disclaimer'],
				'listingSlug'            => $settings['wp_listings_slug'],
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

		if ( isset( $payload['defaultState'] ) ) {
			$settings['wp_listings_default_state'] = $payload['defaultState'];
		}

		if ( isset( $payload['currencySymbolSelected'] ) ) {
			$settings['wp_listings_currency_symbol'] = $payload['currencySymbolSelected'];
		}

		if ( isset( $payload['currencyCodeSelected'] ) ) {
			$settings['wp_listings_currency_code'] = $payload['currencyCodeSelected'];
		}

		if ( isset( $payload['numberOfPosts'] ) ) {
			$settings['wp_listings_archive_posts_num'] = filter_var( $payload['numberOfPosts'], FILTER_VALIDATE_INT );
		}

		if ( isset( $payload['defaultDisclaimer'] ) ) {
			$settings['wp_listings_global_disclaimer'] = $payload['defaultDisclaimer'];
		}

		if ( isset( $payload['listingSlug'] ) ) {
			$settings['wp_listings_slug'] = $payload['listingSlug'];
		}

		if ( isset( $payload['displayCurrencyCode'] ) ) {
			$settings['wp_listings_display_currency_code'] = (int) filter_var( $payload['displayCurrencyCode'], FILTER_VALIDATE_BOOLEAN );
		}

		update_option( 'plugin_wp_listings_settings', $settings );

		return new \WP_REST_Response( null, 204 );
	}
}

new Listings_Settings();
