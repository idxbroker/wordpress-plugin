<?php
namespace IDX;

/**
 * Begin creating Idx_Api class.
 *
 * @since 2.5.10
 */
class Idx_Api {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function __construct() {
		$this->api_key = get_option( 'idx_broker_apikey' );
	}

	/**
	 * Begin creat api_key function
	 *
	 * @var mixed
	 * @access public
	 * @since 2.5.10
	 */
	public $api_key;
	/**
	 * Begin creating apiResponse handles the various replies we get from the IDX Broker API and returns appropriate error messages.
	 *
	 * @param  array $response [response header from API call].
	 * @return array           [keys: 'code' => response code, 'error' => false (default), or error message if one is found].
	 * @since 2.5.10
	 */
	public function apiResponse( $response ) {
		// apiResponse is not in valid snake_case format.
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
	 * Begin creating idx_api function
	 * IDX API Request.
	 *
	 * @since 2.5.10
	 * @param mixed   $method specified the request method.
	 * @param	mixed   $apiversion specifies the version of the API to use.
	 * @param	mixed   $level specifies the method level to use.
	 * @param	array   $params contains an array of arguments.
	 * @param	integer $expiration specifies the expiration in seconds.
	 * @param	mixed   $request_type specifies the request type.
	 * @param	boolean $json_decode_type specifies the method to use while decoding the JSON string.
	 * @return array $data
	 */
	public function idx_api(
		$method,
		$apiversion = Initiate_Plugin::IDX_API_DEFAULT_VERSION,
		$level = 'clients',
		$params = array(),
		$expiration = 7200,
		$request_type = 'GET',
		$json_decode_type = false
	) {
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

		$params = array_merge(
			array(
				'timeout'   => 120,
				'sslverify' => false,
				'headers'   => $headers,
			),
			$params
		);
		$url    = Initiate_Plugin::IDX_API_URL . '/' . $level . '/' . $method;

		if ( 'POST' === $request_type ) {
			$response = wp_safe_remote_post( $url, $params );
		} else {
			$response = wp_remote_get( $url, $params );
		}
		$response = (array) $response;
		// extract() usage is highly discouraged, due to the complexity and unintended issues it might cause.
		extract( $this->apiResponse( $response ) ); // get code and error message if any, assigned to vars $code and $error.
		if ( isset( $error ) && false !== $error ) {
			// Inline control structures are not allowed.
			if ( 401 === $code ) {
				$this->delete_transient( $cache_key );
			}
			$error = "Error { $code }: $error";
			return new \WP_Error( 'idx_api_error', __( "Error {$code}: $error" ) ); // The $text arg must not contain interpolated variables. Found $code and $error.
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
	 * If option does not exist or timestamp is old, return false.
	 * Otherwise return data
	 * We create our own transient functions to avoid bugs with the object cache
	 * for caching plugins.
	 *
	 * @since 2.5.10
	 * @return Boolean value.
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
		$data               = unserialize( $data ); // Issue #115 unserialize() found. Serialized data has known vulnerability problems with Object Injection. JSON is generally a better approach for serializing data. See https://www.owasp.org/index.php/PHP_Object_Injection.
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
	 * Begin creating set_transient function.
	 *
	 * @access public
	 * @param mixed $name is the name for the transient.
	 * @param mixed $data is the data fro the transient.
	 * @param mixed $expiration is when the transient should expire (in seconds).
	 * @since 2.5.10
	 */
	public function set_transient( $name, $data, $expiration ) {
		$expiration = time() + $expiration;
		$data       = array(
			'data'       => $data,
			'expiration' => $expiration,
		);
		$data       = serialize( $data ); // Issue #115 unserialize() found. Serialized data has known vulnerability problems with Object Injection. JSON is generally a better approach for serializing data. See https://www.owasp.org/index.php/PHP_Object_Injection.
		if ( is_multisite() && get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) === $this->api_key ) {
			update_blog_option( get_main_site_id(), $name, $data );
		} else {
			update_option( $name, $data, false );
		}
	}

	/**
	 * Begin creating delete_transient function.
	 *
	 * @access public
	 * @param mixed $name is the name of the transient.
	 * @since 2.5.10
	 */
	public function delete_transient( $name ) {
		if ( is_multisite() && get_blog_option( get_main_site_id(), 'idx_broker_apikey' ) === $this->api_key ) {
			delete_blog_option( get_main_site_id(), $name );
		} else {
			delete_option( $name );
		}
	}

	/**
	 * Clean IDX cached data
	 *
	 * @since 2.5.10
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
	 *
	 * @since 2.5.10
	 * @return array of system links.
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
	 *
	 * @since 2.5.10
	 * @return array of saved links.
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
	 *
	 * @since 2.5.10
	 * @return array of widgets.
	 */
	public function idx_api_get_widgetsrc() {
		if ( empty( $this->api_key ) ) {
			return array();
		}
		return $this->idx_api( 'widgetsrc' );
	}

	/**
	 * Begin creating idx_api_get_apiversion function
	 * Get api version
	 *
	 * @since 2.5.10
	 * @return mixed latest version number of IDX API
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
	 * Begin creating system_results_url function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return Boolean Value.
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
	 * Begin creating system_link_url function
	 * Returns the url of the link
	 *
	 * @param string $name name of the link to return the url of.
	 * @since 2.5.10
	 * @return bool|string
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
	 * @since 2.5.10
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
	 * @since 2.5.10
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
	 * @since 2.5.10
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
	 * Begin creating all_saved_link_urls function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return array of system link urls.
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
	 * Begin creating all_saved_link_names function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return array of save link urls.
	 */
	public function all_saved_link_names() {

		$links = $this->idx_api_get_savedlinks();

		if ( empty( $links ) || ! empty( $links->errors ) ) {
			return array();
		}

		$system_link_names = array();

		foreach ( $links as $link ) {
			$system_link_names[] = $link->linkTitle; // linkTitle is not in valid snake_case format.
		}

		return $system_link_names;
	}

	/**
	 * Begin creating find_idx_page_type function.
	 *
	 * @access public
	 * @param mixed $idx_page contains the type of IDX Page.
	 * @since 2.5.10
	 * @return type of page.
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
	 * Begin creating set_wrapper function.
	 *
	 * @access public
	 * @param mixed $idx_page contains type of IDX Page.
	 * @param mixed $wrapper_url contains the url of the wrapper to apply.
	 * @since 2.5.10
	 * @return null
	 */
	public function set_wrapper( $idx_page, $wrapper_url ) {
		// if none, quit process.
		if ( 'none' === $idx_page ) {
			return;
		} elseif ( 'global' === $idx_page ) {
			// set Global Wrapper.
			$this->idx_api( 'dynamicwrapperurl', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array( 'body' => array( 'dynamicURL' => $wrapper_url ) ), 10, 'POST' );
		} else {
			// find what IDX page type then set the page wrapper.
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

	// Return value not currently checked.
	/**
	 * Begin creating clear_wrapper_cache function
	 *
	 * @since 2.5.10
	 * @return Boolean value.
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
	 * Begin creating saved_link_properties function.
	 *
	 * @access public
	 * @param mixed $saved_link_id contains the id number of the Saved Link.
	 * @since 2.5.10
	 * @return mixed properties of the saved link.
	 */
	public function saved_link_properties( $saved_link_id ) {

		$saved_link_properties = $this->idx_api( 'savedlinks/' . $saved_link_id . '/results?disclaimers=true', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		return $saved_link_properties;
	}

	/**
	 * Begin creating client_properties function.
	 *
	 * @access public
	 * @param mixed $type contains the property type.
	 * @since 2.5.10
	 * @return array of properties.
	 */
	public function client_properties( $type ) {
		$properties = $this->idx_api( $type . '?disclaimers=true', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		return $properties;
	}

	/**
	 * Returns an array of city objects for the agents mls area
	 *
	 * @since 2.5.10
	 * @return array $default_cities
	 */
	public function default_cities() {

		$default_cities = $this->idx_api( 'cities/combinedActiveMLS', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $default_cities;
	}

	/**
	 * Begin creating city_list_ids function
	 * Returns an array of city list ids
	 *
	 * @since 2.5.10
	 * @return array $list_ids
	 */
	public function city_list_ids() {

		$list_ids = $this->idx_api( 'cities', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );
		return $list_ids;
	}

	/**
	 * Begin creating city_list function
	 * Returns a list of cities
	 *
	 * @since 2.5.10
	 * @param integer $list_id contains the id of the specified city list.
	 * @return array $city_list.
	 */
	public function city_list( $list_id ) {

		$city_list = $this->idx_api( 'cities/' . $list_id, Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list;
	}

	/**
	 * Returns a property count integer for an id (city, county, or zip)
	 *
	 * @since 2.5.10
	 * @param string $type The count type (city, county or zip).
	 * @param string $idx_id The idxID (mlsID).
	 * @param string $id The identifier. City id, county id or zip code.
	 * @return array $city_list
	 */
	public function property_count_by_id( $type = 'city', $idx_id, $id ) {

		$city_count = $this->idx_api( 'propertycount/' . $idx_id . '?countType=' . $type . '&countSpecifier=' . $id, Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 12 );

		return $city_count;
	}

	/**
	 * Begin creating city_list_names
	 * Returns the IDs and names for each of a client's city lists including MLS city lists
	 *
	 * @since 2.5.10
	 * @return array
	 */
	public function city_list_names() {

		$city_list_names = $this->idx_api( 'citieslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $city_list_names;
	}

	/**
	 * Begin creating county_list_names function
	 * Returns the IDs and names for each of a client's county lists including MLS county lists
	 *
	 * @since 2.5.10
	 * @return array
	 */
	public function county_list_names() {

		$county_list_names = $this->idx_api( 'countieslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $county_list_names;
	}

	/**
	 * Returns the IDs and names for each of a client's postalcodes lists including MLS postalcodes lists
	 *
	 * @since 2.5.10
	 * @return array
	 */
	public function postalcode_list_names() {

		$postalcodes_list_names = $this->idx_api( 'postalcodeslistname', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients' );

		return $postalcodes_list_names;
	}

	/**
	 * Returns the subdomain url WITH trailing slash
	 *
	 * @since 2.5.10
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
	 *
	 * @since 2.5.10
	 * @return mixed id number of the approved mls(s).
	 */
	public function approved_mls() {

		$approved_mls = $this->idx_api( 'approvedmls', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 24 );

		return $approved_mls;
	}

	/**
	 * Returns search field names for an MLS
	 *
	 * @since 2.5.10
	 * @param integer $idxID ID number for approved MLS.
	 * @return search fields for the approved MLS.
	 */
	public function searchfields( $idxID ) { // $idxID is not in valid snake_case format.
		$approved_mls = $this->idx_api( "searchfields/$idxID", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}
	/**
	 * Begin creating searchfieldvalues function
	 *
	 * @since 2.5.10
	 * @param integer $idxID is the approved MLS ID Number.
	 * @param mixed   $fieldName is the search field name.
	 * @param integer $mlsPtID is the Property type ID for the search field in the MLS.
	 * @return search field values.
	 */
	public function searchfieldvalues( $idxID, $fieldName, $mlsPtID ) { // IidxID, $fieldName and $mlsPtID are not in valid snake_case format.
		$approved_mls = $this->idx_api( "searchfieldvalues/$idxID?mlsPtID=$mlsPtID&name=$fieldName", Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array() );

		return $approved_mls;
	}

	/**
	 * Compares the price fields of two arrays
	 *
	 * @since 2.5.10
	 * @param array $a contains an array of prices.
	 * @param array $b contains an array of prices.
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
	 * Removes the "$" and "," from the price field
	 *
	 * @since 2.5.10
	 * @param string $price contains $'s and ,'s.
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
	 * Begin creating platinum_account_type function.
	 *
	 * @since 2.5.10
	 * @access public
	 * @return bool
	 */
	public function platinum_account_type() {
		$account_type = $this->idx_api( 'accounttype', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 60 * 60 * 24 );
		if ( 'object' !== gettype( $account_type ) && ( 'IDX Broker Platinum' === $account_type[0] || 'IDX Broker Platinum Legacy' === $account_type[0] || 'IDX Broker HOME' === $account_type[0] || 'IDX Broker HOME Legacy' === $account_type[0] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Begin creating get_leads function.
	 *
	 * @access public
	 * @param mixed  $timeframe (default: null) contains timestamps for the lead.
	 * @param string $start_date (default: '') contains the date to begin looking for leads.
	 * @since 2.5.10
	 * @return array of lead data
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
	 * Begin creating get_featured_listings function
	 *
	 * @since 2.5.10
	 * @param mixed $listing_type contains the type of property.
	 * @param mixed $timeframe contains the date and time the property was added.
	 * @return array of featured listings.
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
	 * Returns agents wrapped in option tags
	 *
	 * @since 2.5.10
	 * @param  int $agent_id Instance agentID if exists.
	 * @return str           HTML options tags of agents ids and names
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
	 * @since 2.5.10
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
