<?php
namespace IDX\Widgets\Omnibar;

/**
 * Get_Locations class.
 */
class Get_Locations {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param string $update (default: 'all')
	 * @return void
	 */
	public function __construct( $update = 'all' ) {

		$api = get_option( 'idx_broker_apikey' );
		if ( empty( $api ) ) {
			return;
		}
		$this->idx_api = new \IDX\Idx_Api();
		if ( isset( $this->idx_api->idx_api_get_systemlinks()->errors ) ) {
			return;
		}

		$this->mls_list = $this->idx_api->approved_mls();

		$this->address_mls = get_option( 'idx_broker_omnibar_address_mls', [] );
		if ( ! is_array( $this->address_mls ) ) {
			$this->address_mls = [];
		}

		$this->property_types = get_option( 'idx_default_property_types' );

		switch ( $update ) {
			case 'address':
				$this->create_autocomplete_table();
				break;
			case 'custom':
				$this->initiate_get_locations();
				break;
			case 'all':
				$this->initiate_get_locations();
				$this->create_autocomplete_table();
				break;
		}
	}

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * address_mls
	 *
	 * @var mixed
	 * @access private
	 */
	private $address_mls;

	/**
	 * mls_list
	 *
	 * @var mixed
	 * @access private
	 */
	private $mls_list;

	/**
	 * property_types
	 *
	 * @var mixed
	 * @access private
	 */
	private $property_types;

	/*
	 * Custom Advanced Fields added via admin
	 */

	/**
	 * get_idxIDs function.
	 *
	 * @access public
	 * @param mixed $array
	 * @return void
	 */
	public function get_idxIDs( $array ) {
		$idxIDs = array();
		foreach ( $array as $field ) {
			$idxID = $field['idxID'];
			if ( ! in_array( $idxID, $idxIDs ) ) {
				array_push( $idxIDs, $idxID );
			}
		}
		return $idxIDs;
	}


	/**
	 * fields_in_idxID function.
	 *
	 * @access public
	 * @param mixed $idxIDMatch
	 * @param mixed $fields
	 * @return void
	 */
	public function fields_in_idxID( $idxIDMatch, $fields ) {
		$output              = '';
		$first_run_for_idxID = true;
		for ( $i = 0; $i < count( $fields ); $i++ ) {
			$field   = $fields[ $i ];
			$idxID   = $field['idxID'];
			$name    = $field['value'];
			$mlsPtID = $field['mlsPtID'];
			$prefix  = ', {"' . $name . '" : ';
			if ( $first_run_for_idxID ) {
				$prefix = '{"' . $name . '" : ';
			}
			if ( $idxIDMatch === $idxID ) {
				$first_run_for_idxID = false;
				$field_values        = json_encode( $this->idx_api->idx_api( "searchfieldvalues/$idxID?mlsPtID=$mlsPtID&name=$name", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400 ) );
				$output             .= "$prefix $field_values }";
			}
		}
		return $output;
	}

	// used to retrieve all fields and create JSON objects by each idxID for each field
	public function get_additional_fields() {
		$fields = get_option( 'idx_omnibar_custom_fields' );
		if ( empty( $fields ) ) {
			return;
		}
		$idxIDs = $this->get_idxIDs( $fields );
		$output = '';
		foreach ( $idxIDs as $idxID ) {
			$fields_in_idxID = $this->fields_in_idxID( $idxID, $fields );
			$output         .= ", {\"$idxID\" : [ $fields_in_idxID ]}";
		}
		return $output;
	}

	// for display on the front end.
	public function create_custom_fields_key() {
		$custom_fields_key = array();
		$fields            = get_option( 'idx_omnibar_custom_fields' );
		if ( empty( $fields ) ) {
			return 'var customFieldsKey = {}; ';
		}
		foreach ( $fields as $field ) {
			$name                       = $field['value'];
			$mlsPtID                    = $field['mlsPtID'];
			$displayName                = $field['name'];
			$custom_fields_key[ $name ] = $displayName;
		}
		return 'var customFieldsKey = ' . json_encode( $custom_fields_key ) . '; ';
	}

