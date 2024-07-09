<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' ) ) {
	exit;}
/**
 * Class for our Lead POST action type.
 *
 * @package     Ninja Forms
 * @subpackage  Classes/Actions
*/

/**
 * Class NF_IDXLeads
 */
final class NF_IDXLeads {

	const VERSION = '0.0.1';
	const SLUG    = 'idx-leads';
	const NAME    = 'IDX Leads';
	const AUTHOR  = '';
	const PREFIX  = 'NF_IDXLeads';

	/**
	 * @var NF_IDXLeads
	 * @since 3.0
	 */
	private static $instance;

	/**
	 * Plugin Directory
	 *
	 * @since 3.0
	 * @var string $dir
	 */
	public static $dir = '';

	/**
	 * Plugin URL
	 *
	 * @since 3.0
	 * @var string $url
	 */
	public static $url = '';

	/**
	 * Main Plugin Instance
	 *
	 * Insures that only one instance of a plugin class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 3.0
	 * @static
	 * @static var array $instance
	 * @return NF_IDXLeads Highlander Instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof NF_IDXLeads ) ) {
			self::$instance = new NF_IDXLeads();

			self::$dir = plugin_dir_path( __FILE__ );

			self::$url = plugin_dir_url( __FILE__ );

		}

		return self::$instance;
	}

	public function __construct() {
		/*
		 * Required for all Extensions.
		 */
		add_action( 'admin_init', array( $this, 'setup_license' ) );

		/*
		 * Optional. If your extension processes or alters form submission data on a per form basis...
		 */
		add_filter( 'ninja_forms_register_actions', array( $this, 'register_actions' ) );

	}

	/**
	 * Optional. If your extension processes or alters form submission data on a per form basis...
	 */
	public function register_actions( $actions ) {
		$actions['idx-leads'] = new NF_Lead_Action();

		return $actions;
	}

	/*
	 * Required methods for all extension.
	 */

	public function setup_license() {
		if ( ! class_exists( 'NF_Extension_Updater' ) ) {
			return;
		}

		new NF_Extension_Updater( self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG );
	}
}

/**
 * The main function responsible for returning The Highlander Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 3.0
 * @return {class} Highlander Instance
 */
function NF_IDXLeads() {
	return NF_IDXLeads::instance();
}

NF_IDXLeads();

final class NF_Lead_Action extends NF_Abstracts_Action {

	/**
	 * @var string
	 */
	protected $_name = 'idx-leads';

	/**
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * @var string
	 */
	protected $_timing = 'normal';

	/**
	 * @var int
	 */
	protected $_priority = 10;

	/**
	 * Get things rolling
	 */
	function __construct() {

		parent::__construct();

		$this->_nicename = __( 'IDX Lead Push', 'ninja-forms' );

		$settings = array(
			'instruction' => array(
				'name'  => 'instruction',
				'type'  => 'textarea',
				'group' => 'primary',
				'label' => __( 'Instructions', 'ninja-forms' ),
				'value' => 'Important! Your form must contain a First Name, Last Name, and Email field, all required, in order for the lead to be added to IDX Middleware.',
			),
			'category'    => array(
				'name'    => 'category',
				'type'    => 'select',
				'options' => array(
					array(
						'label' => __( '---', 'ninja-forms' ),
						'value' => '',
					),
					array(
						'label' => __( 'Buyer', 'ninja-forms' ),
						'value' => 'Buyer',
					),
					array(
						'label' => __( 'Contact', 'ninja-forms' ),
						'value' => 'Contact',
					),
					array(
						'label' => __( 'Direct Signup', 'ninja-forms' ),
						'value' => 'Direct Signup',
					),
					array(
						'label' => __( 'Home Valuation', 'ninja-forms' ),
						'value' => 'Home Valuation',
					),
					array(
						'label' => __( 'More Info', 'ninja-forms' ),
						'value' => 'More Info',
					),
					array(
						'label' => __( 'Property Updates', 'ninja-forms' ),
						'value' => 'Property Updates',
					),
					array(
						'label' => __( 'Scheduled Showing', 'ninja-forms' ),
						'value' => 'Scheduled Showing',
					),
					array(
						'label' => __( 'Seller', 'ninja-forms' ),
						'value' => 'Seller',
					),
					array(
						'label' => __( 'Unknown', 'ninja-forms' ),
						'value' => 'Unknown',
					),
				),
				'group'   => 'primary',
				'label'   => __( 'Category (optional)', 'ninja-forms' ),
				'value'   => '',
			),
		);

		$this->_settings = array_merge( $this->_settings, $settings );

	}

