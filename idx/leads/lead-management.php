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
			'Add New',
			'Add New',
			'manage_options',
			'add-lead',
			array(
				$this,
				'idx_leads_add_new'
			)
		);

		/* Add callbacks for this screen only */
		add_action('load-'.$this->page, array($this, 'lead_add_new_actions'),9);
		add_action('admin_head-'.$this->page, array($this,'header_scripts'));

		// Add Edit Lead submenu page
		$this->page = add_submenu_page(
			'leads',
			'Edit Lead',
			'Edit Lead',
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
		// order newest first
		$leads_array = $this->idx_api->get_leads();
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

			$leads .= '<tr>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a href="#">' . $lead->firstName . ' ' . $lead->lastName . '</a></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a id="mail-lead-' . $lead->id . '" href="mailto:' . $lead->email . '" target="_blank">' . $lead->email . '</a><div class="mdl-tooltip" data-mdl-for="mail-lead-' . $lead->id . '">Email Lead</div></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $lead->phone . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $last_active . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $agent_name . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">
						<a href="#" id="edit-lead-' . $lead->id . '"><i class="material-icons md-18">create</i><div class="mdl-tooltip" data-mdl-for="edit-lead-' . $lead->id . '">Edit Lead</div></a>
						<a href="#" id="delete-lead-' . $lead->id . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-lead-' . $lead->id . '">Delete Lead</div></a>
						<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . $lead->id . '" id="edit-mw-' . $lead->id . '"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . $lead->id . '">Edit Lead in Middleware</div></a>
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
			<table><tbody><thead>
				Rows per page:
					<select>
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
			</thead></tbody</table>
			';
		echo '
			<a href="' . admin_url('admin.php?page=add-lead') . '" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored">
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
	 * Output custom CSS, Fonts, Material design CSS, scripts and icons
	 * Called on admin_footer-*
	*/
	public function header_scripts(){
		?>
		<link rel="stylesheet" href="<?php echo IMPRESS_IDX_URL . 'assets/css/idx-leads.css' ;?>" type="text/css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
		<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
		<?php
	}

	/**
	 * Output form to add new lead 
	 * @return void
	 */
	public function idx_leads_add_new() {
		// Check that the user is logged in & has proper permissions
		if ( !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
			return;
		}

		?>

		<h3>Add Lead</h3>
		
		<form action="#">
			<h6>Account Information</h6>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="firstName">
				<label class="mdl-textfield__label" for="firstName">First Name</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="lastName">
				<label class="mdl-textfield__label" for="lastName">Last Name</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="phone">
				<label class="mdl-textfield__label" for="phone">Phone</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="email">
				<label class="mdl-textfield__label" for="email">Email</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="email2">
				<label class="mdl-textfield__label" for="email2">Additional Email</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="address">
				<label class="mdl-textfield__label" for="address">Street Address</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="city">
				<label class="mdl-textfield__label" for="city">City</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="stateProvince">
				<label class="mdl-textfield__label" for="stateProvince">State/Province</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="zipCode">
				<label class="mdl-textfield__label" for="zipCode">Zip Code</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="country">
				<label class="mdl-textfield__label" for="country">Country</label>
			</div>

			<h6>Account Settings</h6>
			<div class="mdl-fieldgroup">
				<label class="mdl-selectfield__label" for="emailFormat">Email Format</label>
				<div class="mdl-selectfield">
					<select class="mdl-selectfield__select" id="emailFormat">
						<option value="html">HTML</option>
						<option value="text">Plain Text</option>
					</select>
				</div>
			</div>
			<div class="mdl-fieldgroup">
				<label class="mdl-selectfield__label" for="agentOwner">Assigned Agent</label>
				<div class="mdl-selectfield">
					<select class="mdl-selectfield__select" id="agentOwner">
						<?php echo self::agents_select_list(); ?>
					</select>
				</div>
			</div>

			<div class="mdl-fieldgroup">
				<label class="mdl-selectfield__label" for="category">Category</label>
				<div class="mdl-selectfield">
					<select class="mdl-selectfield__select" id="category">
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
						<input type="radio" id="disabled-y" class="mdl-radio__button" name="account-disabled" value="y">
						<span class="mdl-radio__label">Yes</span>
					</label>
					<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-n">
						<input type="radio" id="disabled-n" class="mdl-radio__button" name="account-disabled" value="n" checked>
						<span class="mdl-radio__label">No</span>
					</label>
				</div>
			</div>

			<div class="mdl-fieldgroup">
				<div class="mdl-radiofield">
					<span class="mdl-label">Lead Can Login</span>
					<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-y">
						<input type="radio" id="canlogin-y" class="mdl-radio__button" name="can-login" value="y" checked>
						<span class="mdl-radio__label">Yes</span>
					</label>
					<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-n">
						<input type="radio" id="canlogin-n" class="mdl-radio__button" name="can-login" value="n">
						<span class="mdl-radio__label">No</span>
					</label>
				</div>
			</div>

			<div class="mdl-fieldgroup">
				<div class="mdl-radiofield">
					<span class="mdl-label">Receive Property Updates</span>
					<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-y">
						<input type="radio" id="updates-y" class="mdl-radio__button" name="updates" value="y" checked>
						<span class="mdl-radio__label">Yes</span>
					</label>
					<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-n">
						<input type="radio" id="updates-n" class="mdl-radio__button" name="updates" value="n">
						<span class="mdl-radio__label">No</span>
					</label>
				</div>
			</div>
			<br />

			<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" type="submit">Save Lead</button>

		</form>
		
		<?php
	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	public function lead_add_new_actions(){

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array('post_type' => null);

	}

	/**
	 * Output Agents as select options
	 */
	private function agents_select_list() {
		$agents_array = $this->idx_api->idx_api('agents', \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true);

		$agents_list = '<option value="0">None</option>';
		foreach($agents_array['agent'] as $agent) {
			$agents_list .= '<option value="' . $agent['agentID'] . '">' . $agent['agentDisplayName'] . '</option>'; 
		}

		return $agents_list;
	}

	/**
	 * Output edit lead page
	 * @return void
	 */
	public function idx_leads_edit() {
		// Check that the user is logged in & has proper permissions
		if ( !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
			return;
		}

		?>

		<h3>Edit Lead</h3>
		
		<form action="#">
			<h6>Account Information</h6>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="firstName">
				<label class="mdl-textfield__label" for="firstName">First Name</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="lastName">
				<label class="mdl-textfield__label" for="lastName">Last Name</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="phone">
				<label class="mdl-textfield__label" for="phone">Phone</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="email">
				<label class="mdl-textfield__label" for="email">Email</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="email2">
				<label class="mdl-textfield__label" for="email2">Additional Email</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="address">
				<label class="mdl-textfield__label" for="address">Street Address</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="city">
				<label class="mdl-textfield__label" for="city">City</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="stateProvince">
				<label class="mdl-textfield__label" for="stateProvince">State/Province</label>
			</div><br />
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="zipCode">
				<label class="mdl-textfield__label" for="zipCode">Zip Code</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
				<input class="mdl-textfield__input" type="text" id="country">
				<label class="mdl-textfield__label" for="country">Country</label>
			</div>

			<h6>Account Settings</h6>
			<div class="mdl-selectfield">
				<label class="mdl-selectfield__label" for="emailFormat">Email Format</label>
				<select class="mdl-selectfield__select" id="emailFormat">
					<option value="html">HTML</option>
					<option value="text">Plain Text</option>
				</select>
			</div>

			<div class="mdl-selectfield">
				<label class="mdl-selectfield__label" for="agentOwner">Assigned Agent</label>
				<select class="mdl-selectfield__select" id="agentOwner">
					<?php echo self::agents_select_list(); ?>
				</select>
			</div>

			<div class="mdl-selectfield">
				<label class="mdl-selectfield__label" for="category">Category</label>
				<select class="mdl-selectfield__select" id="category">
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
			</div><br />

			<div class="mdl-radiofield">
				<span class="mdl-label">Account Disabled</span>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-y">
					<input type="radio" id="disabled-y" class="mdl-radio__button" name="account-disabled" value="y">
					<span class="mdl-radio__label">Yes</span>
				</label>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="disabled-n">
					<input type="radio" id="disabled-n" class="mdl-radio__button" name="account-disabled" value="n" checked>
					<span class="mdl-radio__label">No</span>
				</label>
			</div>

			<div class="mdl-radiofield">
				<span class="mdl-label">Lead Can Login</span>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-y">
					<input type="radio" id="canlogin-y" class="mdl-radio__button" name="can-login" value="y" checked>
					<span class="mdl-radio__label">Yes</span>
				</label>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="canlogin-n">
					<input type="radio" id="canlogin-n" class="mdl-radio__button" name="can-login" value="n">
					<span class="mdl-radio__label">No</span>
				</label>
			</div>

			<div class="mdl-radiofield">
				<span class="mdl-label">Receive Property Updates</span>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-y">
					<input type="radio" id="updates-y" class="mdl-radio__button" name="updates" value="y" checked>
					<span class="mdl-radio__label">Yes</span>
				</label>
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="updates-n">
					<input type="radio" id="updates-n" class="mdl-radio__button" name="updates" value="n">
					<span class="mdl-radio__label">No</span>
				</label>
			</div>
			<br />

			<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" type="submit">Save Lead</button>

		</form>
		
		<?php
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

}
