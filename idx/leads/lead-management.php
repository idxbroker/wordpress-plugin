<?php
namespace IDX\Leads;
use \Carbon\Carbon;

class Lead_Management {

	private static $instance;

	public static function instance() {
		if (!isset(self::$instance) && !(self::$instance instanceof Lead_Management)) {
			self::$instance = new Lead_Management();
		}
		
		return self::$instance;
	}

	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		
		add_action('plugins_loaded', array($this, 'add_lead_pages'));
		add_action('admin_enqueue_scripts', array($this, 'idx_lead_scripts'));
		add_action('init', array($this, 'idx_ajax_actions'));
	}

	public $idx_api;

	public function add_lead_pages() {

		// Add Leads menu page
		$this->page = add_menu_page(
			'Leads',
			'Leads',
			'manage_options',
			'leads',
			array(
				$this,
				'idx_leads_list'
			),
			'dashicons-businessman',
			'30'
		);

		/* Add callbacks for this screen only */
		add_action('load-'.$this->page, array($this, 'lead_list_actions'),9);
		add_action('admin_head-'.$this->page, array($this,'header_scripts'));

		// Add Leads as submenu page also
		$this->page = add_submenu_page(
			'leads',
			'Leads',
			'Leads',
			'manage_options',
			'leads',
			array(
				$this,
				'idx_leads_list'
			)
		);

		/* Add callbacks for this screen only */
		add_action('load-'.$this->page, array($this, 'lead_list_actions'),9);
		add_action('admin_head-'.$this->page, array($this,'header_scripts'));

		// Add Add Lead submenu page
		$this->page = add_submenu_page(
			'leads',
			'Add/Edit Lead',
			'Add Lead',
			'manage_options',
			'edit-lead',
			array(
				$this,
				'idx_leads_edit'
			)
		);

		/* Add callbacks for this screen only */
		add_action('load-'.$this->page, array($this, 'lead_edit_actions'),9);
		add_action('admin_head-'.$this->page, array($this,'header_scripts'));

	}

	public function idx_ajax_actions() {
		add_action('wp_ajax_idx_lead_add', array($this, 'idx_lead_add'));
		add_action('wp_ajax_idx_lead_edit', array($this, 'idx_lead_edit'));
		add_action('wp_ajax_idx_lead_delete', array($this, 'idx_lead_delete'));
	}

	public function idx_lead_scripts() {
		wp_enqueue_script( 'idx_lead_delete_script', IMPRESS_IDX_URL . 'assets/js/idx-leads.js', array( 'jquery' ), true );
		wp_localize_script( 'idx_lead_delete_script', 'IDXLeadAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'leadurl' => admin_url( 'admin.php?page=edit-lead&leadID=' )
			)
		);
	}

	/**
	 * Add a lead via API
	 * echoes response to /assets/js/idx-leads.js
	 * @return void
	 */
	public function idx_lead_add(){

		$permission = check_ajax_referer( 'idx_lead_add_nonce', 'nonce', false );
		if( $permission == false || !isset($_POST['fields']) ) {
			echo 'error';
		} else {

			// Add lead via API
			$api_url = 'https://api.idxbroker.com/leads/lead';
			$args = array(
				'method' => 'PUT',
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option('idx_broker_apikey'),
					'outputtype'   => 'json'
				),
				'sslverify' => false,
				'body'		=> $_POST['fields']
			);
			$response = wp_remote_request($api_url, $args);

			$decoded_response = json_decode($response['body'], 1);

			if($decoded_response == 'Lead already exists.') {
				echo 'Lead already exists.';
			} elseif(wp_remote_retrieve_response_code($response) == '200') {
				// Delete lead cache so new lead will show in list views immediately
				delete_option('idx_lead_cache');
				// return new lead ID to script
				echo $decoded_response['newID'];
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Edit a lead via API
	 * echoes response to /assets/js/idx-leads.js
	 * @return void
	 */
	public function idx_lead_edit(){

		$permission = check_ajax_referer( 'idx_lead_edit_nonce', 'nonce', false );
		if( $permission == false || !isset($_POST['fields']) || !isset($_POST['leadID']) ) {
			echo 'error';
		} else {

			// Edit lead via API
			$api_url = 'https://api.idxbroker.com/leads/lead/' . $_POST['leadID'];
			$args = array(
				'method' => 'POST',
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option('idx_broker_apikey'),
					'outputtype'   => 'json'
				),
				'sslverify' => false,
				'body'		=> $_POST['fields']
			);
			$response = wp_remote_request($api_url, $args);

			$decoded_response = json_decode($response['body'], 1);

			if(wp_remote_retrieve_response_code($response) == '204') {
				delete_option('idx_lead/' . $_POST['leadID'] . '_cache');
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
	 * @return void
	 */
	public function idx_lead_delete(){

		$permission = check_ajax_referer( 'idx_lead_delete_nonce', 'nonce', false );
		if( $permission == false || !isset($_POST['id'])) {
			echo 'error';
		} else {
			// Delete lead via API
			$api_url = 'https://api.idxbroker.com/leads/lead/' . $_POST['id'];
			$args = array(
				'method' => 'DELETE',
				'headers' => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option('idx_broker_apikey'),
					'outputtype'   => 'json',
					'apiversion' => '1.2.3'
				),
				'sslverify' => false,
				'body'		=> null
			);
			$response = wp_remote_request($api_url, $args);

			if(wp_remote_retrieve_response_code($response) == '204') {
				delete_option('idx_lead_cache');
				delete_option('idx_lead/' . $_POST['id'] . '_cache');
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Output leads table 
	 * @return void
	 */
	public function idx_leads_list() {

		// Check that the user is logged in & has proper permissions
		if ( !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
			return;
		}

		echo '<h3>Leads</h3>';

		$leads_array = $this->idx_api->get_leads();
		// order by lastActivityDate
		
		$leads_array = array_reverse($leads_array);
		//$leads_array = array_slice(array_reverse($leads_array), 0, 5);
		
		$agents_array = $this->idx_api->idx_api('agents', \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true);
		
		$leads = '';

		//prepare leads for display
		foreach($leads_array as $lead){
			$last_active = Carbon::now()->createFromTimestampUTC(strtotime(($lead->lastActivityDate === '0000-00-00 00:00:00') ? $lead->subscribeDate : $lead->lastActivityDate))->diffForHumans();

			if ($lead->agentOwner != '0') {
				foreach($agents_array['agent'] as $agent) {
					if(in_array($lead->agentOwner, $agent)) {
						$agent_name = $agent['agentDisplayName'];
					}
				}
			} else {
				$agent_name = 'None assigned';
			}

			$avatar_args = array(
				'force_display' => true
			);

			$nonce = wp_create_nonce('idx_lead_delete_nonce');

			$leads .= '<tr class="lead-row">';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a href="' . admin_url('admin.php?page=edit-lead&leadID=' . $lead->id) . '">' . get_avatar($lead->email, 32, '', false, $avatar_args) . ' ' . $lead->firstName . ' ' . $lead->lastName . '</a></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a id="mail-lead-' . $lead->id . '" href="mailto:' . $lead->email . '" target="_blank">' . $lead->email . '</a><div class="mdl-tooltip" data-mdl-for="mail-lead-' . $lead->id . '">Email Lead</div></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $lead->phone . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $last_active . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $agent_name . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">
						<a href="' . admin_url('admin.php?page=edit-lead&leadID=' . $lead->id) . '" id="edit-lead-' . $lead->id . '" data-id="' . $lead->id . '" data-nonce="' . $nonce . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-lead-' . $lead->id . '">Edit Lead</div></a>
						<a href="' . admin_url('admin-ajax.php?action=idx_lead_delete&id=' . $lead->id . '&nonce=' . $nonce) . '" id="delete-lead-' . $lead->id . '" class="delete-lead" data-id="' . $lead->id . '" data-nonce="' . $nonce . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-lead-' . $lead->id . '">Delete Lead</div></a>
						<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . $lead->id . '" id="edit-mw-' . $lead->id . '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . $lead->id . '">Edit Lead in Middleware</div></a>
						</td>';
			$leads .= '</tr>';
		}

		echo '<table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp">';
		echo '
			<thead>
				<th class="mdl-data-table__cell--non-numeric">Lead Name</th>
				<th class="mdl-data-table__cell--non-numeric">Email</th>
				<th class="mdl-data-table__cell--non-numeric">Phone</th>
				<th class="mdl-data-table__cell--non-numeric">Last Active</th>
				<th class="mdl-data-table__cell--non-numeric">Agent</th>
				<th class="mdl-data-table__cell--non-numeric">Actions</th>
			</thead>
			<thead style="display:none;">
				<th class="mdl-data-table__cell--non-numeric"><a href="#" title="Delete Lead"><i class="material-icons md-18">delete</i> Delete Selected</a></th>
			</thead>
			<tbody>
			';
		echo $leads;
		echo '</tbody></table>';
		echo '
			<div class="mdl-selectfield">
				Rows per page:
				<select class="mdl-selectfield__select">
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
				</select>
			</div>
			';
		echo '
			<a href="' . admin_url('admin.php?page=edit-lead') . '" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored">
				<i class="material-icons">add</i>
			</a>
			';
	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	public function lead_list_actions(){

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array('post_type' => null);

	}


	/**
	 * Output edit lead page
	 * @return void
	 */
	public function idx_leads_edit() {
		// Check that the user is logged in & has proper permissions
		if ( !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
			return;
		} elseif(empty($_GET['leadID'])) { ?>

			<h3>Add Lead</h3>
			
			<form id="add-lead" action="" method="post">
				<h6>Account Information</h6>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label is-required">
					<input class="mdl-textfield__input" type="text" id="firstName" name="firstName">
					<label class="mdl-textfield__label" for="firstName">First Name</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="lastName" name="lastName">
					<label class="mdl-textfield__label" for="lastName">Last Name</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="phone" name="phone">
					<label class="mdl-textfield__label" for="phone">Phone</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="email" name="email">
					<label class="mdl-textfield__label" for="email">Email</label>
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
							<?php echo self::agents_select_list(); ?>
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
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-lead" data-nonce="<?php echo wp_create_nonce('idx_lead_add_nonce'); ?>" type="submit">Save Lead</button>

			</form>
		<?php
		
		} else {
			$lead_id = $_GET['leadID'];
			if(empty($lead_id)) {
				return;
			}
			// Get Lead info
			$lead = $this->idx_api->idx_api('lead/' . $lead_id, \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'leads', array(), 7200, 'GET', true);
		?>
			<h3>Edit Lead</h3>

			<div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
				<div class="mdl-tabs__tab-bar">
					<a href="#lead-info" class="mdl-tabs__tab is-active">Lead Info</a>
					<a href="#lead-notes" class="mdl-tabs__tab">Notes</a>
					<a href="#lead-properties" class="mdl-tabs__tab">Saved Properties</a>
					<a href="#lead-searches" class="mdl-tabs__tab">Saved Searches</a>
					<a href="#lead-traffic" class="mdl-tabs__tab">Traffic History</a>
				</div>
				<div class="mdl-tabs__panel is-active" id="lead-info">
					<form id="edit-lead" action="" method="post">
						<h6>Account Information</h6>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="firstName" name="firstName" value="<?php echo ($lead['firstName']) ? $lead['firstName'] : '';?>">
							<label class="mdl-textfield__label" for="firstName">First Name</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="lastName" name="lastName" value="<?php echo ($lead['lastName']) ? $lead['lastName'] : '';?>">
							<label class="mdl-textfield__label" for="lastName">Last Name</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="phone" name="phone" value="<?php echo ($lead['phone']) ? $lead['phone'] : '';?>">
							<label class="mdl-textfield__label" for="phone">Phone</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="email" name="email" value="<?php echo ($lead['email']) ? $lead['email'] : '';?>">
							<label class="mdl-textfield__label" for="email">Email</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="email2" name="email2" value="<?php echo ($lead['email2']) ? $lead['email2'] : '';?>">
							<label class="mdl-textfield__label" for="email2">Additional Email</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="address" name="address" value="<?php echo ($lead['address']) ? $lead['address'] : '';?>">
							<label class="mdl-textfield__label" for="address">Street Address</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="city" name="city" value="<?php echo ($lead['city']) ? $lead['city'] : '';?>">
							<label class="mdl-textfield__label" for="city">City</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="stateProvince" name="stateProvince" value="<?php echo ($lead['stateProvince']) ? $lead['stateProvince'] : '';?>">
							<label class="mdl-textfield__label" for="stateProvince">State/Province</label>
						</div><br />
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="zipCode" name="zipCode" value="<?php echo ($lead['zipCode']) ? $lead['zipCode'] : '';?>">
							<label class="mdl-textfield__label" for="zipCode">Zip Code</label>
						</div>
						<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
							<input class="mdl-textfield__input" type="text" id="country" name="country" value="<?php echo ($lead['country']) ? $lead['country'] : '';?>">
							<label class="mdl-textfield__label" for="country">Country</label>
						</div>

						<h6>Account Settings</h6>
						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="emailFormat">Email Format</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="emailFormat" name="emailFormat">
									<option value="html" <?php selected($lead['emailFormat'], 'html');?>>HTML</option>
									<option value="text" <?php selected($lead['emailFormat'], 'text');?>>Plain Text</option>
								</select>
							</div>
						</div>
						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="agentOwner">Assigned Agent</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="agentOwner" name="agentOwner">
									<?php echo self::agents_select_list($lead['agentOwner']); ?>
								</select>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<label class="mdl-selectfield__label" for="actualCategory">Category</label>
							<div class="mdl-selectfield">
								<select class="mdl-selectfield__select" id="actualCategory" name="actualCategory">
									<option value="" <?php selected($lead['actualCategory'], '');?>>---</option>
									<option value="Buyer" <?php selected($lead['actualCategory'], 'Buyer');?>>Buyer</option>
									<option value="Contact" <?php selected($lead['actualCategory'], 'Contact');?>>Contact</option>
									<option value="Direct Signup" <?php selected($lead['actualCategory'], 'Direct Signup');?>>Direct Signup</option>
									<option value="Home Valuation" <?php selected($lead['actualCategory'], 'Home Valuation');?>>Home Valuation</option>
									<option value="More Info" <?php selected($lead['actualCategory'], 'More Info');?>>More Info</option>
									<option value="Property Updates" <?php selected($lead['actualCategory'], 'Property Updates');?>>Property Updates</option>
									<option value="Scheduled Showing" <?php selected($lead['actualCategory'], 'Scheduled Showing');?>>Scheduled Showing</option>
									<option value="Seller" <?php selected($lead['actualCategory'], 'Seller');?>>Seller</option>
									<option value="Unknown" <?php selected($lead['actualCategory'], 'Unknown');?>>Unknown</option>
								</select>
							</div>
						</div><br />

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Account Disabled</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-y">
									<input type="radio" id="disabled-y" class="mdl-radio__button" name="disabled" value="y" <?php checked($lead['disabled'], 'y');?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-n">
									<input type="radio" id="disabled-n" class="mdl-radio__button" name="disabled" value="n" <?php checked($lead['disabled'], 'n');?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Lead Can Login</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-y">
									<input type="radio" id="canlogin-y" class="mdl-radio__button" name="canLogin" value="y" <?php checked($lead['canLogin'], 'y');?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-n">
									<input type="radio" id="canlogin-n" class="mdl-radio__button" name="canLogin" value="n" <?php checked($lead['canLogin'], 'n');?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>

						<div class="mdl-fieldgroup">
							<div class="mdl-radiofield">
								<span class="mdl-label">Receive Property Updates</span>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-y">
									<input type="radio" id="updates-y" class="mdl-radio__button" name="receiveUpdates" value="y" <?php checked($lead['receiveUpdates'], 'y');?>>
									<span class="mdl-radio__label">Yes</span>
								</label>
								<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-n">
									<input type="radio" id="updates-n" class="mdl-radio__button" name="receiveUpdates" value="n" <?php checked($lead['receiveUpdates'], 'n');?>>
									<span class="mdl-radio__label">No</span>
								</label>
							</div>
						</div>
						<br />

						<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored edit-lead" data-nonce="<?php echo wp_create_nonce('idx_lead_edit_nonce'); ?>" data-lead-id="<?php echo $lead_id; ?>" type="submit">Save Lead</button> 

					</form>
					<div class="lead-photo">
						<?php $avatar_args = array( 'force_display' => true ); echo get_avatar($lead['email'], 256, '', false, $avatar_args); ?>
					</div>
				</div>
				<div class="mdl-tabs__panel" id="lead-notes">
					<?php
					echo '<h6>Notes</h6>';

					// order newest first
					$notes_array = $this->idx_api->idx_api('note/' . $lead_id, \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'leads', array(), 7200, 'GET', true);
					$notes_array = array_reverse($notes_array);
					//$leads_array = array_slice(array_reverse($leads_array), 0, 5);
					
					$notes = '';

					//prepare notes for display
					foreach($notes_array as $note){
						$nice_date = Carbon::parse($note['created'])->toDayDateTimeString();

						$notes .= '<tr>';
						$notes .= '<td class="mdl-data-table__cell--non-numeric">' . $nice_date . '</td>';
						$notes .= '<td class="mdl-data-table__cell--non-numeric note"><div class="render-note">' . str_replace('&quot;', '"', str_replace('&gt;', '>', str_replace('&lt;', '<', $note['note']))) . '</div></td>';
						$notes .= '<td class="mdl-data-table__cell--non-numeric">
									<!--<a href="' . admin_url('admin.php?page=edit-note&noteID=' . $note['id']) . '" id="edit-note-' . $note['id'] . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-note-' . $note['id'] . '">Edit Note</div></a>-->
									<a href="#" id="delete-note-' . $note['id'] . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-note-' . $note['id'] . '">Delete Note</div></a>
									<a href="https://middleware.idxbroker.com/mgmt/leadnotes.php?id=' . $note['id'] . '" id="edit-mw-' . $note['id'] . '"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . $note['id'] . '">Edit Note in Middleware</div></a>
									</td>';
						$notes .= '</tr>';
					}

					echo '<table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp">';
					echo '
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Date Created</th>
							<th class="mdl-data-table__cell--non-numeric note">Note</th>
							<th class="mdl-data-table__cell--non-numeric">Actions</th>
						</thead>
						<thead style="display:none;">
							<th class="mdl-data-table__cell--non-numeric"><a href="#" title="Delete Note"><i class="material-icons md-18">delete</i> Delete Selected</a></th>
						</thead>
						<tbody>
						';
					echo $notes;
					echo '</tbody></table>';
					echo '
						<div class="mdl-selectfield">
							Rows per page:
							<select class="mdl-selectfield__select">
								<option value="10">10</option>
								<option value="25">25</option>
								<option value="50">50</option>
								<option value="100">100</option>
							</select>
						</div>
						';
					// echo '
					// 	<a href="' . admin_url('admin.php?page=add-note') . '" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored">
					// 		<i class="material-icons">add</i>
					// 	</a>
					// 	';
			?>
				</div>
				<div class="mdl-tabs__panel" id="lead-properties">
					Properties here
				</div>
				<div class="mdl-tabs__panel" id="lead-searches">
					Searches here
				</div>
				<div class="mdl-tabs__panel" id="lead-traffic">
				<?php
					echo '<h6>Traffic</h6>';

					// order newest first
					$traffic_array = $this->idx_api->idx_api('leadtraffic/' . $lead_id, \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'leads', array(), 7200, 'GET', true);
					$traffic_array = array_reverse($traffic_array);
					//$leads_array = array_slice(array_reverse($leads_array), 0, 5);
					
					$traffic = '';

					//prepare traffic for display
					foreach($traffic_array as $traffic_entry){
						$nice_date = Carbon::parse($traffic_entry['date'])->toDayDateTimeString();

						$traffic .= '<tr>';
						$traffic .= '<td class="mdl-data-table__cell--non-numeric">' . $nice_date . '</td>';
						$traffic .= '<td class="mdl-data-table__cell--non-numeric"><a href="' . $traffic_entry['page'] . '">' . substr($traffic_entry['page'], 0, 80) . '</td>';
						$traffic .= '<td class="mdl-data-table__cell--non-numeric"><a href="' . $traffic_entry['referrer'] . '">' . substr($traffic_entry['referrer'], 0, 80) . '</a></td>';
						$traffic .= '</tr>';
					}

					echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">';
					echo '
						<thead>
							<th class="mdl-data-table__cell--non-numeric">Date</th>
							<th class="mdl-data-table__cell--non-numeric">Page</th>
							<th class="mdl-data-table__cell--non-numeric">Referrer</th>
						</thead>
						<thead style="display:none;">
							<th class="mdl-data-table__cell--non-numeric"><a href="#" title="Delete Note"><i class="material-icons md-18">delete</i> Delete Selected</a></th>
						</thead>
						<tbody>
						';
					echo $traffic;
					echo '</tbody></table>';
					echo '
						<div class="mdl-selectfield">
							Rows per page:
							<select class="mdl-selectfield__select">
								<option value="10">10</option>
								<option value="25">25</option>
								<option value="50">50</option>
								<option value="100">100</option>
							</select>
						</div>
						';
				?>
				</div>
			</div>
		
		<?php
		}
	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	public function lead_edit_actions(){

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array('post_type' => null);

	}

		/**
	 * Output custom CSS, Fonts, Material design CSS, scripts and icons
	 * Called on admin_footer-*
	*/
	public function header_scripts(){
		?>
		<link rel="stylesheet" href="<?php echo IMPRESS_IDX_URL . 'assets/css/idx-leads.css' ;?>" type="text/css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="<?php echo IMPRESS_IDX_URL . 'assets/css/material.min.css' ;?>" type="text/css">
		<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
		<?php
	}

	/**
	 * Output Agents as select options
	 */
	private function agents_select_list($agent_id = null) {
		$agents_array = $this->idx_api->idx_api('agents', \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true);

		if($agent_id != null) {
			$agents_list = '<option value="0" '. selected($agent_id, '0', 0) . '>None</option>';
			foreach($agents_array['agent'] as $agent) {
				$agents_list .= '<option value="' . $agent['agentID'] . '" ' . selected($agent_id, $agent['agentID'], 0) . '>' . $agent['agentDisplayName'] . '</option>'; 
			}
		} else {
			$agents_list = '<option value="0">None</option>';
			foreach($agents_array['agent'] as $agent) {
				$agents_list .= '<option value="' . $agent['agentID'] . '">' . $agent['agentDisplayName'] . '</option>'; 
			}
		}

		return $agents_list;
	}

}
