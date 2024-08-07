<?php
/**
 * REST API: Omnibar_Settings
 *
 * Adds routes for the Omnibar settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for Omnibar settings page routes.
 *
 * Supports GET and POST requests that return/set the Omnibar settings.
 */
class Omnibar_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/omnibar' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'admin_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/omnibar' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'admin_check' ],
				'args'                => [
					'cityListSelected'            => [
						'type' => 'string',
					],
					'countyListSelected'          => [
						'type' => 'string',
					],
					'postalCodeSelected'          => [
						'type' => 'string',
					],
					'defaultPropertyTypeSelected' => [
						'type' => 'string',
					],
					'mlsMembership'               => [
						'type' => 'array',
					],
					'autofillMLSSelected'         => [
						'type' => 'array',
					],
					'customFieldsSelected'        => [
						'type' => 'array',
					],
					'customPlaceholder'           => [
						'type' => 'string',
					],
					'defaultSortOrderSelected'    => [
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
		$current_city_list    = get_option( 'idx_omnibar_current_city_list', '' );
		$current_county_list  = get_option( 'idx_omnibar_current_county_list', '' );
		$current_zipcode_list = get_option( 'idx_omnibar_current_zipcode_list', '' );
		$placeholder          = get_option( 'idx_omnibar_placeholder', '' );
		$sort                 = get_option( 'idx_omnibar_sort', '' );

		// Map legacy data structures to the REST standard.
		$city_lists    = $this->map_keys(
			get_option( 'idx_omnibar_city_lists', [] ),
			[
				'id'   => 'value',
				'name' => 'label',
			]
		);
		$county_lists  = $this->map_keys(
			get_option( 'idx_omnibar_county_lists', [] ),
			[
				'id'   => 'value',
				'name' => 'label',
			]
		);
		$zipcode_lists = $this->map_keys(
			get_option( 'idx_omnibar_zipcode_lists', [] ),
			[
				'id'   => 'value',
				'name' => 'label',
			]
		);
		$custom_lists  = $this->map_keys(
			get_option( 'idx_omnibar_custom_fields', [] ),
			[
				'name' => 'label',
			]
		);

		// Get Omnibar advanced fields.
		$omnibar_advanced_fields = $this->idx_omnibar_advanced_fields();
		$mls_membership          = $this->mls_property_types( $omnibar_advanced_fields );
		$default_prop_type       = $this->default_selected_property_type();
		$address_mls_list        = $this->get_address_mls_list( $mls_membership );

		// Map legacy data structures to the REST standard.
		$custom_fields_options = $this->map_keys(
			$omnibar_advanced_fields[0],
			[
				'mls_name'    => 'mlsName',
				'field_names' => 'fieldNames',
			]
		);
		$custom_fields_options = array_map(
			function ( $mls ) {
				$mls['fieldNames'] = $this->filter_custom_fields( $mls['fieldNames'] );
				return $mls;
			},
			$custom_fields_options
		);

		return rest_ensure_response(
			[
				'cityListOptions'             => $city_lists,
				'cityListSelected'            => $current_city_list,
				'countyListOptions'           => $county_lists,
				'countyListSelected'          => $current_county_list,
				'postalCodeListOptions'       => $zipcode_lists,
				'postalCodeSelected'          => $current_zipcode_list,
				'defaultPropertyTypeSelected' => $default_prop_type,
				'mlsMembership'               => $mls_membership,
				'autofillMLSSelected'         => $address_mls_list,
				'customFieldsSelected'        => $custom_lists,
				'customFieldsOptions'         => $custom_fields_options,
				'customPlaceholder'           => $placeholder,
				'defaultSortOrderSelected'    => $sort,
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
		// TODO: Standardize text sanitizing. Currently using existing legacy filters.
		if ( isset( $payload['cityListSelected'] ) ) {
			update_option( 'idx_omnibar_current_city_list', htmlspecialchars( $payload['cityListSelected'] ), false );
		}
		if ( isset( $payload['countyListSelected'] ) ) {
			update_option( 'idx_omnibar_current_county_list', htmlspecialchars( $payload['countyListSelected'] ), false );
		}
		if ( isset( $payload['postalCodeSelected'] ) ) {
			update_option( 'idx_omnibar_current_zipcode_list', htmlspecialchars( $payload['postalCodeSelected'] ), false );
		}
		if ( isset( $payload['customPlaceholder'] ) ) {
			update_option( 'idx_omnibar_placeholder', sanitize_text_field( wp_unslash( $payload['customPlaceholder'] ) ), false );
		}
		if ( isset( $payload['defaultPropertyTypeSelected'] ) && isset( $payload['mlsMembership'] ) ) {
			$default_prop_types   = [];
			$default_prop_types[] = [
				'idxID'   => 'basic',
				'mlsPtID' => $payload['defaultPropertyTypeSelected'],
			];

			// Convert mlsMembership payload to supported Omnibar format.
			$mls_prop_types = $this->map_keys(
				$payload['mlsMembership'],
				[
					'value'    => 'idxID',
					'selected' => 'mlsPtID',
				],
				true
			);

			$default_prop_types = array_merge( $default_prop_types, $mls_prop_types );
			$default_prop_types = filter_var_array( wp_unslash( $default_prop_types ), FILTER_SANITIZE_STRING );
			update_option( 'idx_default_property_types', $default_prop_types );
		}

		if ( isset( $payload['autofillMLSSelected'] ) ) {
			$address_changed = $this->update_addresses( $payload['autofillMLSSelected'] );
		}
		if ( isset( $payload['customFieldsSelected'] ) ) {
			$custom_fields_selected = $this->map_keys(
				$payload['customFieldsSelected'],
				[
					'label' => 'name',
				]
			);
			update_option( 'idx_omnibar_custom_fields', $custom_fields_selected, false );
		}
		if ( isset( $payload['defaultSortOrderSelected'] ) ) {
			update_option( 'idx_omnibar_sort', $payload['defaultSortOrderSelected'], false );
		}
		$this->update_location_data( $address_changed );
		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Retrieves the Omnibar advanced fields.
	 * TODO: Modernize this process.
	 *
	 * @return array Omnibar advanced fields.
	 */
	private function idx_omnibar_advanced_fields() {
		$idx_api  = new \Idx\Idx_Api();
		$mls_list = $idx_api->approved_mls();
		// Grab all advanced field names for all MLS.
		// Grab all idxIDs for account.
		$all_mls_fields = [];
		$all_mls_pt_ids = [];
		// Grab all field names for each idxID.
		foreach ( $mls_list as $mls ) {
			$idx_id                    = $mls->id;
			$mls_name                  = $mls->name;
			$fields                    = $idx_api->idx_api( "searchfields/$idx_id", $idx_api->idx_api_get_apiversion(), 'mls', array(), 86400 );
			$property_types            = $idx_api->idx_api( "propertytypes/$idx_id", $idx_api->idx_api_get_apiversion(), 'mls', array(), 86400 );
			$mls_object                = new \IDX\Widgets\Omnibar\Advanced_Fields( $idx_id, $mls_name, $fields, $property_types );
			$mls_fields_object         = $mls_object->return_fields();
			$mls_property_types_object = $mls_object->return_mlsPtIDs();
			// Push all fieldnames for each MLS to array.
			array_push( $all_mls_fields, $mls_fields_object );
			array_push( $all_mls_pt_ids, $mls_property_types_object );
		}
		return array( array_unique( $all_mls_fields, SORT_REGULAR ), $all_mls_pt_ids );
	}

	/**
	 * Returns MLS membership data with property types.
	 * Works with legacy Omnibar settings data structures.
	 *
	 * @param array $all_mls_fields Omnibar mls fields.
	 * @return array MLS membership data with property types.
	 */
	private function mls_property_types( $all_mls_fields ) {
		return array_map(
			function ( $mls ) {
				$prop_types = array_map(
					function( $prop_type ) use ( &$mls ) {
						return [
							'value' => $prop_type->mlsPtID,
							'label' => $prop_type->mlsPropertyType,
						];
					},
					$mls['property_types']
				);

				$selected = array_values(
					array_filter(
						get_option( 'idx_default_property_types', [] ),
						function( $prop_type ) use ( &$mls ) {
							if ( $mls['idxID'] === $prop_type['idxID'] ) {
								return true;
							}
						}
					)
				)[0] ?? [];

				$output = [
					'label'         => $mls['mls_name'],
					'value'         => $mls['idxID'],
					'propertyTypes' => $prop_types,
				];

				if ( ! empty( $selected ) ) {
					$output['selected'] = $selected['mlsPtID'];
				}

				return $output;
			},
			$all_mls_fields[1]
		);
	}

	/**
	 * Update the Omnibar address autocomplete.
	 *
	 * @param array $mls_membership MLS membership object.
	 * @return array Array of addresses in form { value => mlsID, label => mlsName }.
	 */
	private function get_address_mls_list( $mls_membership ) {
		$mls_list = get_option( 'idx_broker_omnibar_address_mls', [] );
		return array_map(
			function( $address_mls ) use ( &$mls_membership ) {
				$label = '';
				foreach ( $mls_membership as $key => $mls ) {
					if ( $address_mls === $mls['value'] ) {
						$label = $mls['label'];
						break;
					}
				}
				return [
					'value' => $address_mls,
					'label' => $label,
				];
			},
			$mls_list
		);
	}

	/**
	 * Update the Omnibar address autocomplete.
	 *
	 * @param array $payload New address fields.
	 * @return boolean If the address has changed or not.
	 */
	private function update_addresses( $payload ) {
		$address_change = false;
		$existing       = get_option( 'idx_broker_omnibar_address_mls', [] );
		$mls_list       = array_map(
			function ( $mls ) {
				return $mls['value'];
			},
			$payload
		);
		sort( $mls_list );
		sort( $existing );
		if ( $mls_list !== $existing ) {
			$address_change = true;
			update_option( 'idx_broker_omnibar_address_mls', $mls_list, false );
		}
		return $address_change;
	}

	/**
	 * Returns default IDX prop type.
	 *
	 * @return string|void The default prop type.
	 */
	private function default_selected_property_type() {
		$default_property_type = get_option( 'idx_default_property_types' );
		$selected              = array_values(
			array_filter(
				get_option( 'idx_default_property_types', [] ),
				function( $prop_type ) {
					if ( 'basic' === $prop_type['idxID'] ) {
						return true;
					}
				}
			)
		);
		if ( ! empty( $selected ) ) {
			return $selected[0]['mlsPtID'];
		}
		return '';
	}

	/**
	 * Triggers the cron to update location data.
	 *
	 * @param boolean $addresses_changed Tracks if address autocomplete has changed.
	 * @return void
	 */
	private function update_location_data( $addresses_changed ) {
		$update = $addresses_changed ? 'all' : 'custom';
		wp_schedule_single_event( time(), 'idx_update_location_data', [ $update ] );
	}

	/**
	 * Filters custom field list.
	 *
	 * Blacklists certain custom fields and maps field names for frontend usage.
	 *
	 * @param array $fields Custom field array.
	 * @return array
	 */
	private function filter_custom_fields( $fields ) {
		// Hash table for blacklisted custom fields.
		$blacklist = [
			'address'    => true,
			'cityName'   => true,
			'countyName' => true,
			'zipcode'    => true,
		];
		return array_reduce(
			$fields,
			function( $carry, $field ) use ( &$blacklist ) {
				$field = (array) $field;
				if ( ! isset( $blacklist[ $field['name'] ] ) ) {
					$carry[] = [
						'value'      => $field['name'],
						'label'      => $field['displayName'],
						'mlsPtID'    => $field['mlsPtID'],
						'parentPtID' => $field['parentPtID'],
					];
				}
				return $carry;
			},
			[]
		);
	}
}

new Omnibar_Settings();
