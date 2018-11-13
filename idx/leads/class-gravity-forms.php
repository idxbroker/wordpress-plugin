<?php
add_action( 'init', array( 'IDX_Leads_GF', 'init' ) );

class IDX_Leads_GF {

	public function __construct() {
		if ( ! class_exists( 'GFForms' ) ) {
			exit;
		}

		$this->idx_api = new \IDX\Idx_Api();
	}

	public static function init() {
		add_action( 'gform_after_submission', array( 'IDX_Leads_GF', 'idx_put_lead' ), 10, 2 );
		add_filter( 'gform_form_settings_menu', array( 'IDX_Leads_GF', 'idx_leads_gform_settings_menu' ) );
		add_action( 'gform_form_settings_page_idx_broker_leads_page', array( 'IDX_Leads_GF', 'idx_broker_leads_page' ) );
	}

	public $idx_api;

	public static function idx_leads_gform_settings_menu( $menu_items ) {

		$menu_items[] = array(
			'name'  => 'idx_broker_leads_page',
			'label' => __( 'IDX Broker' ),
		);

		return $menu_items;
	}

	public static function idx_broker_leads_page() {
		$idx_api = new \IDX\Idx_Api();

		GFFormSettings::page_header();

		$form_id      = rgget( 'id' );
		$option_name  = 'idx_lead_form_' . $form_id;
		$form_options = get_option( $option_name );
		$checked      = $form_options['enable_lead'];
		if ( ! isset( $form_options['category'] ) ) {
			$form_options['category'] = '';
		}
		if ( ! isset( $form_options['agent_id'] ) ) {
			$form_options['agent_id'] = '';
		}

		if ( isset( $_POST['submit'] ) ) {

			$new_value                = array();
			$new_value['enable_lead'] = isset( $_POST['enable_lead'] ) ? (int) stripslashes( $_POST['enable_lead'] ) : 0;
			$new_value['category']    = isset( $_POST['category'] ) ? stripslashes( $_POST['category'] ) : 0;
			$new_value['agent_id']    = isset( $_POST['agent_id'] ) ? (int) stripslashes( $_POST['agent_id'] ) : 0;

			update_option( $option_name, $new_value, false );
		}
		?>
			<h3><span><i class="properticons properticons-logo-idx"></i> Settings</span></h3>
			<form action="" method="post" id="gform_form_settings">

					<table class="gforms_form_settings" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td colspan="2">
								<h4 class="gf_settings_subgroup_title">Lead Capture</h4>
							</td>
						</tr>                                       
						<tr>
							<th>Enable Lead Import?
								<a href="#" onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_button_import_leads" title="<h6>Enable Lead Import</h6>Selecting this option will send form entry data as a lead and lead note in IDX Broker Middleware. If the lead already exists (by email address), a note will be added to the lead.<br /> <strong style='color: red;'>This requires that your form use the advanced &#34;Name&#34; and &#34;Email&#34; fields and those fields be required.</strong>"><i class="fa fa-question-circle"></i></a>
							</th>
							<td>
								<input id="enable_lead" name="enable_lead"  value="1" type="checkbox" <?php checked( $checked, 1, true ); ?>>
								<label for="enable_lead">Import Leads</label>
							</td>
						</tr>
						<tr>
							<th>
								Assign to agent (optional)
							</th>
							<td>
								<select name="agent_id">
									<?php echo $idx_api->get_agents_select_list( $form_options['agent_id'] ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Assign to category (optional)
								<a href="#" onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_button_import_leads" title="<h6>Assign to Category</h6>You can optionally choose a category to assign the lead to in IDX Broker Middleware."><i class="fa fa-question-circle"></i></a>
							</th>
							<td>
								<select name="category">
									<option value="" <?php selected( $form_options['category'], '', 1 ); ?>>---</option>
									<option value="Buyer" <?php selected( $form_options['category'], 'Buyer', 1 ); ?>>Buyer</option>
									<option value="Contact" <?php selected( $form_options['category'], 'Contact', 1 ); ?>>Contact</option>
									<option value="Direct Signup" <?php selected( $form_options['category'], 'Direct Signup', 1 ); ?>>Direct Signup</option>
									<option value="Home Valuation" <?php selected( $form_options['category'], 'Home Valuation', 1 ); ?>>Home Valuation</option>
									<option value="More Info" <?php selected( $form_options['category'], 'More Info', 1 ); ?>>More Info</option>
									<option value="Property Updates" <?php selected( $form_options['category'], 'Property Updates', 1 ); ?>>Property Updates</option>
									<option value="Scheduled Showing" <?php selected( $form_options['category'], 'Scheduled Showing', 1 ); ?>>Scheduled Showing</option>
									<option value="Seller" <?php selected( $form_options['category'], 'Seller', 1 ); ?>>Seller</option>
									<option value="Unknown" <?php selected( $form_options['category'], 'Unknown', 1 ); ?>>Unknown</option>
								</select>
							</td>
						</tr>
					 </tbody>
				   </table>
					<button type="submit" name="submit" class="btn button-primary gfbutton">Update Settings</button>
				</form>
		<?php
		GFFormSettings::page_footer();
	}

	public static function idx_put_lead( $entry, $form ) {
		$form_id = $form['id'];

		$option_name = 'idx_lead_form_' . $form_id;

		$form_options = get_option( $option_name );

		$checked = $form_options['enable_lead'];

		$apikey = get_option( 'idx_broker_apikey' );

		if ( $checked ) {
			if ( ! empty( $apikey ) ) {

				$fields = self::get_all_form_fields( $form_id );

				$code_firstname = (string) self::find_field( $fields, 'First', 'name' );
				$firstname      = filter_var( $entry[ $code_firstname ], FILTER_SANITIZE_STRING );

				$code_lastname = (string) self::find_field( $fields, 'Last', 'name' );
				$lastname      = filter_var( $entry[ $code_lastname ], FILTER_SANITIZE_STRING );

				$code_email = (string) self::find_field( $fields, 'Email', 'email' );
				$email      = filter_var( $entry[ $code_email ], FILTER_SANITIZE_STRING );

				$code_phone = (string) self::find_field( $fields, 'Phone', 'phone' );
				if ( $code_phone ) {
					$phone = filter_var( $entry[ $code_phone ], FILTER_SANITIZE_STRING );}

				$code_streetAddress = (string) self::find_field( $fields, 'Address (Street Address)' );
				if ( $code_streetAddress ) {
					$streetAddress = filter_var( $entry[ $code_streetAddress ], FILTER_SANITIZE_STRING );}

				$code_addressLine = (string) self::find_field( $fields, 'Address (Address Line 2)' );
				if ( $code_addressLine ) {
					$addressLine = filter_var( $entry[ $code_addressLine ], FILTER_SANITIZE_STRING );}

				$code_city = (string) self::find_field( $fields, 'Address (City)' );
				if ( $code_city ) {
					$city = filter_var( $entry[ $code_city ], FILTER_SANITIZE_STRING );}

				$code_state = (string) self::find_field( $fields, 'Address (State / Province)' );
				if ( $code_state ) {
					$state = filter_var( $entry[ $code_state ], FILTER_SANITIZE_STRING );}

				$code_zip = (string) self::find_field( $fields, 'Address (ZIP / Postal Code)' );
				if ( $code_zip ) {
					$zip = filter_var( $entry[ $code_zip ], FILTER_SANITIZE_STRING );}

				$code_country = (string) self::find_field( $fields, 'Address (Country)' );
				if ( $code_country ) {
					$country = filter_var( $entry[ $code_country ], FILTER_SANITIZE_STRING );}

				// Get agent ID from form field if it exists
				// otherwise get from form settings
				$code_agent_id = (string) self::find_field( $fields, 'Agent ID' );
				if ( $code_agent_id ) {
					$agent_owner = filter_var( $entry[ $code_agent_id ], FILTER_SANITIZE_STRING );
				} else {
					$agent_owner = ( isset( $form_options['agent_id'] ) ) ? $form_options['agent_id'] : '';
				}

				$lead_data = array(
					'firstName'      => $firstname,
					'lastName'       => $lastname,
					'email'          => $email,
					'phone'          => ( isset( $phone ) ) ? $phone : '',
					'address'        => ( isset( $streetAddress ) ) ? $streetAddress : '',
					'city'           => ( isset( $city ) ) ? $city : '',
					'stateProvince'  => ( isset( $state ) ) ? $state : '',
					'zipCode'        => ( isset( $zip ) ) ? $zip : '',
					'country'        => ( isset( $country ) ) ? $country : '',
					'actualCategory' => ( isset( $form_options['category'] ) ) ? $form_options['category'] : '',
					'agentOwner'     => $agent_owner,
				);
				$api_url   = 'https://api.idxbroker.com/leads/lead';
				$args      = array(
					'method'    => 'PUT',
					'headers'   => array(
						'content-type' => 'application/x-www-form-urlencoded',
						'accesskey'    => get_option( 'idx_broker_apikey' ),
						'outputtype'   => 'json',
					),
					'sslverify' => false,
					'body'      => http_build_query( $lead_data ),
				);
				$response  = wp_remote_request( $api_url, $args );

				// Check for error then add note
				if ( is_wp_error( $response ) ) {
					return;
				} else {

					$decoded_response = json_decode( $response['body'] );

					$note = array(
						'note' => self::output_form_fields( $entry, $form_id ),
					);

					// Add note if lead already exists
					if ( 'Lead already exists.' === $decoded_response ) {
						$args = array_replace(
							$args,
							array(
								'method' => 'GET',
								'body'   => null,
							)
						);

						// Get leads
						if ( false === ( $all_leads = get_transient( 'idx_leads' ) ) ) {
							$response  = wp_remote_request( $api_url, $args );
							$all_leads = json_decode( $response['body'], 1 );
							set_transient( 'idx_leads', $all_leads, 60 * 60 * 1 );
						}

						// Loop through leads to match email address
						foreach ( $all_leads as $leads => $lead ) {
							if ( $lead['email'] === $email ) {
								$api_url  = 'https://api.idxbroker.com/leads/note/' . $lead['id'];
								$args     = array_replace(
									$args,
									array(
										'method' => 'PUT',
										'body'   => http_build_query( $note ),
									)
								);
								$response = wp_remote_request( $api_url, $args );
								if ( is_wp_error( $response ) ) {
									return;
								}
							}
						}
					} else {
						// Add note for new lead
						$lead_id  = $decoded_response->newID;
						$api_url  = 'https://api.idxbroker.com/leads/note/' . $lead_id;
						$args     = array_replace( $args, array( 'body' => http_build_query( $note ) ) );
						$response = wp_remote_request( $api_url, $args );
						if ( is_wp_error( $response ) ) {
							return;
						}
					}
				}
			}
		}
	}

	private static function get_all_form_fields( $form_id ) {
		$form   = RGFormsModel::get_form_meta( $form_id );
		$fields = array();

		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {

					foreach ( $field['inputs'] as $input ) {
						$fields[] = array(
							'id'   => $input['id'],
							'name' => GFCommon::get_label( $field, $input['id'] ),
							'type' => $field['type'],
						);
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$fields[] = array(
						'id'   => $field['id'],
						'name' => GFCommon::get_label( $field ),
						'type' => $field['type'],
					);
				}
			}
		}
		return $fields;
	}

