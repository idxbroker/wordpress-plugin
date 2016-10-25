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
	}

	/**
	 * Output leads table 
	 * @return void
	 */
	function idx_leads_list() {

		// Check that the user is logged in & has proper permissions
		if ( !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
			return;
		}

		echo '<h3>Leads</h3>';
		// order newest first
		$leads_array = $this->idx_api->get_leads();
		$leads_array = array_reverse($leads_array);
		//$leads_array = array_slice(array_reverse($leads_array), 0, 5);
		
		$leads = '';

		//prepare leads for display
		foreach($leads_array as $lead){
			$last_active = Carbon::now()->createFromTimestampUTC(strtotime(($lead->lastActivityDate === '0000-00-00 00:00:00') ? $lead->subscribeDate : $lead->lastActivityDate))->diffForHumans();
			$agent = ($lead->agentOwner != '0') ? $lead->agentOwner : 'None assigned';
			$leads .= '<tr>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a href="#">' . $lead->firstName . ' ' . $lead->lastName . '</a></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric"><a href="mailto:' . $lead->email . '" target="_blank">' . $lead->email . '</a></td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $lead->phone . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $last_active . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">' . $agent . '</td>';
			$leads .= '<td class="mdl-data-table__cell--non-numeric">
						<a href="#" title="Edit Lead"><i class="material-icons md-18">create</i></a>
						<a href="#" title="Delete Lead"><i class="material-icons md-18">delete</i></a>
						<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . $lead->id . '" title="Edit Lead in Middleware"><i class="material-icons md-18">exit_to_app</i></a>
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
			<a href="#" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored">
				<i class="material-icons">add</i>
			</a>
			';
	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	function lead_list_actions(){

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array('post_type' => null);

	}

	/**
	 * Prints the jQuery script to initiliase the metaboxes
	 * Called on admin_footer-*
	*/
	function header_scripts(){
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
	function idx_leads_add_new() {

	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	function lead_add_new_actions(){

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array('post_type' => null);

	}
}
