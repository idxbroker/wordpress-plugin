<?php
/**
 * Allows listing post type to use custom templates for single listings
 * Adapted from Single Post Template plugin by Nathan Rice (http://www.nathanrice.net/)
 * http://wordpress.org/plugins/single-post-template/
 *
 * Author: Nathan Rice
 * Author URI: http://www.nathanrice.net/
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * @package WP Listings
 * @since 0.1.0
 */
class Single_Listing_Template {

	/**
	 * Construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'wplistings_add_metabox' ] );
		add_action( 'save_post', [ $this, 'metabox_save' ], 1, 2 );
		add_filter( 'single_template', [ $this, 'load_listing_template' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'listing_load_dashicons' ] );
	}

	/**
	 * Listing Load Dashicons
	 *
	 * Explicitly enqueue dashicons.
	 *
	 * @return void
	 */
	public function listing_load_dashicons() {
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Load_listing_template
	 *
	 * @param  string $single - Path of template.
	 * @return array
	 */
	public function load_listing_template( $single ) {
		global $post;
		// Exit early if not listing post.
		if ( 'listing' !== $post->post_type ) {
			return $single;
		}

		// Check if template file is set.
		$post_meta = get_post_meta( $post->ID );
		if ( isset( $post_meta['_wp_post_template'][0] ) ) {
			// Return if template file is available.
			if ( ! empty( $this->get_listing_templates()[ $post_meta['_wp_post_template'][0] ] ) ) {
				return $post_meta['_wp_post_template'][0];
			}
			// Search for match is template file is missing.
			$matched_template = $this->handle_missing_template( $post->ID, $post_meta['_wp_post_template'][0] );
			if ( ! empty( $matched_template ) ) {
				return $matched_template;
			}
		}
		// Check if theme has a single-listing template.
		if ( locate_template( 'single-listing.php' ) ) {
			return locate_template( 'single-listing.php' );
		}
		// Return default template if no custom template match is found.
		return plugin_dir_path( __FILE__ ) . 'views/single-listing.php';
	}

	/**
	 * Handle_missing_template
	 * Temporary helper method used to correct any listings with incorrect template file locations.
	 *
	 * @param int    $post_id - Post ID for listing with bad template.
	 * @param string $template_file_path - Path for missing template file.
	 * @return string
	 */
	public function handle_missing_template( $post_id, $template_file_path ) {
		$base_template_name = basename( $template_file_path );
		$current_templates  = $this->get_listing_templates();
		// Loop through current templates to see if a file name matches the missing template.
		foreach ( $current_templates as $key => $value ) {
			if ( basename( $key ) === $base_template_name ) {
				update_post_meta( $post_id, '_wp_post_template', $key );
				return $key;
			}
		}
		return '';
	}

	/**
	 * Get_listing_templates
	 * Returns array with structure: [key = template file path, value = template display name]
	 *
	 * @return array
	 */
	public function get_listing_templates() {
		$listing_templates = [];
		// Gather plugin and theme provided theme files.
		$available_templates = array_merge( $this->get_plugin_templates(), $this->get_theme_templates() );
		foreach ( $available_templates as $full_path ) {
			if ( ! preg_match( '|Single Listing Template:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
				continue;
			}
			$listing_templates[ $full_path ] = _cleanup_header_comment( $header[1] );
		}
		return $listing_templates;
	}

	/**
	 * Get_plugin_templates
	 * Helper function to gather template files found in the plugin
	 *
	 * @return array
	 */
	public function get_plugin_templates() {
		$plugin_files = glob( IMPRESS_IDX_DIR . 'add-ons/listings/includes/listing-templates/single-listing-*.php' );
		if ( is_array( $plugin_files ) ) {
			return $plugin_files;
		}
		return [];
	}

	/**
	 * Get_theme_templates
	 * Helper function to gather template files found in the theme
	 *
	 * @return array
	 */
	public function get_theme_templates() {
		$theme_files = wp_get_theme()->get_files( 'php', 1 );
		if ( is_array( $theme_files ) ) {
			return $theme_files;
		}
		return [];
	}

	/**
	 * Listing_templates_dropdown
	 * Echoes dropdown of listing templates
	 *
	 * @return void
	 */
	public function listing_templates_dropdown() {

		global $post;

		$listing_templates = $this->get_listing_templates();

		/** Loop through templates, make them options */
		foreach ( (array) $listing_templates as $template_file => $template_name ) {
			$selected = ( get_post_meta( $post->ID, '_wp_post_template', true ) === $template_file ) ? ' selected="selected"' : '';
			echo '<option value="' . esc_attr( $template_file ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $template_name ) . '</option>';
		}

	}

	/**
	 * WPListings_Add_Metabox
	 *
	 * @param mixed $post - Post being edited.
	 * @return void
	 */
	public function wplistings_add_metabox( $post ) {
		add_meta_box( 'wplistings_listing_templates', __( 'Listing Template', 'wplistings' ), array( $this, 'listing_template_metabox' ), 'listing', 'side', 'high' );
	}

	/**
	 * Listing_Template_Metabox
	 *
	 * @return void
	 */
	public function listing_template_metabox() {
		?>
		<input type="hidden" name="wplistings_single_noncename" id="wplistings_single_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename( __FILE__ ) ) ); ?>" />
		<label class="hidden" for="listing_template"><?php esc_html_e( 'Listing Template', 'wp-listings' ); ?></label><br />
		<select name="_wp_post_template" id="listing_template" class="dropdown">
			<option value=""><?php esc_html_e( 'Default', 'wp-listings' ); ?></option>
			<?php $this->listing_templates_dropdown(); ?>
		</select><br />
		<?php
	}

	/**
	 * Metabox_Save
	 *
	 * @param integer $post_id - Post ID.
	 * @param WP_Post $post - Post being edited.
	 * @return mixed
	 */
	public function metabox_save( $post_id, $post ) {
		// Verify this came from our screen and with proper authorization, because save_post can be triggered at other times.
		if ( ! isset( $_POST['wplistings_single_noncename'] ) || ! wp_verify_nonce( $_POST['wplistings_single_noncename'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}

		/** Is the user allowed to edit the post or page? */
		if ( 'listing' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post->ID ) ) {
				return $post->ID;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return $post->ID;
			}
		}

		/** OK, we're authenticated: we need to find and save the data */

		/** Put the data into an array to make it easier to loop though and save */
		$mydata['_wp_post_template'] = $_POST['_wp_post_template'];

		/** Add values of $mydata as custom fields */
		foreach ( $mydata as $key => $value ) {
			/** Don't store custom data twice */
			if ( 'revision' === $post->post_type ) {
				return;
			}

			/** If $value is an array, make it a CSV (unlikely) */
			$value = implode( ',', (array) $value );

			/** Update the data if it exists, or add it if it doesn't */
			if ( get_post_meta( $post->ID, $key, false ) ) {
				update_post_meta( $post->ID, $key, $value );
			} else {
				add_post_meta( $post->ID, $key, $value );
			}

			/** Delete if blank */
			if ( ! $value ) {
				delete_post_meta( $post->ID, $key );
			}
		}

	}

}

add_action( 'wp_ajax_listing_inquiry_request', 'listing_inquiry_request' );
add_action( 'wp_ajax_nopriv_listing_inquiry_request', 'listing_inquiry_request' );

/**
 * Listing Inquiry Request
 * Listing inquiry form handling.
 *
 * @return mixed
 */
function listing_inquiry_request() {

	// Exit early if nonce/formdata is missing.
	if ( ! isset( $_POST['nonce'], $_POST['formdata'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_listing_inquiry_nonce' ) ) {
		wp_send_json( 'Nonce or Formdata Check Failed' );
	}

	$form_data = [];
	parse_str( $_POST['formdata'], $form_data );

	$form_data['inquiryFirstname'] = sanitize_text_field( $form_data['inquiryFirstname'] );
	$form_data['inquiryLastname']  = sanitize_text_field( $form_data['inquiryLastname'] );
	$form_data['inquiryEmail']     = sanitize_email( $form_data['inquiryEmail'] );
	$form_data['inquiryPhone']     = sanitize_text_field( $form_data['inquiryPhone'] );
	$form_data['inquiryComment']   = sanitize_text_field( $form_data['inquiryComment'] );
	$form_data['inquiryPostID']    = filter_var( $form_data['inquiryPostID'], FILTER_SANITIZE_NUMBER_INT );

	// Exit early if no post ID provided.
	if ( empty( $form_data['inquiryPostID'] ) ) {
		wp_send_json( 'No ID Provided' );
	}

	$idx_api = new \IDX\Idx_Api();
	// Using 'interval=168' to grab any leads that recently signed up.
	$leads = $idx_api->idx_api( 'lead?interval=168', '1.7.0', 'leads', [], 20 );
	$leads = empty( $leads['data'] ) ? [] : $leads['data'];

	$lead_id;

	$post = get_post( $form_data['inquiryPostID'] );

	foreach ( $leads as $lead ) {
		if ( strcasecmp( $lead->email, $form_data['inquiryEmail'] ) == 0 ) {
			$lead_id = $lead->id;
		}
	}

	// If no match is found, try again with full list of leads.
	if ( empty( $lead_id ) ) {
		$leads = $idx_api->get_leads();
		foreach ( $leads as $lead ) {
			if ( strcasecmp( $lead->email, $form_data['inquiryEmail'] ) == 0 ) {
				$lead_id = $lead->id;
			}
		}
	}

	// If no lead is matched.
	if ( empty( $lead_id ) ) {
		wp_send_json( 'No Lead Match' );
	}

	// Email notification.
	$email_to = get_the_author_meta( 'user_email', $post->post_author );
	if ( ! isset( $email_to ) || ( $email_to == '' ) ) {
		$email_to = get_option( 'admin_email' );
	}

	$subject = 'Listing Inquiry from ' . $form_data['inquiryFirstname'] . ' ' . $form_data['inquiryLastname'];
	$body    = 'Name: ' . $form_data['inquiryFirstname'] . ' ' . $form_data['inquiryLastname'] . "\n\n" . 'Email: ' . $form_data['inquiryEmail'] . "\n\n" . 'Phone: ' . $form_data['inquiryPhone'] . "\n\n" . 'Listing: ' . get_the_title( $post ) . "\n\n" . 'URL: ' . get_permalink( $post ) . "\n\n" . 'Comments: ' . $form_data['inquiryComment'];
	$headers = 'From: ' . $form_data['inquiryFirstname'] . ' ' . $form_data['inquiryLastname'] . ' <' . $email_to . '>' . "\r\n" . 'Reply-To: ' . $form_data['inquiryEmail'];

	wp_mail( $email_to, $subject, $body, $headers );

	// Add note to lead.
	$note = [
		'note' => ( ! empty( $form_data['inquiryComment'] ) ) ? 'I\'m interested in this listing: <a href="' . get_permalink( $post ) . '">' . get_the_title( $post ) . '</a>' . "\n\n" . 'Comments: ' . $form_data['inquiryComment'] : 'I\'m interested in this listing: <a href="' . get_permalink( $post ) . '">' . get_the_title( $post ) . '</a>'
	];

	$args = [
		'method'    => 'PUT',
		'headers'   => [
			'content-type' => 'application/x-www-form-urlencoded',
			'accesskey'    => get_option( 'idx_broker_apikey' ),
			'outputtype'   => 'json',
		],
		'sslverify' => false,
		'body'      => http_build_query( $note ),
	];

	$api_url  = IDX_API_URL . '/leads/note/' . $lead_id;
	$response = wp_remote_request( $api_url, $args );

	wp_send_json( 'Success' );
}