	/**
	 * get_cczs function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_cczs() {
		// Get correct CCZ List set in admin
		$omnibar_city    = get_option( 'idx_omnibar_current_city_list' );
		$omnibar_county  = get_option( 'idx_omnibar_current_county_list' );
		$omnibar_zipcode = get_option( 'idx_omnibar_current_zipcode_list' );
		// If none is set yet, use cobinedActiveMLS
		if ( empty( $omnibar_city ) ) {
			$omnibar_city = 'combinedActiveMLS';
			update_option( 'idx_omnibar_current_city_list', 'combinedActiveMLS', false );
		}
		if ( empty( $omnibar_county ) ) {
			$omnibar_county = 'combinedActiveMLS';
			update_option( 'idx_omnibar_current_county_list', 'combinedActiveMLS', false );
		}
		if ( empty( $omnibar_zipcode ) ) {
			$omnibar_zipcode = 'combinedActiveMLS';
			update_option( 'idx_omnibar_current_zipcode_list', 'combinedActiveMLS', false );
		}
		// grab responses for CCZs and add JSON object container for front end JavaScript
		$cities   = '"cities" : ' . json_encode( $this->idx_api->idx_api( "cities/$omnibar_city" ) );
		$counties = ', "counties" : ' . json_encode( $this->idx_api->idx_api( "counties/$omnibar_county" ) );
		$zipcodes = ', "zipcodes" : ' . json_encode( $this->idx_api->idx_api( "postalcodes/$omnibar_zipcode" ) );
		return $cities . $counties . $zipcodes;
	}

	// Drops the table on each new data fetch.
	// This is super inefficient, but not going to bother optimizing with elastic search
	// around the corner.
	private function drop_autocomplete_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';

		$sql = "DROP TABLE IF EXISTS $table_name";

		return $wpdb->query( $sql );
	}

	// Creates our table
	public function create_autocomplete_table() {
		$drop_result = $this->drop_autocomplete_table();

		// Table failed to drop
		if ( $drop_result === false ) {
			// Return so we don't get duplicate entries in the table
			// We should probably set an alert for the user when this fails
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
			mls varchar(4) NOT NULL,
			field text NOT NULL,
			value text NOT NULL
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		// TODO: Remove this option on uninstall
		add_option( 'idx_broker_autocomplete_values_version', '1.0' );

		$this->populate_table();
	}

	// Loops through all selected address autofill MLSs & takes the property type
	// selection to insert the correct addresses into the db.
	private function populate_table() {
		foreach ( $this->address_mls as $mls ) {
			$pt_arr_id = array_search(
				$mls,
				array_column( $this->property_types, 'idxID' )
			);
			$this->address_table_insert( $mls, $this->property_types[ $pt_arr_id ]['mlsPtID'] );
		}
	}

	// Performs a wp_remote_get then does the database insert. Should do one huge insert.
	private function address_table_insert( $mls, $parent_id ) {

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'accesskey'    => get_option( 'idx_broker_apikey' ),
				'apiversion'   => \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION,
				'outputtype'   => 'json',
			),
			'timeout' => 120,
		);

		$response = wp_remote_get( "https://api.idxbroker.com/mls/searchfieldvalues/$mls?mlsPtID=$parent_id&name=address", $args );

		if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return;
		}

		$field_values = json_decode( $response['body'] );

		// Make sure we have a nonempty array
		if ( ! is_array( $field_values ) || empty( $field_values ) ) {
			return;
		}

		$field = 'address';

		$this->run_insert_query( $mls, $field_values, $field );
	}

	/**
	 * run_insert_query function.
	 *
	 * @access private
	 * @param mixed $mls
	 * @param mixed $field_values
	 * @param mixed $field
	 * @return void
	 */
	private function run_insert_query( $mls, $field_values, $field ) {
		global $wpdb;
		$wpdb->show_errors();

		$insert_values = [];

		$table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';

		$query = "INSERT INTO $table_name (mls, field, value) VALUES ";

		$it = 0;
		foreach ( $field_values as $val ) {
			if ( $it > 0 ) {
				$query .= ',';
			}
			$insert_values[] = $mls;
			$insert_values[] = $field;
			$insert_values[] = $val;
			$query          .= '(%s,%s,%s)';
			$it++;
			if ( $it >= 100 ) {
				$wpdb->query(
					$wpdb->prepare( $query, $insert_values )
				);
				$it            = 0;
				$insert_values = [];
				$query         = "INSERT INTO $table_name (mls, field, value) VALUES ";
			}
		}

		$wpdb->query(
			$wpdb->prepare( $query, $insert_values )
		);
	}

	/**
	 * initiate_get_locations function.
	 *
	 * @access private
	 * @return void
	 */
	private function initiate_get_locations() {
		$cczs = $this->get_cczs();

		// location lists together
		$locations = 'idxOmnibar( [{"core" : {' . $cczs . '} }' . $this->get_additional_fields() . ']);';

		$output = $this->create_custom_fields_key() . $locations;

		$system_links_call = $this->idx_api->idx_api_get_systemlinks();

		$city_lists    = $this->idx_api->city_list_names();
		$county_lists  = $this->idx_api->county_list_names();
		$zipcode_lists = $this->idx_api->postalcode_list_names();

		// test to confirm API call worked properly before updating JSON file etc.
		if ( ! empty( $system_links_call ) && empty( $system_links_call->errors ) ) {
			$upload_dir   = wp_upload_dir();
			$idx_dir_path = $upload_dir['basedir'] . '/idx_cache';
			if ( ! file_exists( $idx_dir_path ) ) {
				wp_mkdir_p( $idx_dir_path );
			}
			file_put_contents( $idx_dir_path . '/locationlist.js', $output );

			// update database with new results url
			// get base Url for client's results page for use on omnibar.js front end
			update_option( 'idx_results_url', $this->idx_api->system_results_url(), false );
			// Update city lists
			update_option( 'idx_omnibar_city_lists', $city_lists, false );
			update_option( 'idx_omnibar_county_lists', $county_lists, false );
			update_option( 'idx_omnibar_zipcode_lists', $zipcode_lists, false );

			// If invalid API key, display error
		} else {
			echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
		}
	}

}
