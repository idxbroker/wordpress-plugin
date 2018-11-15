<?php
add_action( 'init', array( 'IDX_Leads_CF7', 'init' ) );

class IDX_Leads_CF7 {

	public function __construct() {
		if ( ! class_exists( 'WPCF7' ) ) {
			exit;
		}

		$this->idx_api = new \IDX\Idx_Api();
	}

	public static function init() {

		add_filter( 'wpcf7_editor_panels', array( 'IDX_Leads_CF7', 'idx_add_settings_panel' ) );
		add_action( 'wpcf7_before_send_mail', array( 'IDX_Leads_CF7', 'idx_put_lead' ) );
		add_action( 'wpcf7_after_save', array( 'IDX_Leads_CF7', 'idx_save_lead_settings' ) );

		add_action( 'admin_enqueue_scripts', array( 'IDX_Leads_CF7', 'load_scripts' ) );
	}

	public $idx_api;

	public static function idx_add_settings_panel( $panels ) {

		$panels = array_merge(
			$panels,
			array(
				'idx-panel' => array(
					'title'    => __( 'IDX Broker', 'contact-form-7' ),
					'callback' => array( 'IDX_Leads_CF7', 'idx_cf7_settings' ),
				),
			)
		);

		return $panels;
	}

	public static function load_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'idx-tooltip', IMPRESS_IDX_URL . 'assets/js/tooltip.js' );
		wp_enqueue_style( 'idx-tooltip-css', IMPRESS_IDX_URL . 'assets/css/tooltip.css' );
	}

	public function idx_save_lead_settings( $args ) {

		if ( ! empty( $_POST ) ) {

			$option_name = 'idx_lead_form_' . $args->id;

			$new_value                  = array();
			$new_value['enable_lead']   = isset( $_POST['enable_lead'] ) ? (int) stripslashes( $_POST['enable_lead'] ) : 0;
			$new_value['category']      = isset( $_POST['category'] ) ? stripslashes( $_POST['category'] ) : null;
			$new_value['firstName']     = isset( $_POST['firstName'] ) ? stripslashes( $_POST['firstName'] ) : null;
			$new_value['lastName']      = isset( $_POST['lastName'] ) ? stripslashes( $_POST['lastName'] ) : null;
			$new_value['email']         = isset( $_POST['email'] ) ? stripslashes( $_POST['email'] ) : null;
			$new_value['email2']        = isset( $_POST['email2'] ) ? stripslashes( $_POST['email2'] ) : null;
			$new_value['phone']         = isset( $_POST['phone'] ) ? stripslashes( $_POST['phone'] ) : null;
			$new_value['address']       = isset( $_POST['address'] ) ? stripslashes( $_POST['address'] ) : null;
			$new_value['city']          = isset( $_POST['city'] ) ? stripslashes( $_POST['city'] ) : null;
			$new_value['stateProvince'] = isset( $_POST['stateProvince'] ) ? stripslashes( $_POST['stateProvince'] ) : null;
			$new_value['zipCode']       = isset( $_POST['zipCode'] ) ? stripslashes( $_POST['zipCode'] ) : null;
			$new_value['country']       = isset( $_POST['country'] ) ? stripslashes( $_POST['country'] ) : null;

			update_option( $option_name, $new_value, false );
		}
	}

	public static function idx_cf7_settings() {

		// Get the form object
		$form    = wpcf7_get_current_contact_form();
		$form_id = $form->id();

		// Set the form option name
		$option_name  = 'idx_lead_form_' . $form_id;
		$form_options = get_option( $option_name );

		$checked = $form_options['enable_lead'];
		if ( ! isset( $form_options['category'] ) ) {
			$form_options['category'] = '';
		}

		// Instantiate ContactForm class and get tags for form fields
		$cf7 = WPCF7_ContactForm::get_instance( get_post( $form->id() ) );

		if ( is_object( $cf7 ) ) {
			$mail_tags = $cf7->collect_mail_tags( get_post( $form->id() ) );}
		?>
			<h3><span><i class="properticons properticons-logo-idx"></i> Settings</span></h3>
			<form action="" method="post" id="cf7_form_settings">

					<table class="form-table" cellpadding="0" cellspacing="0">
					<tbody>                                     
						<tr>
							<th>Enable Lead Import?
								<a href="#" onclick="return false;" onkeypress="return false;" class="idx_tooltip tooltip tooltip_form_button_import_leads" title="<h6>Enable Lead Import</h6>Selecting this option will send form entry data as a lead and lead note in IDX Broker Middleware. If the lead already exists (by email address), a note will be added to the lead.<br /> <strong style='color: red;''>This requires that your form have a required First and Last Name field and required Email field.</strong>"><i class="fa fa-question-circle"></i></a>
							</th>
							<td>
								<input id="enable_lead" name="enable_lead"  value="1" type="checkbox" <?php checked( $checked, 1, true ); ?>>
								<label for="enable_lead">Import Leads</label>
							</td>
						</tr>
						<tr>
							<th>Assign to category (optional)
								<a href="#" onclick="return false;" onkeypress="return false;" class="idx_tooltip tooltip tooltip_form_button_import_leads" title="<h6>Assign to Category</h6>You can optionally choose a category to assign the lead to in IDX Broker Middleware."><i class="fa fa-question-circle"></i></a>
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
						<tr>
							<th><h4>Map fields</h4></th>
							<td>
								<p>Your form fields must be mapped to the available fields in IDX Broker.<br />For a lead to be added to IDX Broker, it must have First Name, Last Name, and Email Address required and mapped.</p>
							</td>
						</tr>
						<tr>
							<th>
								firstName <span class="required">*</span>
							</th>
							<td>
								<select name="firstName">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'firstName' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								lastName <span class="required">*</span>
							</th>
							<td>
								<select name="lastName">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'lastName' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								email <span class="required">*</span>
							</th>
							<td>
								<select name="email">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'email' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								email2
							</th>
							<td>
								<select name="email2">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'email2' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								phone
							</th>
							<td>
								<select name="phone">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'phone' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								address
							</th>
							<td>
								<select name="address">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'address' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								city
							</th>
							<td>
								<select name="city">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'city' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								stateProvince
							</th>
							<td>
								<select name="stateProvince">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'stateProvince' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								zipCode
							</th>
							<td>
								<select name="zipCode">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'zipCode' ); ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								country
							</th>
							<td>
								<select name="country">
									<?php echo self::output_tag_options( $mail_tags, $form_options, 'country' ); ?>
								</select>
							</td>
						</tr>
					</tbody>
					</table>
			</form>
		<?php
	}

	public function idx_put_lead( $contact_form ) {
		$form_id = $contact_form->id;

		$option_name = 'idx_lead_form_' . $form_id;

		$form_options = get_option( $option_name );

		$checked = $form_options['enable_lead'];

		$apikey = get_option( 'idx_broker_apikey' );

		// Instantiate Submission class and get posted data
		// return early if no posted data
		$submission = WPCF7_Submission::get_instance();
		if ( ! $submission || ! $posted_data = $submission->get_posted_data() ) {
			return;
		}

		if ( $checked && ! empty( $apikey ) ) {
			if ( ! empty( $form_options['firstName'] ) && ! empty( $form_options['lastName'] ) && ! empty( $form_options['email'] ) ) {

				$lead_data = array(
					'firstName'      => $posted_data[ $form_options['firstName'] ],
					'lastName'       => $posted_data[ $form_options['lastName'] ],
					'email'          => $posted_data[ $form_options['email'] ],
					'phone'          => ( ! empty( $form_options['phone'] ) ) ? $posted_data[ $form_options['phone'] ] : '',
					'address'        => ( ! empty( $form_options['address'] ) ) ? $posted_data[ $form_options['address'] ] : '',
					'city'           => ( ! empty( $form_options['city'] ) ) ? $posted_data[ $form_options['city'] ] : '',
					'stateProvince'  => ( ! empty( $form_options['stateProvince'] ) ) ? $posted_data[ $form_options['stateProvince'] ] : '',
					'zipCode'        => ( ! empty( $form_options['zipCode'] ) ) ? $posted_data[ $form_options['zipCode'] ] : '',
					'country'        => ( ! empty( $form_options['country'] ) ) ? $posted_data[ $form_options['country'] ] : '',
					'actualCategory' => ( ! empty( $form_options['category'] ) ) ? $form_options['category'] : '',
				);

				$api_url  = 'https://api.idxbroker.com/leads/lead';
				$args     = array(
					'method'    => 'PUT',
					'headers'   => array(
						'content-type' => 'application/x-www-form-urlencoded',
						'accesskey'    => get_option( 'idx_broker_apikey' ),
						'outputtype'   => 'json',
					),
					'sslverify' => false,
					'body'      => http_build_query( $lead_data ),
				);
				$response = wp_remote_request( $api_url, $args );

				// Check for error then add note
				if ( is_wp_error( $response ) ) {
					return;
				} else {

					$decoded_response = json_decode( $response['body'] );

					$note = array(
						'note' => self::output_form_fields( $posted_data ),
					);

					// Add note if lead already exists
					if ( $decoded_response == 'Lead already exists.' ) {
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
							if ( $lead['email'] == $posted_data[ $form_options['email'] ] ) {
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

	/**
	 * Output form tags as HTML options
	 *
	 * @param  array                the tags of the current form fields
	 * @param  array                                                    $form_options the form options containing the map keys => values
	 * @param  string                                                   $mapped       the field being mapped
	 * @return string               HTML options
	 */
	private static function output_tag_options( $mail_tags, $form_options, $mapped ) {
		$output = '<option value="">-- Not Mapped --</option>';
		foreach ( $mail_tags as $tag ) {
			$output .= '<option value="' . esc_html( $tag ) . '" ' . selected( $form_options[ $mapped ], $tag, 1 ) . '>' . esc_html( $tag ) . '</option>';
		}
		return $output;
	}

	private static function output_form_fields( $posted_data ) {

		$output = '';
		foreach ( $posted_data as $key => $value ) {

			if ( ! preg_match( '/_wpcf7/', $key ) ) {
				$output .= $key . ":\r\n" . $value . "\r\n\r\n";
			}
		}

		return $output;
	}
}
