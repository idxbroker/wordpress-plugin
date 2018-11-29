<?php
namespace IDX\Views;

class Omnibar_Settings {


	public function __construct() {
		 $this->idx_api            = new \IDX\Idx_Api();
		$this->mls_list            = $this->idx_api->approved_mls();
		$this->defaults['address'] = get_option( 'idx_broker_omnibar_address_mls', [] );
		if ( ! is_array( $this->defaults['address'] ) ) {
			$this->defaults['address'] = [];
			update_option( 'idx_broker_omnibar_address_mls', [] );
		}
		$this->property_types = get_option( 'idx_default_property_types' );

		// preload via javascript if first load of view
		add_action( 'wp_ajax_idx_preload_omnibar_settings_view', array( $this, 'idx_preload_omnibar_settings_view' ) );
		add_action( 'wp_ajax_idx_update_omnibar_current_ccz', array( $this, 'idx_update_omnibar_current_ccz' ) );
		add_action( 'wp_ajax_idx_update_omnibar_custom_fields', array( $this, 'idx_update_omnibar_custom_fields' ) );
		add_action( 'wp_ajax_idx_update_sort_order', array( $this, 'idx_update_sort_order' ) );
		add_action( 'wp_ajax_idx_update_address_mls', array( $this, 'idx_update_address_mls' ) );
		add_action( 'wp_ajax_idx_update_database', array( $this, 'idx_update_database' ) );
		add_action( 'idx_get_new_location_data', array( $this, 'get_locations' ) );
	}

	private $mls_list;
	public $idx_api;
	private $defaults;
	private $property_types;

