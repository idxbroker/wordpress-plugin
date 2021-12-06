<?php
namespace IDX\Views;

use \Carbon\Carbon;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Lead_Management class.
 */
class Lead_Management {

	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Lead_Management ) ) {
			self::$instance = new Lead_Management();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		add_action( 'plugins_loaded', array( $this, 'add_lead_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'idx_lead_scripts' ) );
		add_action( 'init', array( $this, 'idx_ajax_actions' ) );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	public function add_lead_pages() {

		// Add Leads menu page.
		$this->page = add_menu_page(
			'Leads',
			'Leads',
			'manage_options',
			'leads',
			array(
				$this,
				'idx_leads_list',
			),
			'dashicons-businessman',
			'30'
		);

		/* Add callbacks for this screen only */
		add_action( 'load-' . $this->page, array( $this, 'lead_list_actions' ), 9 );

		// Add Leads as submenu page also.
		$this->page = add_submenu_page(
			'leads',
			'Leads',
			'Leads',
			'manage_options',
			'leads',
			array(
				$this,
				'idx_leads_list',
			)
		);

		/* Add callbacks for this screen only */
		add_action( 'load-' . $this->page, array( $this, 'lead_list_actions' ), 9 );

		// Add Add Lead submenu page.
		$this->page = add_submenu_page(
			'leads',
			'Add/Edit Lead',
			'Add Lead',
			'manage_options',
			'edit-lead',
			array(
				$this,
				'idx_leads_edit',
			)
		);

		/* Add callbacks for this screen only */
		add_action( 'load-' . $this->page, array( $this, 'lead_edit_actions' ), 9 );

	}

	public function idx_ajax_actions() {
		add_action( 'wp_ajax_idx_lead_add', array( $this, 'idx_lead_add' ) );
		add_action( 'wp_ajax_idx_lead_edit', array( $this, 'idx_lead_edit' ) );
		add_action( 'wp_ajax_idx_lead_note_add', array( $this, 'idx_lead_note_add' ) );
		add_action( 'wp_ajax_idx_lead_note_edit', array( $this, 'idx_lead_note_edit' ) );
		add_action( 'wp_ajax_idx_lead_property_add', array( $this, 'idx_lead_property_add' ) );
		add_action( 'wp_ajax_idx_lead_property_edit', array( $this, 'idx_lead_property_edit' ) );
		add_action( 'wp_ajax_idx_lead_delete', array( $this, 'idx_lead_delete' ) );
		add_action( 'wp_ajax_idx_lead_note_delete', array( $this, 'idx_lead_note_delete' ) );
		add_action( 'wp_ajax_idx_lead_property_delete', array( $this, 'idx_lead_property_delete' ) );
		add_action( 'wp_ajax_idx_lead_search_delete', array( $this, 'idx_lead_search_delete' ) );

		add_action( 'wp_ajax_get_idx_leads_data', [ $this, 'get_idx_leads_data' ] );
	}

	public function idx_lead_scripts() {

		// Only load on leads pages.
		$screen_id = get_current_screen();
		if ( 'leads_page_edit-lead' === $screen_id->id || 'toplevel_page_leads' === $screen_id->id ) {

			wp_enqueue_script( 'idx_lead_ajax_script', IMPRESS_IDX_URL . 'assets/js/idx-leads.js', [ 'jquery' ], '1.0.0', false );
			wp_localize_script(
				'idx_lead_ajax_script',
				'IDXLeadAjax',
				array(
					'ajaxurl'    => admin_url( 'admin-ajax.php' ),
					'leadurl'    => admin_url( 'admin.php?page=edit-lead&leadID=' ),
					'detailsurl' => $this->idx_api->details_url(),
				)
			);
			wp_enqueue_script( 'dialog-polyfill' );
			wp_enqueue_script( 'idx-material-js' );
			wp_enqueue_script( 'jquery-datatables' );
			wp_enqueue_script( 'select2' );

			wp_enqueue_style( 'idx-admin' );
			wp_enqueue_style( 'idx-material-font' );
			wp_enqueue_style( 'idx-material-icons' );
			wp_enqueue_style( 'idx-material-style' );
			wp_enqueue_style( 'idx-material-datatable' );
		}
	}

	/**
	 * Add a lead via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_add() {

		$permission = check_ajax_referer( 'idx_lead_add_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['fields'] ) ) {
			echo 'error';
		} else {
			// Add lead via API.
			$api_url  = IDX_API_URL . '/leads/lead';
			$args     = array(
				'method'    => 'PUT',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => sanitize_text_field( wp_unslash( $_POST['fields'] ) ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( 'Lead already exists.' === $decoded_response ) {
				echo 'Lead already exists.';
			} elseif ( wp_remote_retrieve_response_code( $response ) == '200' ) {
				// Delete lead cache so new lead will show in list views immediately.
				delete_option( 'idx_leads_lead_cache' );
				// return new lead ID to script.
				echo esc_html( $decoded_response['newID'] );
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Edit a lead via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_edit() {

		$permission = check_ajax_referer( 'idx_lead_edit_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['fields'] ) || ! isset( $_POST['leadID'] ) ) {
			echo 'error';
		} else {

			$lead_id = sanitize_text_field( wp_unslash( $_POST['leadID'] ) );

			// Edit lead via API.
			$api_url  = IDX_API_URL . '/leads/lead/' . $lead_id;
			$args     = array(
				'method'    => 'POST',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => sanitize_text_field( wp_unslash( $_POST['fields'] ) ),
			);
			$response = wp_remote_request( $api_url, $args );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_lead/' . $lead_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Post a lead note via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_note_add() {

		$permission = check_ajax_referer( 'idx_lead_note_add_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['note'] ) || ! isset( $_POST['id'] ) ) {
			echo 'error';
		} else {

			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );

			// Add lead note via API.
			$api_url  = IDX_API_URL . '/leads/note/' . $post_id;
			$args     = array(
				'method'    => 'PUT',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => sanitize_text_field( wp_unslash( $_POST['note'] ) ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
				delete_option( 'idx_leads_note/' . $post_id . '_cache' );
				echo esc_html( $decoded_response['newID'] );
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Update a lead note via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_note_edit() {

		$permission = check_ajax_referer( 'idx_lead_note_edit_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['note'], $_POST['id'], $_POST['noteid'] ) ) {
			echo 'not set';
		} else {
			$note_id = sanitize_text_field( wp_unslash( $_POST['noteid'] ) );
			$id      = sanitize_text_field( wp_unslash( $_POST['id'] ) );

			// Update lead note via API.
			$api_url  = IDX_API_URL . '/leads/note/' . $id . '/' . $note_id;
			$args     = array(
				'method'    => 'POST',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => sanitize_text_field( wp_unslash( $_POST['note'] ) ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_note/' . $id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Post a lead property via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_property_add() {

		$permission = check_ajax_referer( 'idx_lead_property_add_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'] ) ) {
			echo 'error';
		} else {

			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );

			$property_array = array(
				'propertyName'   => sanitize_text_field( wp_unslash( $_POST['property_name'] ) ),
				'receiveUpdates' => sanitize_text_field( wp_unslash( $_POST['updates'] ) ),
				'property'       => array(
					'idxID'     => sanitize_text_field( wp_unslash( $_POST['idxid'] ) ),
					'listingID' => sanitize_text_field( wp_unslash( $_POST['listingid'] ) ),
				),
			);

			// Add lead property via API.
			$api_url  = IDX_API_URL . '/leads/property/' . $post_id;
			$args     = array(
				'method'    => 'PUT',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => http_build_query( $property_array ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
				delete_option( 'idx_leads_property/' . $post_id . '_cache' );
				echo esc_html( $decoded_response['newID'] );
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Edit a lead property via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_property_edit() {

		$permission = check_ajax_referer( 'idx_lead_property_edit_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'], $_POST['spid'] ) ) {
			echo 'error';
		} else {

			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			$spid    = sanitize_text_field( wp_unslash( $_POST['spid'] ) );

			$property_array = array(
				'propertyName'   => sanitize_text_field( wp_unslash( $_POST['property_name'] ) ),
				'receiveUpdates' => sanitize_text_field( wp_unslash( $_POST['updates'] ) ),
				'property'       => array(
					'idxID'     => sanitize_text_field( wp_unslash( $_POST['idxid'] ) ),
					'listingID' => sanitize_text_field( wp_unslash( $_POST['listingid'] ) ),
				),
			);

			// Add lead property via API.
			$api_url  = IDX_API_URL . '/leads/property/' . $post_id . '/' . $spid;
			$args     = array(
				'method'    => 'POST',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => http_build_query( $property_array ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_property/' . $post_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Delete a lead via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_delete() {

		$permission = check_ajax_referer( 'idx_lead_delete_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'] ) ) {
			echo 'error';
		} else {

			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			// Delete lead via API.
			$api_url  = IDX_API_URL . '/leads/lead/' . $post_id;
			$args     = array(
				'method'    => 'DELETE',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => null,
			);
			$response = wp_remote_request( $api_url, $args );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_lead_cache' );
				delete_option( 'idx_leads_lead/' . $post_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Delete a lead note via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_note_delete() {

		$permission = check_ajax_referer( 'idx_lead_note_delete_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'], $_POST['noteid'] ) ) {
			echo 'error';
		} else {

			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			$note_id = sanitize_text_field( wp_unslash( $_POST['noteid'] ) );
			// Delete lead note via API.
			$api_url  = IDX_API_URL . '/leads/note/' . $post_id . '/' . $note_id;
			$args     = array(
				'method'    => 'DELETE',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => null,
			);
			$response = wp_remote_request( $api_url, $args );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_note/' . $post_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Delete a lead saved property via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_property_delete() {

		$permission = check_ajax_referer( 'idx_lead_property_delete_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'] ) || ! isset( $_POST['spid'] ) ) {
			echo 'error';
		} else {
			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			$spid    = sanitize_text_field( wp_unslash( $_POST['spid'] ) );
			// Delete lead saved property via API.
			$api_url  = IDX_API_URL . '/leads/property/' . $post_id . '/' . $spid;
			$args     = array(
				'method'    => 'DELETE',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => null,
			);
			$response = wp_remote_request( $api_url, $args );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_property/' . $post_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Delete a lead saved search via API
	 * echoes response to /assets/js/idx-leads.js
	 *
	 * @return void
	 */
	public function idx_lead_search_delete() {

		$permission = check_ajax_referer( 'idx_lead_search_delete_nonce', 'nonce', false );
		if ( ! $permission || ! isset( $_POST['id'], $_POST['ssid'] ) ) {
			echo 'error';
		} else {
			$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			$spid    = sanitize_text_field( wp_unslash( $_POST['spid'] ) );
			// Delete lead saved search via API.
			$api_url  = IDX_API_URL . '/leads/search/' . $post_id . '/' . $spid;
			$args     = array(
				'method'    => 'DELETE',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => null,
			);
			$response = wp_remote_request( $api_url, $args );

			if ( wp_remote_retrieve_response_code( $response ) == '204' ) {
				delete_option( 'idx_leads_search/' . $post_id . '_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Output leads table
	 *
	 * @return void
	 */
	public function idx_leads_list() {
		// Check that the user is logged in & has proper permissions.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<h3>Leads</h3>';
		echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp leads">';
		echo '
			<a href="#" title="Delete Lead" class="delete-selected hide"><i class="material-icons md-18">delete</i> Delete Selected</a>
			<thead>
				<th class="mdl-data-table__cell--non-numeric">Lead Name</th>
				<th class="mdl-data-table__cell--non-numeric">Email</th>
				<th class="mdl-data-table__cell--non-numeric">Phone</th>
				<th class="mdl-data-table__cell--non-numeric">Subscribed</th>
				<th class="mdl-data-table__cell--non-numeric">Last Active</th>
				<th class="mdl-data-table__cell--non-numeric">Agent</th>
				<th class="mdl-data-table__cell--non-numeric">Actions</th>
			</thead>
			<tbody>
			';
		echo '</tbody></table>';
		echo '<dialog id="dialog-lead-delete">
				<form method="dialog">
					<h5>Delete Lead</h5>
					<p>Are you sure you want to delete this lead?</p>
					<button type="submit" value="no" autofocus>No</button>
					<button type="submit" value="yes">Yes</button>
				</form>
			</dialog>';
		echo '
			<a href="' . esc_url( admin_url( 'admin.php?page=edit-lead' ) ) . '" id="add-lead" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp">
				<i class="material-icons">add</i>
				<div class="mdl-tooltip" data-mdl-for="add-lead">Add New Lead</div>
			</a>
			<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
			';
	}

	public function get_idx_leads_data() {

		$leads_array = $this->idx_api->get_leads();

		$leads_array = array_reverse( $leads_array );

		$agents_array = $this->idx_api->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		$leads = [];

		$offset = get_option( 'gmt_offset', 0 );

		// prepare leads for display.
		foreach ( $leads_array as $lead ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$last_active = Carbon::parse( ( '0000-00-00 00:00:00' === $lead->lastActivityDate ) ? $lead->subscribeDate : $lead->lastActivityDate )->addHours( $offset )->toDayDateTimeString();
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$subscribed_on = Carbon::parse( $lead->subscribeDate )->addHours( $offset )->toDayDateTimeString();
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( '0' != $lead->agentOwner && isset( $agents_array['agent'] ) ) {
				foreach ( $agents_array['agent'] as $agent ) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( $lead->agentOwner == $agent['agentID'] ) {
						$agent_name = $agent['agentDisplayName'];
					}
				}
				if ( ! isset( $agent_name ) ) {
					$agent_name = 'None assigned';
				}
			} else {
				$agent_name = 'None assigned';
			}

			$avatar_args = array(
				'default'       => '404',
				'force_display' => true,
			);

			$edit_lead_nonce = wp_create_nonce( 'idx_lead_edit_nonce' );
			$nonce           = wp_create_nonce( 'idx_lead_delete_nonce' );

			// Column structure of data being passed to jQuery Datatables:
			// Column 0 = Gravitar, First Name + Last Name.
			// Column 1 = Email address.
			// Column 2 = Phone number.
			// Column 3 = Subscribed.
			// Column 4 = Last active.
			// Column 5 = Agent.
			// Column 6 = Actions (edit, delete, MW link).

			$current_lead = [
				0 => '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( admin_url( 'admin.php?page=edit-lead&leadID=' . $lead->id ) ) . '">' . get_avatar( $lead->email, 32, '', 'Lead photo', $avatar_args ) . ' ' . esc_html( $lead->firstName ) . ' ' . esc_html( $lead->lastName ) . '</a></td>',
				1 => '<td class="mdl-data-table__cell--non-numeric"><a id="mail-lead-' . esc_attr( $lead->id ) . '" href="mailto:' . $lead->email . '" target="_blank">' . $lead->email . '</a><div class="mdl-tooltip" data-mdl-for="mail-lead-' . $lead->id . '">Email Lead</div></td>',
				2 => '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $lead->phone ) . '</td>',
				3 => '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $subscribed_on ) . '</td>',
				4 => '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $last_active ) . '</td>',
				5 => '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $agent_name ) . '</td>',
				6 => '<td class="mdl-data-table__cell--non-numeric">
						<a href="' . esc_url( admin_url( 'admin.php?page=edit-lead&leadID=' . $lead->id . '&nonce=' . $edit_lead_nonce ) ) . '" id="edit-lead-' . esc_attr( $lead->id ) . '" data-id="' . esc_attr( $lead->id ) . '" data-nonce="' . esc_attr( $edit_lead_nonce ) . '">
							<i class="material-icons md-18">create</i>
							<div class="mdl-tooltip" data-mdl-for="edit-lead-' . esc_attr( $lead->id ) . '">Edit Lead</div>
						</a>
						<a href="' . esc_url( admin_url( 'admin-ajax.php?action=idx_lead_delete&id=' . $lead->id . '&nonce=' . $nonce ) ) . '" id="delete-lead-' . esc_attr( $lead->id ) . '" class="delete-lead" data-id="' . esc_attr( $lead->id ) . '" data-nonce="' . esc_attr( $nonce ) . '">
							<i class="material-icons md-18">delete</i>
							<div class="mdl-tooltip" data-mdl-for="delete-lead-' . esc_attr( $lead->id ) . '">Delete Lead</div>
						</a>
						<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . esc_attr( $lead->id ) . '" id="edit-mw-' . esc_attr( $lead->id ) . '" target="_blank">
							<i class="material-icons md-18">exit_to_app</i>
							<div class="mdl-tooltip" data-mdl-for="edit-mw-' . esc_attr( $lead->id ) . '">Edit Lead in Middleware</div>
						</a>
					 </td>',
			];

			array_push( $leads, $current_lead );

		}

		echo wp_json_encode( [ 'data' => $leads ] );
		wp_die();
	}

	/**
	 * Actions to be taken prior to page loading. This is after headers have been set.
	 * call on load-$hook.
	 */
	public function lead_list_actions() {

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array(
			'post_type' => null,
			'ID'        => null,
		);

	}


	/**
	 * Output edit lead page
	 *
	 * @return void
	 */
	public function idx_leads_edit() {
		add_thickbox();
		// Check that the user is logged in & has proper permissions.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		} elseif ( empty( $_GET['leadID'] ) ) { ?>

			<h3>Add Lead</h3>

			<form id="add-lead" action="" method="post">
				<h6>Account Information</h6>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
					<input class="mdl-textfield__input" type="text" id="firstName" name="firstName">
					<label class="mdl-textfield__label" for="firstName">First Name <span class="is-required">(required)</span></label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
					<input class="mdl-textfield__input" type="text" id="lastName" name="lastName">
					<label class="mdl-textfield__label" for="lastName">Last Name <span class="is-required">(required)</span></label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="phone" name="phone">
					<label class="mdl-textfield__label" for="phone">Phone</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
					<input class="mdl-textfield__input" type="text" id="email" name="email">
					<label class="mdl-textfield__label" for="email">Email <span class="is-required">(required)</span></label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="email2" name="email2">
					<label class="mdl-textfield__label" for="email2">Additional Email</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="address" name="address">
					<label class="mdl-textfield__label" for="address">Street Address</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="city" name="city">
					<label class="mdl-textfield__label" for="city">City</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="stateProvince" name="stateProvince">
					<label class="mdl-textfield__label" for="stateProvince">State/Province</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="zipCode" name="zipCode">
					<label class="mdl-textfield__label" for="zipCode">Zip Code</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="country" name="country">
					<label class="mdl-textfield__label" for="country">Country</label>
				</div>

				<h6>Account Settings</h6>
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="emailFormat">Email Format</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="emailFormat" name="emailFormat">
							<option value="html">HTML</option>
							<option value="text">Plain Text</option>
						</select>
					</div>
				</div>
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="agentOwner">Assigned Agent</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="agentOwner" name="agentOwner">
							<?php self::agents_select_list(); ?>
						</select>
					</div>
				</div>

				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="actualCategory">Category</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="actualCategory" name="actualCategory">
							<option value="">---</option>
							<option value="Buyer">Buyer</option>
							<option value="Contact">Contact</option>
							<option value="Direct Signup">Direct Signup</option>
							<option value="Home Valuation">Home Valuation</option>
							<option value="More Info">More Info</option>
							<option value="Property Updates">Property Updates</option>
							<option value="Scheduled Showing">Scheduled Showing</option>
							<option value="Seller">Seller</option>
							<option value="Unknown">Unknown</option>
						</select>
					</div>
				</div><br />

				<div class="mdl-fieldgroup">
					<div class="mdl-radiofield">
						<span class="mdl-label">Account Disabled</span>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-y">
							<input type="radio" id="disabled-y" class="mdl-radio__button" name="disabled" value="y">
							<span class="mdl-radio__label">Yes</span>
						</label>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-n">
							<input type="radio" id="disabled-n" class="mdl-radio__button" name="disabled" value="n" checked>
							<span class="mdl-radio__label">No</span>
						</label>
					</div>
				</div>

				<div class="mdl-fieldgroup">
					<div class="mdl-radiofield">
						<span class="mdl-label">Lead Can Login</span>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-y">
							<input type="radio" id="canlogin-y" class="mdl-radio__button" name="canLogin" value="y" checked>
							<span class="mdl-radio__label">Yes</span>
						</label>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-n">
							<input type="radio" id="canlogin-n" class="mdl-radio__button" name="canLogin" value="n">
							<span class="mdl-radio__label">No</span>
						</label>
					</div>
				</div>

				<div class="mdl-fieldgroup">
					<div class="mdl-radiofield">
						<span class="mdl-label">Receive Property Updates</span>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-y">
							<input type="radio" id="updates-y" class="mdl-radio__button" name="receiveUpdates" value="y" checked>
							<span class="mdl-radio__label">Yes</span>
						</label>
						<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-n">
							<input type="radio" id="updates-n" class="mdl-radio__button" name="receiveUpdates" value="n">
							<span class="mdl-radio__label">No</span>
						</label>
					</div>
				</div>
				<br />

				<input type="hidden" name="action" value="idx_lead_add" />
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-lead" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_add_nonce' ) ); ?>" type="submit">Save Lead</button>
				<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
				<div class="error-existing" style="display: none;">Lead already exists.</div>
				<div class="error-fail" style="display: none;">Lead addition failed. Check all required fields or try again later.</div>
				<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>

			</form>
			<?php

		} else {

			if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'idx_lead_edit_nonce' ) ) {
				echo '<p>Nonce verification failed</p>';
				return;
			}

			$lead_id = (int) sanitize_text_field( wp_unslash( $_GET['leadID'] ) );
			if ( empty( $lead_id ) ) {
				return;
			}

			// Get Lead info.
			$lead = $this->idx_api->idx_api( 'lead/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
			// Return early on error, if not an array, or first key is invalid lead ID message.
			if ( is_wp_error( $lead ) || ! is_array( $lead ) || ( ! empty( $lead[0] ) && ' Required parameter missing or invalid.' === $lead[0] ) ) {
				return;
			}
			?>
			<h3>Edit Lead &raquo; <?php echo esc_html( $lead['firstName'] ?? '' ); ?> <?php echo esc_html( $lead['lastName'] ) ?? ''; ?></h3>

			<div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
				<div class="mdl-tabs__tab-bar">
					<a href="#lead-info" class="mdl-tabs__tab is-active">Lead Info</a>
					<a href="#lead-notes" class="mdl-tabs__tab">Notes</a>
					<a href="#lead-properties" class="mdl-tabs__tab">Saved Properties</a>
					<a href="#lead-searches" class="mdl-tabs__tab">Saved Searches</a>
					<a href="#lead-traffic" class="mdl-tabs__tab">Traffic History</a>
				</div>
				<div class="mdl-tabs__panel is-active" id="lead-info">
					<div class="lead-photo">
						<?php // $avatar_args = array( 'force_display' => true, 'default' => '404' ); echo get_avatar($lead['email'], 256, '', 'Lead photo', $avatar_args); ?>
					</div>
					<form id="edit-lead" action="" method="post">
						<h6>Account Information</h6>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="firstName" name="firstName" value="<?php echo esc_attr( $lead['firstName'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="firstName">First Name <span class="is-required">(required)</span></label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="lastName" name="lastName" value="<?php echo esc_attr( $lead['lastName'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="lastName">Last Name <span class="is-required">(required)</span></label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="phone" name="phone" value="<?php echo esc_attr( $lead['phone'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="phone">Phone</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="email" name="email" value="<?php echo esc_attr( $lead['email'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="email">Email <span class="is-required">(required)</span></label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="email2" name="email2" value="<?php echo esc_attr( $lead['email2'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="email2">Additional Email</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="address" name="address" value="<?php echo esc_attr( $lead['address'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="address">Street Address</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="city" name="city" value="<?php echo esc_attr( $lead['city'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="city">City</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="stateProvince" name="stateProvince" value="<?php echo esc_attr( $lead['stateProvince'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="stateProvince">State/Province</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="zipCode" name="zipCode" value="<?php echo esc_attr( $lead['zipCode'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="zipCode">Zip Code</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="country" name="country" value="<?php echo esc_attr( $lead['country'] ?? '' ); ?>">
							<label class="mdl-textfield__label" for="country">Country</label>
						</div>

						<h6>Account Settings</h6>
						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="emailFormat">Email Format</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="emailFormat" name="emailFormat">
									<option value="html" <?php selected( $lead['emailFormat'], 'html' ); ?>>HTML</option>
									<option value="text" <?php selected( $lead['emailFormat'], 'text' ); ?>>Plain Text</option>
								</select>
							</div>
						</div>
						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="agentOwner">Assigned Agent</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="agentOwner" name="agentOwner">
									<?php self::agents_select_list( $lead['agentOwner'] ); ?>
								</select>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="actualCategory">Category</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="actualCategory" name="actualCategory">
									<option value="" <?php selected( $lead['actualCategory'], '' ); ?>>---</option>
									<option value="Buyer" <?php selected( $lead['actualCategory'], 'Buyer' ); ?>>Buyer</option>
									<option value="Contact" <?php selected( $lead['actualCategory'], 'Contact' ); ?>>Contact</option>
									<option value="Direct Signup" <?php selected( $lead['actualCategory'], 'Direct Signup' ); ?>>Direct Signup</option>
									<option value="Home Valuation" <?php selected( $lead['actualCategory'], 'Home Valuation' ); ?>>Home Valuation</option>
									<option value="More Info" <?php selected( $lead['actualCategory'], 'More Info' ); ?>>More Info</option>
									<option value="Property Updates" <?php selected( $lead['actualCategory'], 'Property Updates' ); ?>>Property Updates</option>
									<option value="Scheduled Showing" <?php selected( $lead['actualCategory'], 'Scheduled Showing' ); ?>>Scheduled Showing</option>
									<option value="Seller" <?php selected( $lead['actualCategory'], 'Seller' ); ?>>Seller</option>
									<option value="Unknown" <?php selected( $lead['actualCategory'], 'Unknown' ); ?>>Unknown</option>
								</select>
							</div>
						</div><br />

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Account Disabled</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-y">
									<input type="radio" id="disabled-y" class="mdl-radio__button" name="disabled" value="y" <?php checked( $lead['disabled'], 'y' ); ?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-n">
									<input type="radio" id="disabled-n" class="mdl-radio__button" name="disabled" value="n" <?php checked( $lead['disabled'], 'n' ); ?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Lead Can Login</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-y">
									<input type="radio" id="canlogin-y" class="mdl-radio__button" name="canLogin" value="y" <?php checked( $lead['canLogin'], 'y' ); ?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-n">
									<input type="radio" id="canlogin-n" class="mdl-radio__button" name="canLogin" value="n" <?php checked( $lead['canLogin'], 'n' ); ?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Receive Property Updates</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-y">
									<input type="radio" id="updates-y" class="mdl-radio__button" name="receiveUpdates" value="y" <?php checked( $lead['receiveUpdates'], 'y' ); ?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-n">
									<input type="radio" id="updates-n" class="mdl-radio__button" name="receiveUpdates" value="n" <?php checked( $lead['receiveUpdates'], 'n' ); ?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>
						<br />

						<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored edit-lead" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_edit_nonce' ) ); ?>" data-lead-id="<?php echo esc_attr( $lead_id ); ?>" type="submit">Save Lead</button>
						<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
						<div class="error-fail" style="display: none;">Lead update failed. Check all required fields or try again later.</div>
						<div class="error-invalid-email" style="display: none;">Invalid email address detected. Please enter a valid email.</div>
						<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>

					</form>


					<a href="<?php echo esc_url( admin_url( 'admin.php?page=edit-lead' ) ); ?>" id="add-lead" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp">
						<i class="material-icons">add</i>
						<div class="mdl-tooltip" data-mdl-for="add-lead">Add New Lead</div>
					</a>

				</div>

				<div class="mdl-tabs__panel" id="lead-notes">
					<?php
					// order newest first.
					$notes_array = $this->idx_api->idx_api( 'note/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
					$notes_array = array_reverse( $notes_array );

					$offset = get_option( 'gmt_offset', 0 );

					echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp lead-notes">';
					echo '
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Date Created</th>
							<th class="mdl-data-table__cell--non-numeric note">Note</th>
							<th class="mdl-data-table__cell--non-numeric">Actions</th>
						</thead>
						<tbody>
						';

					// prepare notes for display.
					if ( $notes_array ) {
						foreach ( $notes_array as $note ) {

							// Skip large HTML table notes that are non-editable.
							if ( strpos( $note['note'], 'class=&quot;mcnTextContentContainer&quot;' ) ) {
								continue;
							}

							$nice_date = Carbon::parse( $note['created'] )->addHours( $offset )->toDayDateTimeString();

							echo '<tr id="note-id-' . esc_attr( $note['id'] ) . '" class="note-row note-id-' . esc_attr( $note['id'] ) . '">';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $nice_date ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric note"><div class="render-note-' . esc_attr( $note['id'] ) . '">' . wp_kses_post( str_replace( '&quot;', '"', str_replace( '&gt;', '>', str_replace( '&lt;', '<', $note['note'] ) ) ) ) . '</div></td>';
							echo '<td class="mdl-data-table__cell--non-numeric">
										<a href="#TB_inline?width=600&height=350&inlineId=edit-lead-note" class="edit-note thickbox" id="edit-note-' . esc_attr( $note['id'] ) . '" data-id="' . esc_attr( $lead_id ) . '" data-noteid="' . esc_attr( $note['id'] ) . '" data-note="' . esc_attr( $note['note'] ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'idx_lead_note_edit_nonce' ) ) . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-note-' . esc_attr( $note['id'] ) . '">Edit Note</div></a>

										<a href="#" id="delete-note-' . esc_attr( $note['id'] ) . '" class="delete-note" data-id="' . esc_attr( $lead_id ) . '" data-noteid="' . esc_attr( $note['id'] ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'idx_lead_note_delete_nonce' ) ) . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-note-' . esc_attr( $note['id'] ) . '">Delete Note</div></a>

										</td>';
							echo '</tr>';
						}
					}

					echo '</tbody></table>';
					echo '<dialog id="dialog-lead-note-delete">
							<form method="dialog">
								<h5>Delete Lead Note</h5>
								<p>Are you sure you want to delete this lead note?</p>
								<button type="submit" value="no" autofocus>No</button>
								<button type="submit" value="yes">Yes</button>
							</form>
						</dialog>';
					echo '
						<a href="#TB_inline?width=600&height=350&inlineId=add-lead-note" id="add-lead-note-btn" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp thickbox">
							<i class="material-icons">add</i>
							<div class="mdl-tooltip" data-mdl-for="add-lead-note-btn">Add Lead Note</div>
						</a>
						';
					?>
					<div id="add-lead-note" style="display: none;">
						<h5>Add Note</h5>
						<form action="" method="post" class="add-lead-note">
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<textarea class="mdl-textfield__input" type="text" rows="4" id="note" name="note" autofocus></textarea>
								<label class="mdl-textfield__label" for="note">Note <span class="is-required">(required)</span></label>
							</div><br />
							<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-note" data-id="<?php echo esc_attr( $lead_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_note_add_nonce' ) ); ?>" type="submit">Save Note</button>
							<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
							<div class="error-fail" style="display: none;">Lead note addition failed. Check all required fields or try again later.</div>
							<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
						</form>
					</div>

					<div id="edit-lead-note" style="display: none;">
						<h5>Edit Note</h5>
						<form action="" method="post" class="edit-lead-note">
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<textarea class="mdl-textfield__input" type="text" rows="4" id="note" name="note" value="" autofocus></textarea>
								<label class="mdl-textfield__label" for="note">Note <span class="is-required">(required)</span></label>
							</div><br />
							<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored edit-note" data-id="<?php echo esc_attr( $lead_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_note_edit_nonce' ) ); ?>" type="submit">Save Note</button>
							<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
							<div class="error-fail" style="display: none;">Lead note update failed. Check all required fields or try again later.</div>
							<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
						</form>
					</div>
				</div>
				<div class="mdl-tabs__panel" id="lead-properties">
					<?php
					// order newest first.
					$properties_array = $this->idx_api->idx_api( 'property/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
					$properties_array = array_reverse( $properties_array );

					// Get details URL.
					$details_url = $this->idx_api->details_url();

					$offset = get_option( 'gmt_offset', 0 );

					echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp lead-properties">';
					echo '
						<a style="display: none;" href="#" title="Delete Properties"><i class="material-icons md-18">delete</i> Delete Selected</a>
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Property Name</th>
							<th class="mdl-data-table__cell--non-numeric">Receive Updates</th>
							<th class="mdl-data-table__cell--non-numeric">Created</th>
							<th class="mdl-data-table__cell--non-numeric">Actions</th>
						</thead>
						<tbody>
						';

					// prepare properties for display.
					if ( ! empty( $properties_array ) && is_array( $properties_array ) ) {
						foreach ( $properties_array as $property ) {
							$nice_created_date = Carbon::parse( $property['created'] )->addHours( $offset )->toDayDateTimeString();
							$updates           = ( 'y' == $property['receiveUpdates'] ) ? 'Yes' : 'No';

							echo '<tr class="property-row property-id-' . esc_attr( $property['id'] ) . '">';
							echo '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( $details_url . '/' . $property['property']['idxID'] . '/' . $property['property']['listingID'] ) . '" target="_blank" id="view-property-' . esc_attr( $property['id'] ) . '"><div class="mdl-tooltip" data-mdl-for="view-property-' . esc_attr( $property['id'] ) . '">View Property</div>' . esc_html( stripslashes( $property['propertyName'] ) ) . '</a></td>';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $updates ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $nice_created_date ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric">
										<a href="#TB_inline?width=600&height=500&inlineId=edit-lead-property" class="edit-property thickbox" id="edit-property-' . esc_attr( $property['id'] ) . '" data-id="' . esc_attr( $lead_id ) . '" data-spid="' . esc_attr( $property['id'] ) . '" data-name="' . esc_attr( stripslashes( $property['propertyName'] ) ) . '" data-updates="' . esc_attr( $property['receiveUpdates'] ) . '" data-idxid="' . esc_attr( $property['property']['idxID'] ) . '" data-listingid="' . esc_attr( $property['property']['listingID'] ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'idx_lead_property_edit_nonce' ) ) . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-property-' . esc_attr( $property['id'] ) . '">Edit Property</div></a>

										<a href="#" id="delete-property-' . esc_attr( $property['id'] ) . '" class="delete-property" data-id="' . esc_attr( $lead_id ) . '" data-spid="' . esc_attr( $property['id'] ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'idx_lead_property_delete_nonce' ) ) . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-property-' . esc_attr( $property['id'] ) . '">Delete Saved Property</div></a>

										<a href="https://middleware.idxbroker.com/mgmt/addeditsavedprop.php?id=' . esc_attr( $lead_id ) . '&spid=' . esc_attr( $property['id'] ) . '" id="edit-mw-' . esc_attr( $property['id'] ) . '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . esc_attr( $property['id'] ) . '">Edit Property in Middleware</div></a>
										</td>';
							echo '</tr>';
						}
					}

					echo '</tbody></table>';
					echo '<dialog id="dialog-lead-property-delete">
							<form method="dialog">
								<h5>Delete Lead Property</h5>
								<p>Are you sure you want to delete this lead saved property?</p>
								<button type="submit" value="no" autofocus>No</button>
								<button type="submit" value="yes">Yes</button>
							</form>
						</dialog>';
					echo '
						<a href="#TB_inline?width=600&height=500&inlineId=add-lead-property" id="add-lead-property-btn" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp thickbox">
							<i class="material-icons">add</i>
							<div class="mdl-tooltip" data-mdl-for="add-lead-property-btn">Add New Property</div>
						</a>
						';
					?>
					<div id="add-lead-property" style="display: none;">
						<h5>Add Property</h5>
						<form action="" method="post" class="add-lead-property">
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<input class="mdl-textfield__input" type="text" id="propertyName" name="propertyName" autofocus>
								<label class="mdl-textfield__label" for="propertyName">Name <span class="is-required">(required)</span></label>
							</div>
							<div class="mdl-fieldgroup">
								<label class="mdl-selectfield__label" for="idxID">MLS</label>
								<div class="mdl-selectfield">
									<select class="mdl-selectfield__select" id="idxID" name="idxID">
										<?php self::approved_mls_select_list(); ?>
									</select>
								</div>
							</div>
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<input class="mdl-textfield__input" type="text" id="listingID" name="listingID">
								<label class="mdl-textfield__label" for="property">MLS ID <span class="is-required">(required)</span></label>
							</div>
							<div class="mdl-fieldgroup">
								<label for="receiveUpdates-add" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
									<input type="checkbox" id="receiveUpdates-add" name="receiveUpdates" class="mdl-switch__input" checked>
									<span class="mdl-switch__label">Receive Property Updates Off/On</span>
								</label>
							</div><br />
							<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-property" data-id="<?php echo esc_attr( $lead_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_property_add_nonce' ) ); ?>" type="submit">Save Property</button>
							<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
							<div class="error-fail" style="display: none;">Lead saved property addition failed. Check all required fields or try again later.</div>
							<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
						</form>
					</div>

					<div id="edit-lead-property" style="display: none;">
						<h5>Edit Property</h5>
						<form action="" method="post" class="edit-lead-property">
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<input class="mdl-textfield__input" type="text" id="propertyName" name="propertyName" autofocus>
								<label class="mdl-textfield__label" for="propertyName">Name <span class="is-required">(required)</span></label>
							</div>
							<div class="mdl-fieldgroup">
								<label class="mdl-selectfield__label" for="idxID">MLS</label>
								<div class="mdl-selectfield">
									<select class="mdl-selectfield__select" id="idxID" name="idxID">
										<?php self::approved_mls_select_list(); ?>
									</select>
								</div>
							</div>
							<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
								<input class="mdl-textfield__input" type="text" id="listingID" name="listingID">
								<label class="mdl-textfield__label" for="property">MLS ID <span class="is-required">(required)</span></label>
							</div>
							<div class="mdl-fieldgroup">
								<label for="receiveUpdates-edit" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
									<input type="checkbox" id="receiveUpdates-edit" name="receiveUpdates" class="mdl-switch__input" checked>
									<span class="mdl-switch__label">Receive Property Updates Off/On</span>
								</label>
							</div><br />
							<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored edit-property" data-id="<?php echo esc_attr( $lead_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_property_edit_nonce' ) ); ?>" type="submit">Save Property</button>
							<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
							<div class="error-fail" style="display: none;">Lead saved property update failed. Check all required fields or try again later.</div>
							<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
						</form>
					</div>
				</div>
				<div class="mdl-tabs__panel" id="lead-searches">
					<?php
					// order newest first.
					$searches_array = $this->idx_api->idx_api( 'search/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
					$searches_array = ( isset( $searches_array['searchInformation'] ) && is_array( $searches_array['searchInformation'] ) ) ? array_reverse( $searches_array['searchInformation'] ) : null;

					$results_url = $this->idx_api->system_results_url();

					$offset = get_option( 'gmt_offset', 0 );

					echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp lead-searches">';
					echo '
						<a style="display: none;" href="#" title="Delete Searches"><i class="material-icons md-18">delete</i> Delete Selected</a>
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Search Name</th>
							<th class="mdl-data-table__cell--non-numeric">Receive Updates</th>
							<th class="mdl-data-table__cell--non-numeric">Created</th>
							<th class="mdl-data-table__cell--non-numeric">Actions</th>
						</thead>
						<tbody>
						';

					// prepare searches for display.
					if ( is_array( $searches_array ) && ! empty( $searches_array ) ) {
						foreach ( $searches_array as $search ) {

							$search_query = http_build_query( ( $search['search'] ) );

							$nice_created_date = Carbon::parse( $search['created'] )->addHours( $offset )->toDayDateTimeString();
							$updates           = ( 'y' == $search['receiveUpdates'] ) ? 'Yes' : 'No';

							echo '<tr class="search-row">';
							echo '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( $results_url . '?' . $search_query ) . '" target="_blank" id="view-search-' . esc_attr( $search['id'] ) . '"><div class="mdl-tooltip" data-mdl-for="view-search-' . esc_attr( $search['id'] ) . '">View Search</div>' . esc_html( $search['searchName'] ) . '</a><br /><a href="' . esc_url( $results_url . '?' . $search_query ) . '&add=1" target="_blank" id="view-search-today-' . esc_attr( $search['id'] ) . '"><div class="mdl-tooltip" data-mdl-for="view-search-today-' . esc_attr( $search['id'] ) . '">View Today\'s Results</div>View Today\'s Results</a></td>';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $updates ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $nice_created_date ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric">
										<!--<a href="' . esc_url( admin_url( 'admin.php?page=edit-search&searchID=' . $search['id'] ) ) . '" id="edit-search-' . esc_attr( $search['id'] ) . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-search-' . esc_attr( $search['id'] ) . '">Edit Search</div></a>-->

										<a href="#" id="delete-search-' . esc_attr( $search['id'] ) . '" class="delete-search" data-id="' . esc_attr( $lead_id ) . '" data-ssid="' . esc_attr( $search['id'] ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'idx_lead_search_delete_nonce' ) ) . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-search-' . esc_attr( $search['id'] ) . '">Delete Saved Search</div></a>

										<a href="https://middleware.idxbroker.com/mgmt/addeditsavedsearch.php?id=' . esc_attr( $lead_id ) . '&ssid=' . esc_attr( $search['id'] ) . '" id="edit-mw-' . esc_attr( $search['id'] ) . '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . esc_attr( $search['id'] ) . '">Edit Search in Middleware</div></a>
										</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr class="search-row"><td class="mdl-data-table__cell--non-numeric">No searches found</td><td class="mdl-data-table__cell--non-numeric"></td><td class="mdl-data-table__cell--non-numeric"></td><td class="mdl-data-table__cell--non-numeric"></td></tr>';
					}

					echo '</tbody></table>';
					echo '<dialog id="dialog-lead-search-delete">
							<form method="dialog">
								<h5>Delete Lead Saved Search</h5>
								<p>Are you sure you want to delete this lead saved search?</p>
								<button type="submit" value="no" autofocus>No</button>
								<button type="submit" value="yes">Yes</button>
							</form>
						</dialog>';
					echo '
						<a href="' . esc_url( admin_url( 'admin.php?page=edit-idx-search&leadID=' . $lead_id . '&nonce=' . wp_create_nonce( 'idx_lead_add_search_nonce' ) ) ) . '" id="add-lead-search-btn" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp">
							<i class="material-icons">add</i>
							<div class="mdl-tooltip" data-mdl-for="add-lead-search-btn">Add Lead Saved Search</div>
						</a>
						';
					?>
				</div>
				<div class="mdl-tabs__panel" id="lead-traffic">
					<?php
					// order newest first.
					$traffic_array = $this->idx_api->idx_api( 'leadtraffic/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
					$traffic_array = array_reverse( $traffic_array );

					$traffic = '';

					$offset = get_option( 'gmt_offset', 0 );

					if ( is_array( $traffic_array ) ) {

						// prepare traffic for display.
						foreach ( $traffic_array as $traffic_entry ) {
							$nice_date = Carbon::parse( $traffic_entry['date'] )->addHours( $offset )->toDayDateTimeString();

							echo '<tr>';
							echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $nice_date ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( $traffic_entry['page'] ) . '" target="_blank">' . esc_html( substr( $traffic_entry['page'], 0, 80 ) ) . '</td>';
							echo '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( $traffic_entry['referrer'] ) . '" target="_blank">' . esc_html( substr( $traffic_entry['referrer'], 0, 80 ) ) . '</a></td>';
							echo '</tr>';
						}
					}

					echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp lead-traffic">';
					echo '
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Date</th>
							<th class="mdl-data-table__cell--non-numeric">Page</th>
							<th class="mdl-data-table__cell--non-numeric">Referrer</th>
						</thead>
						<tbody>
						';
					echo '</tbody></table>';
					?>
				</div>
			</div>

			<?php
		}
	}

	/**
	 * Actions to be taken prior to page loading. This is after headers have been set.
	 * Call on load-$hook.
	 */
	public function lead_edit_actions() {

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array( 'post_type' => null );

	}

	/**
	 * Output Agents as select options.
	 *
	 * @param string $agent_id - Agent ID.
	 * @return void
	 */
	private function agents_select_list( $agent_id = null ) {
		$agents_array = $this->idx_api->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( ! empty( $agent_id ) ) {
			echo '<option value="0" ' . selected( $agent_id, '0', 0 ) . '>None</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				echo '<option value="' . esc_attr( $agent['agentID'] ) . '" ' . selected( $agent_id, $agent['agentID'], 0 ) . '>' . esc_html( $agent['agentDisplayName'] ) . '</option>';
			}
		} else {
			echo '<option value="0">None</option>';
			if ( ! empty( $agents_array['agent'] ) && is_array( $agents_array['agent'] ) ) {
				foreach ( $agents_array['agent'] as $agent ) {
					echo '<option value="' . esc_attr( $agent['agentID'] ) . '">' . esc_html( $agent['agentDisplayName'] ) . '</option>';
				}
			}
		}
	}

	/**
	 * Output approved MLS's as select options
	 */
	private function approved_mls_select_list() {
		$mls_array = $this->idx_api->approved_mls();

		if ( ! empty( $mls_array ) && is_array( $mls_array ) ) {
			foreach ( $mls_array as $mls ) {
				echo '<option value="' . esc_attr( $mls->id ) . '">' . esc_html( $mls->name ) . '</option>';
			}
		} else {
			echo '<option value="a000">Demo</option>';
		}
	}

}
