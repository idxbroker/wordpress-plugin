<?php
namespace IDX;

/**
 * Idx_Api class.
 */
class Idx_Api {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->api_key = get_option( 'idx_broker_apikey' );
	}

	/**
	 * API Key.
	 *
	 * @var mixed
	 * @access public
	 */
	public $api_key;


	/**
	 * API Response.
	 *
	 * @access public
	 * @param mixed $response Response.
	 */
	public function api_response( $response ) {
		if ( ! $response || ! is_array( $response ) || ! isset( $response['response'] ) ) {
			return array(
				'code'  => 'Generic',
				'error' => 'Unable to complete API call.',
			);
		}
		$response_code = $response['response']['code'];
		$err_message   = false;
		if ( is_numeric( $response_code ) ) {
			switch ( $response_code ) {
				case 401:
					$err_message = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>, or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 401">help@idxbroker.com</a>';
					break;
				case 403:
					$ip          = gethostbyname( preg_replace( '(^https?://)', '', get_site_url() ) );
					$err_message = 'IP address: ' . $ip . ' was blocked due to violation of TOS. Contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 403">help@idxbroker.com</a> with your IP to determine the reason for the block.';
					break;
				case 403.4:
					$err_message = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.';
					break;
				case 405:
				case 409:
					$err_message = 'Invalid request sent to IDX Broker API, please re-install the IMPress for IDX Broker plugin.';
					break;
				case 406:
					$err_message = 'Access key is missing. To obtain an access key, please visit your <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>.';
					break;
				case 412:
					$err_message = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the <a href="https://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control">IDX Broker Dashboard</a>, or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 412">help@idxbroker.com</a>';
					update_option( 'idx_api_limit_exceeded', time() );
					break;
				case 500:
					$err_message = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 500">help@idxbroker.com</a> if the problem persists.';
					break;
				case 503:
					$err_message = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or contact <a href="mailto:help@idxbroker.com?subject=IMPress for IDX Broker - Error 503">help@idxbroker.com</a> if the problem persists.';
					break;
			}
		}
		return array(
			'code'  => $response_code,
			'error' => $err_message,
		);
	}

	/**
	 * IDX API Request.
	 */
	public function idx_api(

		/**
		 * Method.
		 *
		 * @var mixed
		 * @access public
		 */
		$method,

		/**
		 * API Version.
		 *
		 * @var mixed
		 * @access public
		 */
		$api_version = Initiate_Plugin::IDX_API_DEFAULT_VERSION,

		/**
		 * Level.
		 *
		 * @var mixed
		 * @access public
		 */
		$level = 'clients',

		/**
		 * Params.
		 *
		 * @var mixed
		 * @access public
		 */
		$params = array(),

		/**
		 * expiration.
		 *
		 * @var mixed
		 * @access public
		 */
		$expiration = 7200,

		/**
		 * Request Type.
		 *
		 * @var mixed
		 * @access public
		 */
		$request_type = 'GET',

		/**
		 * JSON Decode Type.
		 *
		 * @var mixed
		 * @access public
		 */
		$json_decode_type = false
	) {

		/**
		 * Cache Key.
		 *
		 * (default value: 'idx_' . $level . '_' . $method . '_cache')
		 *
		 * @var string
		 * @access public
		 */
		$cache_key = 'idx_' . $level . '_' . $method . '_cache';

		if ( $this->get_transient( $cache_key ) !== false ) {
			$data = $this->get_transient( $cache_key );
			return $data;
		}

		/**
		 * Headers.
		 *
		 * @var mixed
		 * @access public
		 */
		$headers = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'accesskey'     => $this->api_key,
			'outputtype'    => 'json',
			'apiversion'    => $api_version,
			'pluginversion' => \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION,
		);

		/**
		 * Params.
		 *
		 * @var mixed
		 * @access public
		 */
		$params = array_merge(
			array(
				'timeout'   => 120,
				'sslverify' => false,
				'headers'   => $headers,
			),
			$params
		);

		/**
		 * URL.
		 *
		 * (default value: Initiate_Plugin::IDX_API_URL . '/' . $level . '/' . $method)
		 *
		 * @var string
		 * @access public
		 */
		$url = Initiate_Plugin::IDX_API_URL . '/' . $level . '/' . $method;

		if ( 'POST' === $request_type ) {
			$response = wp_safe_remote_post( $url, $params );
		} else {
			$response = wp_remote_get( $url, $params );
		}

		/**
		 * Response.
		 *
		 * (default value: (array) $response)
		 *
		 * @var mixed
		 * @access public
		 */
		$response = (array) $response;

		extract( $this->api_response( $response ) ); // Get code and error message if any, assigned to vars $code and $error.
		if ( isset( $error ) && false !== $error ) {
			if ( 401 === $code ) {
				$this->delete_transient( $cache_key );
			}
			return new \WP_Error( 'idx_api_error', __( "Error {$code}: $error" ) );
		} else {
			$data = (array) json_decode( (string) $response['body'], $json_decode_type );
			if ( 'POST' !== $request_type ) {
				$this->set_transient( $cache_key, $data, $expiration );
			}
			// API call was successful, delete this option if it exists.
			delete_option( 'idx_api_limit_exceeded' );
			return $data;
		}
	}

	/**
	 * Get Transient.
	 *
	 * If option does not exist or timestamp is old, return false.
	 * Otherwise return data
	 * We create our own transient functions to avoid bugs with the object cache
	 * for caching plugins.
	 *
	 * @access public
	 * @param mixed $name Name.
	 */
	public function get_transient( $name ) {
		if ( is_multisite() && get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) === $this->api_key ) {
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
	 * Set Transient.
	 *
	 * @access public
	 * @param mixed $name Name.
	 * @param mixed $data Data.
	 * @param mixed $expiration Expiration.
	 * @return void
	 */
	public function set_transient( $name, $data, $expiration ) {
		$expiration = time() + $expiration;
		$data       = array(
			'data'       => $data,
			'expiration' => $expiration,
		);
		$data       = serialize( $data );
		if ( is_multisite() && get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) === $this->api_key ) {
			update_blog_option( get_main_site_id(), $name, $data );
		} else {
			update_option( $name, $data, false );
		}
	}

	/**
	 * Delete Transient.
	 *
	 * @access public
	 * @param mixed $name Name.
	 * @return void
	 */
	public function delete_transient( $name ) {
		if ( is_multisite() && get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) === $this->api_key ) {
			delete_blog_option( get_main_site_id(), $name );
		} else {
			delete_option( $name );
		}
	}

	/**
	 * Clean IDX cached data.
	 */
	public function idx_clean_transients() {
		global $wpdb;
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
		return $this->idx_api( 'widgetsrc' );
	}

	/**
	 * Get api version
	 */
	public function idx_api_get_apiversion() {
		if ( empty( $this->api_key ) ) {
			return Initiate_Plugin::IDX_API_DEFAULT_VERSION;
		}

		$data = $this->idx_api( 'apiversion', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 86400 );
		if ( is_array( $data ) && ! empty( $data ) ) {
			return $data['version'];
		} else {
			return Initiate_Plugin::IDX_API_DEFAULT_VERSION;
		}
	}

	/**
	 * System Results URL.
	 *
	 * @access public
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
	 * Returns the url of the link.
	 *
	 * @access public
	 * @param mixed $name Name.
	 */
	public function system_link_url( $name ) {

		$links = $this->idx_api_get_systemlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return false;
		}

		foreach ( $links as $link ) {
			if ( $name === $link->name ) {
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
			if ( 'details' === $link->category ) {
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
	 * All Saved Link URLS.
	 *
	 * @access public
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
	 * All Saved Link Names.
	 *
	 * @access public
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
	 * Find IDX Page Type.
	 *
	 * @access public
	 * @param mixed $idx_page IDX Page.
	 */
	public function find_idx_page_type( $idx_page ) {
		// if it is a saved linke, return saved_link otherwise it is a system page.
		$saved_links = $this->idx_api_get_savedlinks();
		foreach ( $saved_links as $saved_link ) {
			$id = $saved_link->id;
			if ( $id === $idx_page ) {
				return 'saved_link';
			}
		}
	}

	/**
	 * Set Wrapper.
	 *
	 * @access public
	 * @param mixed $idx_page IDX Page.
	 * @param mixed $wrapper_url Wrapper URL.
	 */
	public function set_wrapper( $idx_page, $wrapper_url ) {
		// if none, quit process.
		if ( 'none' === $idx_page ) {
			return;
		} elseif ( 'global' === $idx_page ) {
			// Set Global Wrapper.
			$this->idx_api( 'dynamicwrapperurl', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array( 'body' => array( 'dynamicURL' => $wrapper_url ) ), 10, 'POST' );
		} else {
			// Find what IDX page type then set the page wrapper.
			$page_type = $this->find_idx_page_type( $idx_page );
			if ( 'saved_link' === $page_type ) {
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
			$this->idx_api( 'dynamicwrapperurl', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array( 'body' => $params ), 10, 'POST' );
		}
	}

	/**
	 * Clear Wrapper Cache.
	 *
	 * @access public
	 */
	public function clear_wrapper_cache() {
		$idx_broker_key = $this->api_key;
		$url            = Initiate_Plugin::IDX_API_URL . '/clients/wrappercache';
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
		if ( 204 !== $response_code ) {
			return false;
		}
		return true;
	}

	/**
	 * Saved Link Properties.
	 *
	 * @access public
	 * @param mixed $saved_link_id Saved Link ID.
	 */
	public function saved_link_properties( $saved_link_id ) {

		$saved_link_properties = $this->idx_api( 'savedlinks/' . $saved_link_id . '/results?disclaimers=true', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		return $saved_link_properties;
	}

	/**
	 * Client Properties.
	 *
	 * @access public
	 * @param mixed $type Type.
	 */
	public function client_properties( $type ) {
		$properties = $this->idx_api( $type . '?disclaimers=true', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		return $properties;
	}

	/**
	 * Returns an array of city objects for the agents mls area.
	 *
	 * @return array $default_cities Default Cities.
	 */
	public function default_cities() {

		$default_cities = $this->idx_api( 'cities/combinedActiveMLS', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $default_cities;
	}


	/**
	 * Returns an array of city list ids.
	 *
	 * @access public
	 */
	public function city_list_ids() {

		$list_ids = $this->idx_api( 'cities', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );
		return $list_ids;
	}

	/**
	 * Get City List.
	 *
	 * @access public
	 * @param mixed $list_id List ID.
	 */
	public function city_list( $list_id ) {

		$city_list = $this->idx_api( 'cities/' . $list_id, Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list;
	}

	/**
	 * Returns a property count integer for an id (city, county, or zip)
	 *
	 * @param  string $type The count type (city, county or zip).
	 * @param  string $idx_id The idxID (mlsID).
	 * @param  string $id The identifier. City id, county id or zip code.
	 * @return array $city_list City List.
	 */
	public function property_count_by_id( $type = 'city', $idx_id, $id ) {

		$city_count = $this->idx_api( 'propertycount/' . $idx_id . '?countType=' . $type . '&countSpecifier=' . $id, Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 12 );

		return $city_count;
	}

	/**
	 * Returns the IDs and names for each of a client's city lists including MLS city lists
	 *
	 * @return array
	 */
	public function city_list_names() {

		$city_list_names = $this->idx_api( 'citieslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list_names;
	}

	/**
	 * Returns the IDs and names for each of a client's county lists including MLS county lists
	 *
	 * @return array
	 */
	public function county_list_names() {

		$county_list_names = $this->idx_api( 'countieslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $county_list_names;
	}

	/**
	 * Returns the IDs and names for each of a client's postalcodes lists including MLS postalcodes lists
	 *
	 * @return array
	 */
	public function postalcode_list_names() {

		$postalcodes_list_names = $this->idx_api( 'postalcodeslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $postalcodes_list_names;
	}

	/**
	 * Returns the subdomain url WITH trailing slash.
	 *
	 * @return string $url URL.
	 */
	public function subdomain_url() {

		$url = $this->system_link_url( 'Sitemap' );
		$url = explode( 'sitemap', $url );

		return $url[0];
	}

	/**
	 * Returns the IDX IDs and names for all of the paper work approved MLSs on the client's account.
	 */
	public function approved_mls() {

		$approved_mls = $this->idx_api( 'approvedmls', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 24 );

		return $approved_mls;
	}


	/**
	 * Returns search field names for an MLS.
	 *
	 * @access public
	 * @param mixed $idx_id IDX ID.
	 */
	public function searchfields( $idx_id ) {
		$approved_mls = $this->idx_api( "searchfields/$idx_id", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}

	/**
	 * Search Field Values.
	 *
	 * @access public
	 * @param mixed $idx_id IDX ID.
	 * @param mixed $field_name Field Name.
	 * @param mixed $mls_pt_id MLS Property Type ID.
	 */
	public function searchfieldvalues( $idx_id, $field_name, $mls_pt_id ) {
		$approved_mls = $this->idx_api( "searchfieldvalues/$idx_id?mlsPtID=$mls_pt_id&name=$field_name", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}

	/**
	 * Compares the price fields of two arrays.
	 *
	 * @param array $a A.
	 * @param array $b B.
	 * @return int
	 */
	public function price_cmp( $a, $b ) {

		$a = $this->clean_price( $a['listingPrice'] );
		$b = $this->clean_price( $b['listingPrice'] );

		if ( $a === $b ) {
			return 0;
		}

		return ( $a < $b ) ? -1 : 1;
	}

	/**
	 * Removes the "$" and "," from the price field.
	 *
	 * @param string $price Price.
	 * @return mixed $price The cleaned price.
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
	 * Platinum Account Type.
	 *
	 * @access public
	 */
	public function platinum_account_type() {
		$account_type = $this->idx_api( 'accounttype', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 60 * 24 );
		if ( gettype( 'object' !== $account_type ) && ( 'IDX Broker Platinum' === $account_type[0] || 'IDX Broker HOME' === $account_type[0] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get Leads.
	 *
	 * @access public
	 * @param mixed  $timeframe (default: null) Timeframe.
	 * @param string $start_date (default: '') Start Date.
	 */
	public function get_leads( $timeframe = null, $start_date = '' ) {
		if ( ! empty( $start_date ) ) {
			$start_date = "&startDatetime=$start_date";
		}
		if ( ! empty( $timeframe ) ) {
			$leads = $this->idx_api( "lead?interval=$timeframe$start_date", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'leads' );
		} else {
			$leads = $this->idx_api( 'lead', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'leads' );
		}
		return $leads['data'];
	}

	/**
	 * Get Featured Listings.
	 *
	 * @access public
	 * @param string $listing_type (default: 'featured') Listing Type.
	 * @param mixed  $timeframe (default: null) Timeframe.
	 */
	public function get_featured_listings( $listing_type = 'featured', $timeframe = null ) {
		// Force type to array.
		if ( ! empty( $timeframe ) ) {
			$listings = $this->idx_api( "$listing_type?interval=$timeframe", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 2, 'GET', true );
		} else {
			$listings = $this->idx_api( $listing_type, Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 2, 'GET', true );
		}

		return $listings;
	}

	/**
	 * Returns agents wrapped in option tags.
	 *
	 * @param  int $agent_id Instance agentID if exists.
	 * @return str           HTML options tags of agents ids and names.
	 */
	public function get_agents_select_list( $agent_id ) {
		$agents_array = $this->idx_api( 'agents', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( ! is_array( $agents_array ) || ! isset( $agents_array['agent'] ) ) {
			return;
		}

		if ( null !== $agent_id ) {
			$agents_list = '<option value="" ' . selected( $agent_id, '', '' ) . '>---</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				$agents_list .= '<option value="' . $agent['agentID'] . '" ' . selected( $agent_id, $agent['agentID'], 0 ) . '>' . $agent['agentDisplayName'] . '</option>';
			}
		} else {
			$agents_list = '<option value="">---</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				$agents_list .= '<option value="' . $agent['agentID'] . '">' . $agent['agentDisplayName'] . '</option>';
			}
		}

		return $agents_list;
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
}
