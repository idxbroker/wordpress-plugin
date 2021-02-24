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
	 * @return bool
	 */
	public function admin_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to view this resource.' ), array( 'status' => $this->authorization_failed_status_code() ) );
		}
		return true;
	}

	/**
	 * Checks if IMPress Agents is enabled.
	 *
	 * @return bool
	 */
	public function agents_enabled() {
		// TODO: Return wp_option for agents enabled state.
		return true;
	}

	/**
	 * Checks if IMPress Listings is enabled.
	 *
	 * @return bool
	 */
	public function listings_enabled() {
		// TODO: Return wp_option for listings enabled state.
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
}

new Apis\Settings_General();
