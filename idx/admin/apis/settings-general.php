<?php
/**
 * REST API: Settings_General
 *
 * Adds routes for the general settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for general/settings page routes.
 *
 * Supports GET and POST requests that return/set the IDX Broker API key, global wrapper,
 * reCAPTCHA settings, and cron schedule timing.
 */
class Settings_General extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes and creates local IDX API object.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/general' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/general' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'admin_check' ],
				'args'                => [
					'apiKey'          => [
						'type' => 'string',
					],
					'reCAPTCHA'       => [
						'type' => 'boolean',
					],
					'updateFrequency' => [
						'type' => 'string',
						'enum' => [
							'five_minutes',
							'hourly',
							'daily',
							'twice_daily',
							'weekly',
							'two_weeks',
							'monthly',
							'disabled',
						],
					],
					'wrapperName'     => [
						'type' => 'string',
					],
					'optOut'          => [
						'type' => 'boolean',
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/general/apiKeyIsValid' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'api_valid_endpoint_callback' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);
	}

	/**
	 * GET request
	 *
	 * @return WP_REST_Response
	 */
	public function get() {
		return rest_ensure_response(
			[
				'apiKey'          => get_option( 'idx_broker_apikey', '' ),
				'devPartnerKey'   => get_option( 'idx_broker_dev_partner_key', '' ),
				'reCAPTCHA'       => boolval( get_option( 'idx_recaptcha_enabled', 0 ) ),
				'updateFrequency' => get_option( 'idx_cron_schedule', '' ),
				'wrapperName'     => get_option( 'idx_broker_dynamic_wrapper_page_name', '' ),
				'optOut'          => boolval( get_option( 'impress_data_optout', 0 ) ),
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
		if ( isset( $payload['apiKey'] ) ) {
			$error = $this->refresh_api( $payload['apiKey'] );
			if ( is_wp_error( $error ) ) {
				return rest_ensure_response( $error );
			}
		}

		if ( isset( $payload['devPartnerKey'] ) ) {
			$sanitized_key = sanitize_text_field( wp_unslash( $payload['devPartnerKey'] ) );
			update_option( 'idx_broker_dev_partner_key', $sanitized_key, false );
		}

		if ( isset( $payload['reCAPTCHA'] ) ) {
			update_option( 'idx_recaptcha_enabled', (int) filter_var( $payload['reCAPTCHA'], FILTER_VALIDATE_BOOLEAN ) );
		}

		if ( isset( $payload['updateFrequency'] ) ) {
			$error = $this->update_cron_frequency( $payload['updateFrequency'] );
			if ( is_wp_error( $error ) ) {
				return rest_ensure_response( $error );
			}
		}

		if ( isset( $payload['wrapperName'] ) ) {
			$error = $this->create_wrapper( $payload['wrapperName'] );
			if ( is_wp_error( $error ) ) {
				return rest_ensure_response( $error );
			}
		}

		if ( isset( $payload['optOut'] ) ) {
			update_option( 'impress_data_optout', (int) filter_var( $payload['optOut'], FILTER_VALIDATE_BOOLEAN ) );
		}
		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Checks if API key is valid.
	 *
	 * @return bool
	 */
	public function api_valid_endpoint_callback() {
		$api_key = get_option( 'idx_broker_apikey', '' );
		$valid   = true;
		$error   = false;

		// TODO: Speed up this check by caching api_success in wp_options.
		if ( ! $api_key ) {
			$valid = false;
		} else {
			$idx_api = new \Idx\Idx_Api();
			$error   = $this->api_error_test( $idx_api );
			if ( $error ) {
				$valid = false;
			}
		}

		$output = [ 'isValid' => $valid ];
		if ( $error ) {
			$converted_error = $this->convert_idx_api_error( $error );
			$output['error'] = [
				'code'    => $converted_error->get_error_code(),
				'message' => $converted_error->get_error_message(),
				'data'    => $converted_error->get_error_data(),
			];
		}
		return rest_ensure_response( $output );
	}

	/**
	 * Checks if api key is valid.
	 *
	 * @param \IDX\IDX_Api $idx_api API object.
	 * @return WP_Error|null
	 */
	private function api_error_test( $idx_api ) {
		$error        = null;
		$system_links = $idx_api->idx_api_get_systemlinks();
		if ( is_wp_error( $system_links ) ) {
			$error = $system_links;
		}
		return $error;
	}

	/**
	 * Refreshes IDX Broker API settings and sets a cron task.
	 *
	 * @param string $api_key IDX Broker API key.
	 * @return WP_Error|null
	 */
	private function refresh_api( $api_key ) {
		$sanitized_key = sanitize_text_field( wp_unslash( $api_key ) );
		$old_key       = get_option( 'idx_broker_apikey', '' );

		// If the key has changed, we need to clear MLS specific data.
		if ( $old_key !== $sanitized_key ) {
			$this->clear_existing_options();
		}

		update_option( 'idx_broker_apikey', $sanitized_key, false );

		$idx_api = new \Idx\Idx_Api();
		$idx_api->clear_wrapper_cache();
		$idx_api->idx_clean_transients();
		$error = $this->api_error_test( $idx_api );
		if ( $error ) {
			return $this->convert_idx_api_error( $error );
		}

		// Update/store the MLS widget data restriction setting.
		$idx_api->idx_api_get_coming_soon_widget_restriction();

		// Fire an omnibar location update and schedule a daily cron.
		if ( ! wp_get_schedule( 'idx_omnibar_get_locations' ) ) {
			wp_schedule_event( time(), 'daily', 'idx_omnibar_get_locations' );
		}
		new \IDX\Widgets\Omnibar\Get_Locations();
	}

	/**
	 * Checks if api key is valid.
	 *
	 * @param string $wrapper_name Wrapper name.
	 * @return WP_Error|null
	 */
	private function create_wrapper( $wrapper_name ) {
		$idx_wrappers = new \IDX\Wrappers();
		$error        = $idx_wrappers->idx_create_dynamic_page( $wrapper_name );
		return $this->convert_idx_api_error( $error );
	}

	/**
	 * Updates cron update frequency.
	 *
	 * @param string $timing Cron update event recurrence schedule.
	 * @return WP_Error|null
	 */
	private function update_cron_frequency( $timing ) {
		$schedules = wp_get_schedules();
		if ( isset( $schedules[ $timing ] ) || 'disabled' === $timing ) {
			update_option( 'idx_cron_schedule', $timing );
			return null;
		}

		return new \WP_Error(
			'cron_option_unavailable',
			"Update frequency option $timing does not exist.",
			[
				'status' => 500,
			]
		);
	}

	/**
	 * Clears existing plugin options that are tied to a specific account key.
	 * Pulls some data removal code from uninstall.php.
	 *
	 * @return void
	 */
	private function clear_existing_options() {
		global $wpdb;
		// Delete omnibar data.
		$test = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%idx_omnibar%' ) );
		// Delete the autocomplete table (new rest api autcomplete).
		$autocomplete_table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';
		$wpdb->query( "DROP TABLE IF EXISTS $autocomplete_table_name" );

		delete_option( 'idx_broker_omnibar_address_mls' );
		delete_option( 'idx_default_property_types' );
		delete_option( 'idx_broker_social_pro_enabled' );
	}
}

new Settings_General();