	/**
	 * Finds the field ID given the label or optional advanced field type.
	 *
	 * @param  array  $fields Array of fields from get_all_form_fields()
	 * @param  string $label The field label or piece of label if type is name (i.e. first)
	 * @param  string $type   Optional. Advanced field type (name and email)
	 * @uses   self::get_all_form_fields()
	 *
	 * @return int|false      Field ID or false
	 */
	private static function find_field( $fields, $label, $type = null ) {
		foreach ( $fields as $field ) {
			if ( null !== $type && $type === $field['type'] ) {
				if ( 'name' === $type && is_int( strpos( $field['name'], $label ) ) ) {
					return $field['id'];
				} elseif ( 'email' === $type || 'phone' === $type ) {
					return $field['id'];
				}
			} elseif ( $field['name'] === $label ) {
				return $field['id'];
			}
		}
		return false;
	}

	private static function output_form_fields( $entry, $form_id ) {
		$fields = self::get_all_form_fields( $form_id );
		$output = '';
		foreach ( $fields as $field ) {
			$field_id    = $field['id'];
			$field_entry = filter_var( $entry[ $field_id ], FILTER_SANITIZE_STRING );

			if ( $field_entry ) {
				$output .= $field['name'] . ":\r\n" . $field_entry . "\r\n\r\n";
			}
		}

		return $output;
	}
}