	public function save( $action_settings ) {

	}

	/**
	 * Process our API POST action
	 *
	 * @access public
	 * @return void
	 */
	public function process( $action_settings, $form_id, $data ) {

		/*
		Carry out our processing using the setting here
		*/
		$fields = $data['fields'];

		foreach ( $data['fields'] as $field ) {
			if ( $field['type'] == 'firstname' ) {
				$firstname = $field['value'];}
			if ( $field['type'] == 'lastname' ) {
				$lastname = $field['value'];}
			if ( $field['type'] == 'email' ) {
				$email = $field['value'];}
			if ( $field['type'] == 'phone' ) {
				$phone = $field['value'];}
			if ( $field['type'] == 'address' ) {
				$address = $field['value'];}
			if ( $field['type'] == 'city' ) {
				$city = $field['value'];}
			if ( $field['type'] == 'liststate' ) {
				$state = $field['value'];}
			if ( $field['type'] == 'zip' ) {
				$zip = $field['value'];}
			if ( $field['type'] == 'listcountry' ) {
				$country = $field['value'];}
		}

		if ( ! isset( $firstname ) || ! isset( $lastname ) || ! isset( $email ) ) {
			return;
		}

		$lead_data = array(
			'firstName'      => $firstname,
			'lastName'       => $lastname,
			'email'          => $email,
			'phone'          => ( isset( $phone ) ) ? $phone : '',
			'address'        => ( isset( $address ) ) ? $address : '',
			'city'           => ( isset( $city ) ) ? $city : '',
			'stateProvince'  => ( isset( $state ) ) ? $state : '',
			'zipCode'        => ( isset( $zip ) ) ? $zip : '',
			'country'        => ( isset( $country ) ) ? $country : '',
			'actualCategory' => ( isset( $action_settings['category'] ) ) ? $action_settings['category'] : '',
		);

		$api_url  = IDX_API_URL . '/leads/lead';
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
				'note' => self::output_form_fields( $fields ),
			);

			// Add note if lead already exists
			if ( is_string( $decoded_response ) && str_contains($decoded_response, 'Lead already exists') ) {
				$args = array_replace(
					$args,
					array(
						'method' => 'GET',
						'body'   => null,
					)
				);

				// Get leads
				if ( false === ( $all_leads = get_transient( 'idx_leads_cache' ) ) ) {
					$response  = wp_remote_request( $api_url, $args );
					if ( is_wp_error( $response ) ) {
						return;
					}
					$all_leads = json_decode( $response['body'], 1 )['data'];
					set_transient( 'idx_leads', $all_leads, 60 * 60 * 1 );
				}

				// Loop through leads to match email address
				foreach ( $all_leads as $leads => $lead ) {
					if ( $lead['email'] == $email ) {
						$api_url  = IDX_API_URL . '/leads/note/' . $lead['id'];
						$args     = array_replace(
							$args,
							array(
								'method' => 'PUT',
								'body'   => http_build_query( $note ),
							)
						);
						wp_remote_request( $api_url, $args );
						break;
					}
				}
			} else {
				// Add note for new lead
				$lead_id  = $decoded_response->newID;
				$api_url  = IDX_API_URL . '/leads/note/' . $lead_id;
				$args     = array_replace( $args, array( 'body' => http_build_query( $note ) ) );
				wp_remote_request( $api_url, $args );
			}
		}
	}

	private static function output_form_fields( $fields ) {
		$output = '';
		foreach ( $fields as $field ) {
			if ( ! empty( $field['value'] ) ) {
				$output .= $field['label'] . ":\r\n" . $field['value'] . "\r\n\r\n";
			}
		}

		return $output;
	}
}
