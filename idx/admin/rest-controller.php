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
		return $this->addon_enabled_check( 'idx_broker_agents_enabled', 'IMPress Agents' );
	}

	/**
	 * Checks if IMPress Listings is enabled.
	 *
	 * @return bool
	 */
	public function listings_enabled() {
		return $this->addon_enabled_check( 'idx_broker_listings_enabled', 'IMPress Listings' );
	}

	/**
	 * Checks if Social Pro is enabled.
	 *
	 * @return bool
	 */
	public function social_pro_enabled() {
		return $this->addon_enabled_check( 'idx_broker_social_pro_enabled', 'Social Pro' );
	}

	/**
	 * Add-on enabled check.
	 *
	 * @param string $wp_option wp_option to check against.
	 * @param string $human_readable Human readable add-on name.
	 * @return bool|WP_Error enabled or not.
	 */
	private function addon_enabled_check( $wp_option, $human_readable ) {
		$admin_permissions = $this->admin_check();
		if ( true !== $admin_permissions ) {
			return $admin_permissions;
		}

		if ( ! boolval( get_option( $wp_option, 0 ) ) ) {
			return $this->addon_not_enabled_error( $human_readable );
		}
		return true;
	}

	/**
	 * Returns WP_Error for invalid value.
	 *
	 * @param string $name Human readable value name for addon.
	 * @return WP_Error
	 */
	private function addon_not_enabled_error( $name ) {
		return new \WP_Error(
			'addon_disabled',
			"$name is not enabled.",
			[
				'status' => 422,
			]
		);
	}

	/**
	 * Provides the appropiate status code for failed auth.
	 *
	 * @return int Status code.
	 */
	private function authorization_failed_status_code() {
		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}

	/**
	 * Converts the error returned from the IDX_API class to be more suitable for the REST endpoints.
	 *
	 * @param WP_Error $error WP_Error to convert.
	 * @return WP_Error
	 */
	protected function convert_idx_api_error( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return $error;
		}
		$code    = $error->get_error_code();
		$message = $error->get_error_message();
		$data    = $error->get_error_data();
		$status  = 'Generic' === $data['status'] ? 500 : $data['status'];
		if ( 'idx_api_error' === $code ) {
			return new \WP_Error(
				$code,
				$data['rest_error'],
				[
					'status' => $status,
				]
			);
		}
		return $error;
	}

	/**
	 * Maps object keys in an array for REST output.
	 *
	 * @param array   $objects Array of objects or associative arrays.
	 * @param array   $name_map Associative array of key pairs to map to.
	 * @param boolean $subset Only keep the keys included in the $name_map.
	 * @return array Updated array of associative arrays.
	 */
	protected function map_keys( $objects, $name_map, $subset = false ) {
		return array_map(
			function( $collection ) use ( &$name_map, &$subset ) {
				$collection = (array) $collection;
				$new_obj    = [];
				foreach ( $name_map as $old => $new ) {
					$new_obj[ $new ] = $collection[ $old ];
					if ( isset( $collection[ $old ] ) ) {
						unset( $collection[ $old ] );
					}
				}
				if ( $subset ) {
					return $new_obj;
				} else {
					return array_merge( $collection, $new_obj );
				}
			},
			$objects
		);
	}
}

new Apis\Agents_Settings();
new Apis\Enable_Addons();
new Apis\Import_Agents();
new Apis\Import_Listings();
new Apis\Listings_IDX_Settings();
new Apis\Listings_Advanced_Settings();
new Apis\Listings_Settings();
new Apis\Omnibar_Settings();
new Apis\Settings_General();
new Apis\Social_Pro_Settings();
