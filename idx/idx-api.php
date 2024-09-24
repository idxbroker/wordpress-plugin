<?php
namespace IDX;

/**
 * Idx_api class.
 */
class Idx_Api {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->api_key         = get_option( 'idx_broker_apikey' );
		$this->dev_partner_key = get_option( 'idx_broker_dev_partner_key' );
	}

	/**
	 * api_key
	 *
	 * @var mixed
	 * @access public
	 */
	public $api_key;

	/**
	 * developer_partner_key
	 * 
	 * @var mixed
	 * @access public
	 */
	public $dev_partner_key;

	/**
	 * apiResponse handles the various replies we get from the IDX Broker API and returns appropriate error messages.
	 *
	 * @param  [array] $response [response header from API call]
	 * @return [array]           [keys: 'code' => response code, 'error' => false (default), or error message if one is found]
	 */
	public function apiResponse( $response ) {
		if ( ! $response || ! is_array( $response ) || ! isset( $response['response'] ) ) {
			return array(
				'code'       => 'Generic',
				'error'      => 'Unable to complete API call.',
				'rest_error' => 'Unable to complete API call.',
			);
		}
		$response_code = $response['response']['code'];
		$err_message   = false;
		$rest_error    = false;
		if ( is_numeric( $response_code ) ) {
			// $err_message is for legacy error messaging. $rest_error is for the return in the REST API interface.
			switch ( $response_code ) {
				case 401:
					$err_message = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>, or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 401">help@idxbroker.com</a>';
					$rest_error  = 'Access key is invalid or has been revoked.';
					break;
				case 403:
					$ip          = gethostbyname( preg_replace( '(^https?://)', '', get_site_url() ) );
					$err_message = 'IP address: ' . $ip . ' was blocked due to violation of TOS. Contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 403">help@idxbroker.com</a> with your IP to determine the reason for the block.';
					$rest_error  = "IP address: $ip was blocked due to violation of IDX Broker's Terms of Service.";
					break;
				case 403.4:
					$err_message = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.';
					$rest_error  = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.';
					break;
				case 400:
				case 405:
				case 409:
					$err_message = 'Invalid request sent to IDX Broker API, please re-install the IMPress for IDX Broker plugin.';
					$rest_error  = 'Invalid request sent to IDX Broker API.';
					break;
				case 406:
					$err_message = 'Access key is missing. To obtain an access key, please visit your <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>.';
					$rest_error  = 'Access key is missing.';
					break;
				case 412:
					$err_message = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>, or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 412">help@idxbroker.com</a>';
					$rest_error  = 'Account has exceeded the hourly access limit for your API key.';
					update_option( 'idx_api_limit_exceeded', time() );
					break;
				case 500:
					$err_message = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 500">help@idxbroker.com</a> if the problem persists.';
					$rest_error  = 'General system error when attempting to communicate with the IDX Broker API.';
					break;
				case 503:
					$err_message = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 503">help@idxbroker.com</a> if the problem persists.';
					$rest_error  = 'IDX Broker API is currently undergoing maintenance.';
					break;
			}
		}
		return array(
			'code'       => $response_code,
			'error'      => $err_message,
			'rest_error' => $rest_error,
		);
	}

	/**
	 * IDX API Request
	 */
	public function idx_api(
		$method,
		$apiversion = IDX_API_DEFAULT_VERSION,
		$level = 'clients',
		$params = array(),
		$expiration = 7200,
		$request_type = 'GET',
		$json_decode_type = false
	) {

		// If no API key is set, return early.
		if ( empty( $this->api_key ) || empty( str_replace( ' ', '', $this->api_key ) ) ) {
			return [];
		}

		$cache_key = 'idx_' . $level . '_' . $method . '_cache';

		if ( $this->get_transient( $cache_key ) !== false ) {
			$data = $this->get_transient( $cache_key );
			return $data;
		}

		$headers = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'accesskey'     => $this->api_key,
			'outputtype'    => 'json',
			'apiversion'    => $apiversion,
			'pluginversion' => \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION,
		);

		if ( ! empty( $this->dev_partner_key ) && is_string( $this->dev_partner_key ) ) {
			$headers['ancillarykey'] = $this->dev_partner_key;
		}

		$params = array_merge(
			array(
				'timeout'   => 120,
				'sslverify' => false,
				'headers'   => $headers,
			),
			$params
		);
		$url    = IDX_API_URL . '/' . $level . '/' . $method;

		if ( 'POST' === $request_type ) {
			$response = wp_safe_remote_post( $url, $params );
		} elseif ( 'PUT' === $request_type ) {
			$params['method'] = $request_type;
			$response         = wp_remote_request( $url, $params );
		} else {
			$response = wp_remote_get( $url, $params );
		}
		$response = (array) $response;

		extract( $this->apiResponse( $response ) ); // get code and error message if any, assigned to vars $code and $error
		if ( isset( $error ) && $error !== false ) {
			if ( $code == 401 ) {
				$this->delete_transient( $cache_key );
			}
			return new \WP_Error(
				'idx_api_error',
				__( 'Error ' ) . $code . __( ': ' ) . $error,
				array(
					'status'     => $code,
					'rest_error' => $error,
				)
			);
		} else {
			$data = (array) json_decode( (string) $response['body'], $json_decode_type );
			if ( 'POST' !== $request_type && 'PUT' !== $request_type ) {
				$this->set_transient( $cache_key, $data, $expiration );
			}
			// API call was successful, delete this option if it exists.
			delete_option( 'idx_api_limit_exceeded' );
			return $data;
		}
	}

	/*
	 * If option does not exist or timestamp is old, return false.
	 * Otherwise return data
	 * We create our own transient functions to avoid bugs with the object cache
	 * for caching plugins.
	 */
	public function get_transient( $name ) {
		if ( is_multisite() && $this->api_key === get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) ) {
			$data = get_blog_option( get_main_site_id(), $name );
		} else {
			$data = get_option( $name );
		}
		if ( empty( $data ) ) {
			return false;
		}
		$data               = unserialize( $data );
		$expiration         = $data['expiration'];
		$api_maybe_exceeded = get_option( 'idx_api_limit_exceeded' );

		// If the data is past expiration, but we've currently exceeded the API limit,
		// let's return the cached data so we don't continue to call the API until
		// after one hour since the first 412 error.
		if ( $api_maybe_exceeded && time() <= $api_maybe_exceeded + ( 60 * 60 ) && $expiration < time() ) {
			return $data['data'];
		} elseif ( $expiration < time() ) {
			return false;
		}
		return $data['data'];
	}

	/**
	 * set_transient function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $data
	 * @param mixed $expiration
	 * @return void
	 */
	public function set_transient( $name, $data, $expiration ) {
		$expiration = time() + $expiration;
		$data       = array(
			'data'       => $data,
			'expiration' => $expiration,
		);
		$data       = serialize( $data );
		if ( is_multisite() && $this->api_key === get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) ) {
			update_blog_option( get_main_site_id(), $name, $data );
		} else {
			update_option( $name, $data, false );
		}
	}

	/**
	 * delete_transient function.
	 *
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function delete_transient( $name ) {
		if ( is_multisite() && $this->api_key === get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) ) {
			delete_blog_option( get_main_site_id(), $name );
		} else {
			delete_option( $name );
		}
	}

	/**
	 * Clean IDX cached data for all idx_ options containing $name, or all of these options in general, the wrapper cache and idx pages if nothing is provided
	 * 
	 * @param String $name, what name should be matched when clearing cached options. Defaults to an empty string.
	 * @return void
	 */
	public function idx_clean_transients($name = '') {
		global $wpdb;

		// If nothing was provided for the name of options to clear, clear everything.
		if ($name === '') {
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $wpdb->options
			 WHERE option_name LIKE %s
			",
					'%idx_%_cache'
				)
			);
	
			$this->clear_wrapper_cache();
	
			// Update IDX Pages Immediately.
			wp_schedule_single_event( time(), 'idx_create_idx_pages' );
			wp_schedule_single_event( time(), 'idx_delete_idx_pages' );
		} else {
			// If $name was set, only clear idx_*_cache options that contain that name		
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $wpdb->options
			 WHERE option_name LIKE %s
			",
					'%idx_' . $name . '%_cache'
				)
			);
	
		}
	}

	/**
	 *
	 * Using our web services function, lets get the system links built in the middleware,
	 * clean and prepare them, and return them in a new array for use.
	 */
	public function idx_api_get_systemlinks() {
		if ( empty( $this->api_key ) ) {
			return array();
		}
		return $this->idx_api( 'systemlinks' );
	}

	/**
	 *
	 * Using our web services function, lets get saved links built in the middleware,
	 * clean and prepare them, and return them in a new array for use.
	 */
	public function idx_api_get_savedlinks() {
		if ( empty( $this->api_key ) ) {
			return array();
		}
		return $this->idx_api( 'savedlinks' );
	}

	/**
	 *
	 * Using our web services function, lets get the widget details built in the middleware,
	 * clean and prepare them, and return them in a new array for use.
	 */
	public function idx_api_get_widgetsrc() {
		if ( empty( $this->api_key ) ) {
			return array();
		}

		$legacyWidgets = $this->idx_api( 'widgetsrc?rf[]=name&rf[]=uid&rf[]=url' );
		if (is_wp_error( $legacyWidgets )) {
			return $legacyWidgets;
		}

		$newWidgets = $this->idx_api( 'widgets?rf[]=name&rf[]=uid&rf[]=url' );
		if (is_wp_error( $newWidgets )) {
			return $newWidgets;
		}

		return array_merge( $legacyWidgets['data'], $newWidgets['data'] ?? []);
	}

	/**
	 * Get api version
	 */
	public function idx_api_get_apiversion() {
		if ( empty( $this->api_key ) ) {
			return IDX_API_DEFAULT_VERSION;
		}

		$data = $this->idx_api( 'apiversion', IDX_API_DEFAULT_VERSION, 'clients', array(), 86400 );
		if ( is_array( $data ) && ! empty( $data ) ) {
			return $data['version'];
		} else {
			return IDX_API_DEFAULT_VERSION;
		}
	}

	/**
	 * system_results_url function.
	 *
	 * @access public
	 * @return void
	 */
	public function system_results_url() {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return false;
		}

		foreach ( $links as $link ) {
			if ( $link->systemresults ) {
				$results_url = $link->url;
			}
		}

		// What if or can they have more than one system results page?
		if ( isset( $results_url ) ) {
			return $results_url;
		}

		return false;
	}

	/**
	 * Returns the url of the link
	 *
	 * @param string $name name of the link to return the url of
	 * @return bool|string
	 */
	public function system_link_url( $name ) {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return false;
		}

		foreach ( $links as $link ) {
			if ( $name == $link->name ) {
				return $link->url;
			}
		}

		return false;
	}

	/**
	 * Returns the url of the first system link found with
	 * a category of "details"
	 *
	 * @return bool|string link url if found else false
	 */
	public function details_url() {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return false;
		}

		foreach ( $links as $link ) {
			if ( 'details' == $link->category ) {
				return $link->url;
			}
		}

		return false;
	}

	/**
	 * Returns an array of system link urls
	 *
	 * @return array
	 */
	public function all_system_link_urls() {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return array();
		}

		$system_link_urls = array();

		foreach ( $links as $link ) {
			$system_link_urls[] = $link->url;
		}

		return $system_link_urls;
	}

	/**
	 * Returns an array of system link names
	 *
	 * @return array
	 */
	public function all_system_link_names() {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return array();
		}

		$system_link_names = array();

		foreach ( $links as $link ) {
			$system_link_names[] = $link->name;
		}

		return $system_link_names;
	}

	/**
	 * all_saved_link_urls function.
	 *
	 * @access public
	 * @return void
	 */
	public function all_saved_link_urls() {

		$links = $this->idx_api_get_savedlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return array();
		}

		$system_link_urls = array();

		foreach ( $links as $link ) {
			$system_link_urls[] = $link->url;
		}

		return $system_link_urls;
	}

	/**
	 * all_saved_link_names function.
	 *
	 * @access public
	 * @return void
	 */
	public function all_saved_link_names() {

		$links = $this->idx_api_get_savedlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return array();
		}

		$system_link_names = array();

		foreach ( $links as $link ) {
			$system_link_names[] = $link->linkTitle;
		}

		return $system_link_names;
	}

	/**
	 * find_idx_page_type function.
	 *
	 * @access public
	 * @param mixed $idx_page
	 * @return void
	 */
	public function find_idx_page_type( $idx_page ) {
		// if it is a saved linke, return saved_link otherwise it is a system page
		$saved_links = $this->idx_api_get_savedlinks();
		foreach ( $saved_links as $saved_link ) {
			$id = $saved_link->id;
			if ( $id === $idx_page ) {
				return 'saved_link';
			}
		}
	}

	/**
	 * set_wrapper function.
	 *
	 * @access public
	 * @param mixed $idx_page
	 * @param mixed $wrapper_url
	 * @return void
	 */
	public function set_wrapper( $idx_page, $wrapper_url ) {
		// if none, quit process
		if ( $idx_page === 'none' ) {
			return;
		} elseif ( $idx_page === 'global' ) {
			// set Global Wrapper:
			$this->idx_api( 'dynamicwrapperurl', IDX_API_DEFAULT_VERSION, 'clients', array( 'body' => array( 'dynamicURL' => $wrapper_url ) ), 10, 'POST' );
		} else {
			// find what IDX page type then set the page wrapper
			$page_type = $this->find_idx_page_type( $idx_page );
			if ( $page_type === 'saved_link' ) {
				$params = array(
					'dynamicURL'  => $wrapper_url,
					'savedLinkID' => $idx_page,
				);
			} else {
				$params = array(
					'dynamicURL' => $wrapper_url,
					'pageID'     => $idx_page,
				);
			}
			$this->idx_api( 'dynamicwrapperurl', IDX_API_DEFAULT_VERSION, 'clients', array( 'body' => $params ), 10, 'POST' );
		}
	}

	// Return value not currently checked
	public function clear_wrapper_cache() {
		$idx_broker_key = $this->api_key;
		$url            = IDX_API_URL . '/clients/wrappercache';
		$args           = array(
			'method'  => 'DELETE',
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'accesskey'    => $idx_broker_key,
				'outputtype'   => 'json',
			),
		);
		$response       = wp_remote_request( $url, $args );
		$response_code  = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 204 ) {
			return false;
		}
		return true;
	}

	/**
	 * saved_link_properties function.
	 *
	 * @access public
	 * @param mixed $saved_link_id
	 * @return void
	 */
	public function saved_link_properties( $saved_link_id ) {

		$saved_link_properties = $this->idx_api( 'savedlinks/' . $saved_link_id . '/results?disclaimers=true', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		return $saved_link_properties;
	}

	/**
	 * Saved_link_properties_count function.
	 * Used to get accurate count on saved link results as the /results method currently is limited to 250 listings returned.
	 *
	 * @access public
	 * @param mixed $saved_link_id - Saved link ID.
	 * @return mixed
	 */
	public function saved_link_properties_count( $saved_link_id ) {
		$saved_link_count = $this->idx_api( 'savedlinks/' . $saved_link_id . '/count', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );
		if ( is_wp_error( $saved_link_count ) || empty( $saved_link_count[0] ) ) {
			return 0;
		}
		return $saved_link_count[0];
	}

	/**
	 * Client_properties function.
	 * Expected $type posibilities: featured, soldpending, supplemental.
	 * 
	 * @access public
	 * @param string $type
	 * @return array
	 */
	public function client_properties( $type ) {
		// Handle supplemental listings.
		// supplemental and supplementalactive both just return active supplemental listings---leaving old supplemental type functionality to avoid making unexpected changes to client sites
		if ( 'supplemental' === $type 
		|| 'supplementalactive' === $type) {
			// Pass 'featured' to get just the active supplemental listings.
			return $this->get_client_supplementals( 'featured' );
		}

		if ( 'supplementalsoldpending' === $type ) {
			return $this->get_client_supplementals( 'soldpending' );
		}

		if ( 'supplementalall' === $type ) {
			return $this->get_client_supplementals( '' );
		}

		$properties        = [];
		$download_complete = false;

		// Make initial API request for listings.
		$listing_data = $this->idx_api( "$type?disclaimers=true", IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( ! is_wp_error( $listing_data ) && isset( $listing_data['data'] ) && is_array( $listing_data['data'] ) ) {
			$properties = $listing_data['data'];
		} else {
			return [];
		}

		// Download remaining listings if available.
		while ( ! $download_complete ) {
			// Check if there is a next URL to request more listings.
			if ( empty( $listing_data['next'] ) ) {
				$download_complete = true;
				continue;
			}
			// Explode $listing_data['next'] on '/clients/', index 1 of the resulting array will have the fragment needed to make the next API request.
			$listing_data = $this->idx_api( explode( '/clients/', $listing_data['next'] )[1] . '&disclaimers=true', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );
			// If $listing_data['data'] is an array, merge it with the existing listings/properties array.
			if ( ! is_wp_error( $listing_data ) && isset( $listing_data['data'] ) && is_array( $listing_data['data'] ) ) {
				$properties = array_merge( $properties, $listing_data['data'] );
			}
		}

		// Add supplemental listings to featured and soldpending types.
		if ( strpos( $type, 'featured' ) !== false || strpos( $type, 'soldpending' ) !== false ) {
			return array_merge( $properties, $this->get_client_supplementals( $type ) );
		}

		// Fallback, return $properties array.
		return $properties;
	}


	/**
	 * Get_Client_Supplementals function.
	 * Helper function to gather supplemental listings.
	 *
	 * @access public
	 * @param string $status - defaults to all listings if not set, 'featured' will pull in active supplemental listings and 'soldpending' will get non-active supplementals.
	 * @return array
	 */
	public function get_client_supplementals( $status = '' ) {
		$listing_data = $this->idx_api( 'supplemental', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		// Return empty array if no listings are returned.
		if ( empty( $listing_data ) || ! is_array( $listing_data ) ) {
			return [];
		}

		// If no $status is provided, return all supplemental listings.
		if ( empty( $status ) ) {
			return $listing_data;
		}

		// If a $status is provided, return filtered results.
		return array_filter(
			$listing_data,
			function ( $listing ) use ( &$status ) {
				// If $status is featured, match for active listings.
				if ( strpos( $status, 'featured' ) !== false && ( 'Active' === $listing['status'] || 'A' === $listing['status'] || 'active' === $listing['status'] || 'ACTIVE' === $listing['status'] ) ) {
					return true;
				}
				// If $status is soldpending, match for non-active listings.
				if ( strpos( $status, 'soldpending' ) !== false && ( 'Active' !== $listing['status'] && 'A' !== $listing['status'] && 'active' !== $listing['status'] && 'ACTIVE' !== $listing['status'] ) ) {
					return true;
				}
			}
		);
	}

	/**
	 * Returns an array of city objects for the agents mls area
	 *
	 * @return array $default_cities
	 */
	public function default_cities() {

		$default_cities = $this->idx_api( 'cities/combinedActiveMLS', IDX_API_DEFAULT_VERSION, 'clients' );

		return $default_cities;
	}

	/**
	 * Returns an array of city list ids
	 *
	 * @return array $list_ids
	 */
	public function city_list_ids() {

		$list_ids = $this->idx_api( 'cities', IDX_API_DEFAULT_VERSION, 'clients' );
		return $list_ids;
	}

	/**
	 * Returns a list of cities
	 *
	 * @return array $city_list
	 */
	public function city_list( $list_id ) {

		$city_list = $this->idx_api( 'cities/' . $list_id, IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list;
	}

	/**
	 * Returns a property count integer for an id (city, county, or zip)
	 *
	 * @param  string $idx_id The idxID (mlsID)
	 * @param  string $id The identifier. City id, county id or zip code
	 * @param  string $type The count type (city, county or zip)
	 *
	 * @return array $city_list
	 */
	public function property_count_by_id( $idx_id, $id, $type = 'city' ) {

		$city_count = $this->idx_api( 'propertycount/' . $idx_id . '?countType=' . $type . '&countSpecifier=' . $id, IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 12 );

		return $city_count;
	}

	/**
	 * Returns the IDs and names for each of a client's city lists including MLS city lists
	 *
	 * @return array
	 */
	public function city_list_names() {

		$city_list_names = $this->idx_api( 'citieslistname', IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list_names;
	}

	/**
	 * Returns the IDs and names for each of a client's county lists including MLS county lists
	 *
	 * @return array
	 */
	public function county_list_names() {

		$county_list_names = $this->idx_api( 'countieslistname', IDX_API_DEFAULT_VERSION, 'clients' );

		return $county_list_names;
	}

	/**
	 * Returns the IDs and names for each of a client's postalcodes lists including MLS postalcodes lists
	 *
	 * @return array
	 */
	public function postalcode_list_names() {

		$postalcodes_list_names = $this->idx_api( 'postalcodeslistname', IDX_API_DEFAULT_VERSION, 'clients' );

		return $postalcodes_list_names;
	}

	/**
	 * Returns the subdomain url WITH trailing slash
	 *
	 * @return string $url
	 */
	public function subdomain_url() {

		$url = $this->system_link_url( 'Sitemap' );
		$url = explode( 'sitemap', $url );

		return $url[0];
	}

	/**
	 * Returns the IDX IDs and names for all of the paper work approved MLSs
	 * on the client's account
	 */
	public function approved_mls() {

		$approved_mls = $this->idx_api( 'approvedmls', IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 24 );

		return $approved_mls;
	}

	/**
	 * Returns search field names for an MLS
	 */
	public function searchfields( $idxID ) {
		$approved_mls = $this->idx_api( "searchfields/$idxID", IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}

	public function searchfieldvalues( $idxID, $fieldName, $mlsPtID ) {
		$approved_mls = $this->idx_api( "searchfieldvalues/$idxID?mlsPtID=$mlsPtID&name=$fieldName", IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}

	/**
	 * Compares the price fields of two arrays
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	public function price_cmp( $a, $b ) {

		$a = $this->clean_price( $a['listingPrice'] );
		$b = $this->clean_price( $b['listingPrice'] );

		return $a <=> $b;
	}

	/**
	 * Removes the "$" and "," from the price field
	 *
	 * @param string $price
	 * @return mixed $price the cleaned price
	 */
	public function clean_price( $price ) {

		$patterns = array(
			'/\$/',
			'/,/',
		);

		$price = preg_replace( $patterns, '', $price );

		return $price;
	}

	/**
	 * Platinum_account_type function.
	 *
	 * @access public
	 * @return bool
	 */
	public function platinum_account_type() {
		$account_type = $this->idx_api( 'accounttype', IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 60 * 24 );
		if ( ! empty( $account_type ) && 'object' !== gettype( $account_type ) && ( stripos( $account_type[0], 'plat' ) || stripos( $account_type[0], 'home' ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get_leads function.
	 *
	 * @access public
	 * @param mixed  $timeframe (default: null).
	 * @param string $start_date (default: '').
	 * @return array
	 */
	public function get_leads( $timeframe = null, $start_date = '' ) {
		// API return limit and offset.
		$limit  = 500;
		$offset = 0;

		// Raw lead data and parsed leads.
		$lead_data = [];
		$leads     = [];

		$api_method = '';

		if ( ! empty( $start_date ) ) {
			$start_date = "&startDatetime=$start_date";
		}

		if ( ! empty( $timeframe ) ) {
			$api_method = "lead?interval=$timeframe$start_date&offset=";
		} else {
			$api_method = 'lead?offset=';
		}

		$lead_data = $this->idx_api( $api_method . $offset, IDX_API_DEFAULT_VERSION, 'leads' );

		if ( array_key_exists( 'data', $lead_data ) && array_key_exists( 'total', $lead_data ) ) {
			$leads = $lead_data['data'];

			while ( ( $offset + $limit ) < $lead_data['total'] ) {
				$offset    = $offset + $limit;
				$lead_data = $this->idx_api( $api_method . $offset, IDX_API_DEFAULT_VERSION, 'leads' );
				// If no leads are returned, stop requesting more.
				if ( empty( $lead_data['data'] ) ) {
					break;
				}
				$leads = array_merge( $leads, $lead_data['data'] );
			}
		}

		return $leads;
	}

	/**
	 * Get_leads_total function.
	 *
	 * @access public
	 * @return int
	 */
	public function get_leads_total() {

		$lead_count = 0;

		$lead_data = $this->idx_api( 'lead?offset=0', IDX_API_DEFAULT_VERSION, 'leads' );

		if ( array_key_exists( 'total', $lead_data ) && 'integer' === gettype( $lead_data['total'] ) ) {
			$lead_count = $lead_data['total'];
		}

		return $lead_count;
	}

	/**
	 * Get_recent_leads function.
	 *
	 * @access public
	 * @param string $date_type (default: 'subscribeDate').
	 * @param int    $lead_count (default: 5).
	 * @return array
	 */
	public function get_recent_leads( $date_type = 'subscribeDate', $lead_count = 5 ) {
		// Get first page of leads.
		$lead_data = $this->idx_api( 'lead?offset=0&dateType=' . $date_type, IDX_API_DEFAULT_VERSION, 'leads' );

		if ( is_wp_error( $lead_data ) || empty( $lead_data['data'] ) ) {
			return [];
		}

		// If 'first' and 'last' are the same URL, return the results. If not, request leads with the 'last' URL and return those.
		if ( $lead_data['first'] === $lead_data['last'] ) {
			return array_reverse( array_slice( $lead_data['data'], -$lead_count ) );
		}

		// Get last page of leads.
		$api_method = 'lead' . substr( $lead_data['last'], strpos( $lead_data['last'], '?' ) ) . '&dateType=' . $date_type;
		$lead_data  = $this->idx_api( $api_method, IDX_API_DEFAULT_VERSION, 'leads' );

		if ( is_wp_error( $lead_data ) || empty( $lead_data['data'] ) ) {
			return [];
		}

		// If returned listings from last page is enough to cover the $lead_count, return results.
		if ( count( $lead_data['data'] ) >= $lead_count ) {
			return array_reverse( array_slice( $lead_data['data'], -$lead_count ) );
		}

		// If there are not enough leads on the last page to match the $lead_count, store the current leads and call the previous-to-last page to get the rest.
		$returned_leads = array_reverse( array_slice( $lead_data['data'], $lead_count ) );

		// Get 2nd-to-last page of leads if needed.
		$api_method = 'lead' . substr( $lead_data['previous'], strpos( $lead_data['previous'], '?' ) ) . '&dateType=' . $date_type;
		$lead_data  = $this->idx_api( $api_method, IDX_API_DEFAULT_VERSION, 'leads' );

		if ( ! is_wp_error( $lead_data ) && ! empty( $lead_data['data'] ) ) {
			return array_merge( $returned_leads, array_reverse( array_slice( $lead_data['data'], -( $lead_count - count( $returned_leads ) ) ) ) );
		}

		return [];
	}

	/**
	 * Get_featured_listings function.
	 *
	 * @access public
	 * @param string $listing_type (default: 'featured').
	 * @param string $timeframe (default: null).
	 * @return array
	 */
	public function get_featured_listings( $listing_type = 'featured', $timeframe = null ) {
		// API return limit and offset.
		$limit  = 50;
		$offset = 0;
		// Returned data from IDXB featured listings call.
		$listing_data = [];
		// Property listings taken from $listing_data['data'].
		$properties = [];
		// API method string.
		$api_method = '';
		if ( ! empty( $timeframe ) ) {
			$api_method = "$listing_type?interval=$timeframe&offset=";
		} else {
			$api_method = "$listing_type?offset=";
		}

		// Initial request.
		$listing_data = $this->idx_api( $api_method . $offset, IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 2, 'GET', true );

		if ( ! is_wp_error( $listing_data ) && ! empty( $listing_data['data'] ) ) {
			// Assign returned listings to $properties.
			$properties = $listing_data['data'];

			// Get rest of listings if there are more than 50.
			while ( ( $offset + $limit ) < $listing_data['total'] ) {
				$offset       = $offset + $limit;
				$listing_data = $this->idx_api( $api_method . $offset, IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 2, 'GET', true );
				$properties   = array_merge( $properties, $listing_data['data'] );
			}
		}

		return $properties;
	}

	/**
	 * Returns agents wrapped in option tags
	 *
	 * @param  int $agent_id Instance agentID if exists.
	 * @return str           HTML options tags of agents ids and names
	 */
	public function get_agents_select_list( $agent_id ) {
		$agents_array = $this->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( is_wp_error( $agents_array ) || empty( $agents_array['agent'] ) ) {
			return;
		}

		if ( ! empty( $agent_id ) ) {
			echo '<option value="" ' . selected( $agent_id, '', '' ) . '>---</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				echo '<option value="' . esc_attr( $agent['agentID'] ) . '" ' . selected( $agent_id, $agent['agentID'], 0 ) . '>' . esc_html( $agent['agentDisplayName'] ) . '</option>';
			}
		} else {
			echo '<option value="">---</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				echo '<option value="' . esc_attr( $agent['agentID'] ) . '">' . esc_html( $agent['agentDisplayName'] ) . '</option>';
			}
		}

	}

	/**
	 * Determine if agent has properties.
	 *
	 * @param  int $agent_id The IDX assigned agent ID.
	 * @return bool           True if yes, false if no, null if no agentID provided.
	 */
	public function agent_has_properties( $agent_id ) {
		if ( ! $agent_id ) {
			return null;
		}

		$properties = $this->client_properties( 'featured' );

		foreach ( $properties as $prop ) {
			if ( isset( $prop['userAgentID'] ) && (int) $prop['userAgentID'] === (int) $agent_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Some MLS' require that no listing data is displayed prior to IDX approval. Check the account's MLS' for this restriction rule
	 * and determine if this needs to be applied.
	 */
	public function idx_api_get_coming_soon_widget_restriction() {
		$response = $this->idx_api('widgetDataRestriction');
		update_option( 'idx_broker_widget_data_restriction', $response['restrictWidgetDataBeforeApproval'] );
	}
}
