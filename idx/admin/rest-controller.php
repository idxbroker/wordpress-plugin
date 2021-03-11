<?php
/**
 * REST API: Rest_Controller
 *
 * Parent class for all admin routes using the WordPress REST API.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin;

/**
 * Controller class for admin REST routes
 *
 * All individual routes extend this class.
 */
class Rest_Controller {
	/**
	 * REST route namespace and version.
	 *
	 * @var string
	 */
	public $namespace = 'idxbroker/v1';

	/**
	 * REST subroute name.
	 *
	 * @var string
	 */
	public $resource_name = 'admin';

	/**
	 * Check for if user has persmissions to use REST endpoint.
	 *
	 * @param string $route Route to namespace.
	 * @return string Namespaced route.
	 */
	protected function route_name( $route ) {
		$leading_slash = '/' === $route[0] ? '' : '/';
		return '/' . $this->resource_name . $leading_slash . $route;
	}

	/**
	 * Check if user has persmissions to use REST endpoint.
	 *
	 * @return WP_Error|bool
	 */
	public function admin_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to view this resource.' ), [ 'status' => $this->authorization_failed_status_code() ] );
		}
		return true;
	}

	/**
	 * Checks if IMPress Agents is enabled.
	 *
	 * @return WP_Error|bool
	 */
	public function agents_enabled() {
		$admin_permissions = $this->admin_check();
		if ( true !== $admin_permissions ) {
			return $admin_permissions;
		}

		if ( ! boolval( get_option( 'idx_broker_agents_enabled', 0 ) ) ) {
			return $this->addon_not_enabled_error( 'IMPress Agents' );
		}
		return true;
	}

	/**
	 * Checks if IMPress Listings is enabled.
	 *
	 * @return bool
	 */
	public function listings_enabled() {
		$admin_permissions = $this->admin_check();
		if ( true !== $admin_permissions ) {
			return $admin_permissions;
		}

		if ( ! boolval( get_option( 'idx_broker_listings_enabled', 0 ) ) ) {
			return $this->addon_not_enabled_error( 'IMPress Listings' );
		}
		return true;
	}

	/**
	 * Provides the appropiate status code for failed auth.
	 *
	 * @return int Status code.
	 */
	public function authorization_failed_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}

	/**
	 * Returns WP_Error for invalid value.
	 *
	 * @param string $name Human readable value name for addon.
	 * @return WP_Error
	 */
	public function addon_not_enabled_error( $name ) {
		return new \WP_Error(
			'addon_disabled',
			"$name is not enabled.",
			[
				'status' => 422,
			]
		);
	}

	/**
	 * Converts the error returned from the IDX_API class to be more suitable for the REST endpoints.
	 *
	 * @param WP_Error $error WP_Error to convert.
	 * @return WP_Error
	 */
	public function convert_idx_api_error( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return $error;
		}
		$code    = $error->get_error_code();
		$message = $error->get_error_message();
		$data    = $error->get_error_data();
		if ( 'idx_api_error' === $code ) {
			return new \WP_Error(
				$code,
				$data['rest_error'],
				[
					'status' => $data['status'],
				]
			);
		}
		return $error;
	}
}

new Apis\Enable_Addons();
new Apis\Settings_General();
new Apis\Agents_Settings();
new Apis\Import_Agents();
new Apis\Import_Listings();
new Apis\Listings_Settings();
new Apis\Listings_Advanced_Settings();