	// preload view via javascript if first load of view to give user feedback of loading the page and decreased perceived page load time
	public function idx_omnibar_settings_interface() {
		// register omnibar settings script
		wp_register_script( 'idx-omnibar-settings', plugins_url( '/assets/js/idx-omnibar-settings.min.js', dirname( dirname( __FILE__ ) ) ), 'jquery' );
		wp_enqueue_style( 'idx-omnibar-settings', plugins_url( '/assets/css/idx-omnibar-settings.css', dirname( dirname( __FILE__ ) ) ) );
		if ( $this->idx_api->get_transient( 'idx_mls_approvedmls_cache' ) !== false ) {
			$this->idx_preload_omnibar_settings_view();
		} else {
			echo '<div class="loading" style="margin-top: 2rem; font-size: 1rem;">Loading Omnibar Settings...</div><div class="idx-loader"></div>';
			// Tell JS to reload page when ready.
			wp_localize_script( 'idx-omnibar-settings', 'loadOmnibarView', 'true' );
		}
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), '4.0.5', 'all'  );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array( 'jquery' ), '4.0.5', true );

		return wp_enqueue_script( 'idx-omnibar-settings' );
	}

	public function idx_preload_omnibar_settings_view() {
		global $api_error;
		$search_item      = array( '_', '-' );
		$display_class    = '';
		$savedlinks       = '';
		$systemlinks      = '';
		$check_sys_option = '';

		if ( ! $api_error ) {
			$systemlinks = $this->idx_api->idx_api_get_systemlinks();
			if ( is_wp_error( $systemlinks ) ) {
				$api_error   = $systemlinks->get_error_message();
				$systemlinks = '';
			}
			$savedlinks = $this->idx_api->idx_api_get_savedlinks();
			if ( is_wp_error( $savedlinks ) ) {
				$api_error  = $savedlinks->get_error_message();
				$savedlinks = '';
			}
		}
		// if API error, display error and do not attempt to load the rest of the page
		if ( $api_error ) {
			return "<div class=\"error\">$api_error</div>";
		}
		// Shows which ccz list is currently being used by the omnibar
		$omnibar_cities   = $this->idx_api->city_list_names();
		$omnibar_counties = $this->idx_api->county_list_names();
		$omnibar_zipcodes = $this->idx_api->postalcode_list_names();
		?>
		<div class="wrap">
			<div class="">
				<div class="inside">
					<form>
						<div id="omnibar-ccz">
							<a href="http://www.idxbroker.com" target="_blank" class="logo-link">
								<div id="logo"></div>
							</a>
							<h2>IMPress Omnibar Search Widget Settings</h2>
							<h3>City, County, and Postal Code Lists</h3>
							<div class="help-text">
								Choose which custom City, County, or Postal Code lists to use for the Omnibar. Only locations in these lists will return results.
								<div>
									<i>Example: Combined Active MLS Cities List</i>
								</div>
							</div>

		<div class="city-list select-div"><label>City List:</label><select name="city-list">
		<?php
		// Cities
		foreach ( $omnibar_cities as $lists => $list ) {
			foreach ( $list as $list_option => $list_option_value ) {
				if ( $list_option === 'id' ) {
					$id = $list_option_value;
				}
				if ( $list_option === 'name' ) {
					$name = $list_option_value;
				}
			}
			// create options for each list and select currently saved option in select by default
			$saved_list = $this->idx_saved_or_default_list( get_option( 'idx_omnibar_current_city_list' ) );
			echo "<option value=\"$id\"" . selected( $id, $saved_list, false ) . ">$name</option>";
		}
		echo '</select></div><div class="county-list select-div"><label>County List:</label><select name="county-list">';
		// Counties
		foreach ( $omnibar_counties as $lists => $list ) {
			foreach ( $list as $list_option => $list_option_value ) {
				if ( $list_option === 'id' ) {
					$id = $list_option_value;
				}
				if ( $list_option === 'name' ) {
					$name = $list_option_value;
				}
			}
			// create options for each list and select currently saved option in select by default
			$saved_list = $this->idx_saved_or_default_list( get_option( 'idx_omnibar_current_county_list' ) );
			echo "<option value=\"$id\"" . selected( $id, $saved_list, false ) . ">$name</option>";
		}
		echo '</select></div><div class="zipcode-list select-div"><label>Postal Code List:</label><select name="zipcode-list">';
		// Zipcodes
		foreach ( $omnibar_zipcodes as $lists => $list ) {
			foreach ( $list as $list_option => $list_option_value ) {
				if ( $list_option === 'id' ) {
					$id = $list_option_value;
				}
				if ( $list_option === 'name' ) {
					$name = $list_option_value;
				}
			}
			// create options for each list and select currently saved option in select by default
			$saved_list = $this->idx_saved_or_default_list( get_option( 'idx_omnibar_current_zipcode_list' ) );
			echo "<option value=\"$id\"" . selected( $id, $saved_list, false ) . ">$name</option>";
		}
		echo '</select></div></div>';

		// Advanced Fields:
		$all_mls_fields = $this->idx_omnibar_advanced_fields();
		// Default property type for each MLS
		$default_property_type = get_option( 'idx_default_property_types' );
		echo '<h3>Property Type</h3><div class="idx-property-types">';
		echo '<div class="help-text">Choose the property type for default and custom fields Omnibar searches.</div>';
		?>

		<div class="select-div">
			<label for="basic">Default Property Type:</label><select class="omnibar-mlsPtID" name="basic">
			<option <?php selected( 'all', $this->idx_in_saved_array( 'all', $default_property_type, 'basic' ) ); ?> value="all">All Property Types</option>

				<option <?php selected( 'sfr', $this->idx_in_saved_array( 'sfr', $default_property_type, 'basic' ) ); ?> value="sfr">Single Family Residential</option>
				<option <?php selected( 'com', $this->idx_in_saved_array( 'com', $default_property_type, 'basic' ) ); ?> value="com">Commercial</option>
				<option <?php selected( 'ld', $this->idx_in_saved_array( 'ld', $default_property_type, 'basic' ) ); ?> value="ld">Lots and Land</option>
				<option <?php selected( 'mfr', $this->idx_in_saved_array( 'mfr', $default_property_type, 'basic' ) ); ?> value="mfr">Multifamily Residential</option>
				<option <?php selected( 'rnt', $this->idx_in_saved_array( 'rnt', $default_property_type, 'basic' ) ); ?> value="rnt">Rentals</option>
			</select>
		</div>

		<div class="mls-specific-pt">
			<h4>MLS Specific Property Type (For Custom Fields Searches and Addresses)</h4>
		<?php
		// store array of property type names, idxIDs, and  mlsPtIDs
		$mls_pt_key = array();
		foreach ( $all_mls_fields[1] as $mls ) {
			$mls_name       = $mls['mls_name'];
			$idxID          = $mls['idxID'];
			$property_types = json_decode( $mls['property_types'] );
			echo "<div class=\"select-div\"><label for=\"$idxID\">$mls_name:</label>";
			echo "<select class=\"omnibar-mlsPtID\" name=\"$idxID\">";
			foreach ( $property_types as $property_type ) {
				$mlsPtID         = $property_type->mlsPtID;
				$mlsPropertyType = $property_type->mlsPropertyType;
				// for finding property type name for custom fields
				array_push(
					$mls_pt_key,
					array(
						'idxID'           => $idxID,
						'mlsPtID'         => $mlsPtID,
						'mlsPropertyType' => $mlsPropertyType,
					)
				);
				echo "<option value=\"$mlsPtID\"" . selected( $mlsPtID, $this->idx_in_saved_array( $mlsPtID, get_option( 'idx_default_property_types' ), $idxID, $mlsPtID ), false ) . ">$mlsPropertyType</option>";
			}
			echo '</select></div>';
		}

		echo '</div>';

		// Addresses:
		echo '<h3>Addresses</h3><div class="idx-omnibar-address-settings">';
		echo '<div class="help-text">Choose which MLS is included in the address autofill. Addresses will only be included from the above selected property types.';
		echo '<br />Do <b>NOT</b> select address as a custom field while using this option.</div>';
		?>
		<div class="select-div">
			<select id="omnibar-address-mls" class="omnibar-address-multiselect" name="address-mls[]" multiple="multiple" autocomplete="off">
				<?php
				foreach ( $this->mls_list as $mls ) {
					?>
					<option value="<?php echo esc_attr( $mls->id ); ?>" <?php selected( $mls->id, $this->address_selected( $mls->id ) ); ?>>
						<?php echo esc_html( $mls->name ); ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<?php

		// echo them as one select
		echo '<h3>Custom Fields</h3>';
		echo '<div class="help-text">By default the omnibar searches by City, County, Postal Code, or Listing ID. Add up to 10 custom fields to be used as well.<div><i>Examples: High School, Area, Subdivision</i></div></div>';
		echo '<div class="customFieldError error"><p></p></div>';

		// There is a bug in firefox that will select all options of the same value on
		// refresh if one option of that value is already selected. The omnibar logic
		// relies on specific selected option behavior, so we add autocomplete="off"
		// to force Firefox to not cache its option selections on refresh.
		echo '<select class="omnibar-additional-custom-field select2" name="omnibar-additional-custom-field" multiple="multiple" autocomplete="off">';

		echo $this->get_all_custom_fields( $all_mls_fields[0], $mls_pt_key );

		echo '</select>';

		$placeholder = get_option( 'idx_omnibar_placeholder' );
		if ( empty( $placeholder ) ) {
			$placeholder = 'City, Postal Code, Address, or Listing ID';
		}
		echo '<h3>Custom Placeholder</h3>';
		echo '<div class="help-text">This is a placeholder for the main input of Omnibar widgets.<div><i>Examples: "Search for Properties" or "Location, School, Address, or Listing ID"</i></div></div>';
		echo "<input class=\"omnibar-placeholder\" type=\"text\" value=\"$placeholder\">";

		echo '<h3>Default Sort Order</h3>';
		echo '<div class="help-text">Choose a default sort order for results pages.</div>';

		$sort_order = get_option( 'idx_omnibar_sort' );
		if ( empty( $sort_order ) ) {
			$sort_order = 'newest';
		}
		echo '
        <select id="sort-order" class="sort-order">
            <option value="newest" ' . selected( $sort_order, 'newest', false ) . '>Newest Listings</option>
            <option value="oldest" ' . selected( $sort_order, 'oldest', false ) . '>Oldest Listings</option>
            <option value="pra" ' . selected( $sort_order, 'pra', false ) . '>Least expensive to most</option>
            <option value="prd" ' . selected( $sort_order, 'prd', false ) . '>Most expensive to least</option>
            <option value="bda" ' . selected( $sort_order, 'bda', false ) . '>Bedrooms (Low to High)</option>
            <option value="bdd" ' . selected( $sort_order, 'bdd', false ) . '>Bedrooms (High to Low)</option>
            <option value="tba" ' . selected( $sort_order, 'tba', false ) . '>Bathrooms (Low to High)</option>
            <option value="tbd" ' . selected( $sort_order, 'tbd', false ) . '>Bathrooms (High to Low)</option>
            <option value="sqfta" ' . selected( $sort_order, 'sqfta', false ) . '>Square Feet (Low to High)</option>
            <option value="sqftd" ' . selected( $sort_order, 'sqftd', false ) . '>Square Feet (High to Low)</option>
        </select>
        ';

		echo '</div>';
		echo <<<EOT
                        <div class="saveFooter">
                        <input type="submit" value="Save Changes" id="save_changes" class="button-primary update_idxlinks"  />
                        <span class="status"></span>
                        <input type="hidden" name="action_mode" id="action_mode" value="" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
EOT;

	}

	// Checks if address MLS is selected, to be used with WordPress's select() function.
	public function address_selected( $mls ) {
		// If this mls was already selected, return the mls id ($mls) since WordPress's
		// select() function compares two values and we want them to be the same.
		if ( in_array( $mls, $this->defaults['address'] ) ) {
			return $mls;
		}
		// If the address wasn't selected already, return 0, which does not match any MLS id.
		return 0;
	}

	// find the display name of the mls property type
	public function find_property_type( $idxID, $mlsPtID, $mls_pt_key ) {
		foreach ( $mls_pt_key as $mls_pt ) {
			if ( $mls_pt['idxID'] === $idxID && $mls_pt['mlsPtID'] === $mlsPtID ) {
				return $mls_pt['mlsPropertyType'];
			}
		}
	}

	public function idx_in_saved_array( $name, $array, $idxID, $mlsPtID = null ) {
		if ( empty( $array ) ) {
			return false;
		}
		foreach ( $array as $field ) {
			// for when mlsptid is irrelevant, do not throw errors
			if ( $mlsPtID === null ) {
				$mlsPtID = $idxID;
			}
			if ( in_array( $name, $field ) && in_array( $idxID, $field ) && in_array( $mlsPtID, $field ) ) {
				return $name;
			}
		}
	}

	public function idx_saved_or_default_list( $list_name ) {
		if ( empty( $list_name ) ) {
			return 'combinedActiveMLS';
		} else {
			return $list_name;
		}
	}

	// custom fields:
	public function idx_omnibar_advanced_fields() {

		// Grab all advanced field names for all MLS
		// grab all idxIDs for account
		$all_mls_fields = array();
		$all_mlsPtIDs   = array();
		// grab all field names for each idxID
		foreach ( $this->mls_list as $mls ) {
			$idxID                     = $mls->id;
			$mls_name                  = $mls->name;
			$fields                    = json_encode( $this->idx_api->idx_api( "searchfields/$idxID", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400 ) );
			$property_types            = json_encode( $this->idx_api->idx_api( "propertytypes/$idxID", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400 ) );
			$mls_object                = new \IDX\Widgets\Omnibar\Advanced_Fields( $idxID, $mls_name, $fields, $property_types );
			$mls_fields_object         = $mls_object->return_fields();
			$mls_property_types_object = $mls_object->return_mlsPtIDs();
			// push all fieldnames for each MLS to array
			array_push( $all_mls_fields, $mls_fields_object );
			array_push( $all_mlsPtIDs, $mls_property_types_object );
		}
		return array( array_unique( $all_mls_fields, SORT_REGULAR ), $all_mlsPtIDs );
	}

	public function get_all_custom_fields( $mls_fields, $mls_pt_key ) {
		$output = '';
		foreach ( $this->mls_list as $mls ) {
			$idxID = $mls->id;
			if ( $idxID !== 'basic' ) {
				$output .= $this->get_custom_fields( $idxID, $mls_fields, $mls_pt_key );
			}
		}
		return $output;
	}

	public function get_custom_fields( $idxID, $mls_fields, $mls_pt_key ) {
		$output = '';
		foreach ( $mls_fields as $mls ) {
			$mls_name = $mls['mls_name'];
			if ( $idxID === $mls['idxID'] ) {

				$output .= "<optgroup label=\"$mls_name\" class=\"$idxID\">";
				$fields  = json_decode( $mls['field_names'] );
				// make sure field names only appear once per MLS
				foreach ( $fields as $field ) {
					$name    = $field->displayName;
					$value   = $field->name;
					$mlsPtID = $field->mlsPtID;
					// find property type name
					$mls_property_type = $this->find_property_type( $idxID, $mlsPtID, $mls_pt_key );
					if ( $name !== '' ) {
						$output .= "<option value=\"$value\"" . selected( $value, $this->idx_in_saved_array( $value, get_option( 'idx_omnibar_custom_fields' ), $idxID, $mlsPtID ), false ) .
							" data-mlsPtID=\"$mlsPtID\" title=\"$name ($mls_name - $mls_property_type) \">$name</option>";
					}
				}
				$output .= '</optgroup>';
			}
		}
		return $output;
	}
	/** Update Saved CCZ Lists for Omnibar when Admin form is saved
	 *
	 * @param void
	 */
	public function idx_update_omnibar_current_ccz() {
		// Strip out HTML Special Characters before updating db to avoid security or formatting issues
		$city_list    = htmlspecialchars( $_POST['city-list'] );
		$county_list  = htmlspecialchars( $_POST['county-list'] );
		$zipcode_list = htmlspecialchars( $_POST['zipcode-list'] );
		update_option( 'idx_omnibar_current_city_list', $city_list, false );
		update_option( 'idx_omnibar_current_county_list', $county_list, false );
		update_option( 'idx_omnibar_current_zipcode_list', $zipcode_list, false );
		wp_die();
	}

	public function idx_update_omnibar_custom_fields() {
		// Strip out HTML Special Characters before updating db to avoid security or formatting issues
		if ( ! empty( $_POST['fields'] ) ) {
			$fields = $_POST['fields'];
		} else {
			$fields = array();
		}
		update_option( 'idx_omnibar_custom_fields', $fields, false );
		$output = $this->has_property_type_changed();
		update_option( 'idx_default_property_types', $_POST['mlsPtIDs'], false );
		update_option( 'idx_omnibar_placeholder', htmlspecialchars( $_POST['placeholder'] ), false );
		wp_die( $output );
	}

	public function idx_update_sort_order() {
		$sort_order = $_POST['sort-order'];
		update_option( 'idx_omnibar_sort', $sort_order, false );
		wp_die( 0 );
	}

	// Updates the mls selected for address autocomplete in wp_options
	public function idx_update_address_mls() {
		$address_mls = $_POST['address-mls'];
		if ( ! is_array( $address_mls ) ) {
			$address_mls = [];
		}
		$output = $this->has_address_changed( $address_mls );
		update_option( 'idx_broker_omnibar_address_mls', $address_mls, false );
		wp_die( $output );
	}

	// wp_schedule_single_event() allows us to run tasks in the background
	public function idx_update_database() {
		$to_update = $_POST['toUpdate'];
		wp_schedule_single_event( time(), 'idx_get_new_location_data', [ $to_update ] );
		wp_die();
	}

	// Returns 1 if property changed, 0 otherwise
	private function has_property_type_changed() {
		$arr1 = [];
		$arr2 = [];

		// Concat property type and mls id then compare POST and wp_option data to see
		// if any additional property types are present in the POST data.
		foreach ( $_POST['mlsPtIDs'] as $mls ) {
			$arr1[] = $mls['idxID'] . $mls['mlsPtID'];
		}
		foreach ( $this->property_types as $mls ) {
			$arr2[] = $mls['idxID'] . $mls['mlsPtID'];
		}
		if ( empty( array_diff( $arr1, $arr2 ) ) ) {
			return 0;
		}
		return 1;
	}

	// Returns 1 if address changed, 0 otherwise
	private function has_address_changed( $address_mls ) {
		$selected_mls = $this->defaults['address'];

		// Sort the POST field and the wp_options entry then compare the two arrays
		sort( $address_mls );
		sort( $selected_mls );
		if ( $address_mls == $selected_mls ) {
			return 0;
		}
		return 1;
	}

	public function get_locations( $update = 'all' ) {
		new \IDX\Widgets\Omnibar\Get_Locations( $update );
	}

}
