<?php
/**
 * UI for managing saved links and lead saved searches
 *
 * @package IDX\Views
 */

namespace IDX\Views;

use \Carbon\Carbon;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Set up the class
 */
class Search_Management {
	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Search_Management ) ) {
			self::$instance = new Search_Management();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		add_action( 'admin_menu', array( $this, 'add_search_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'idx_search_scripts' ) );
		add_action( 'init', array( $this, 'idx_ajax_actions' ) );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	public function add_search_pages() {

		// Add Searches as submenu page.
		$this->page = add_submenu_page(
			'idx-broker',
			'Searches',
			'Saved Searches',
			'manage_options',
			'idx-searches',
			array(
				$this,
				'idx_searches_list',
			)
		);

		// Add Add Search submenu page.
		$this->page = add_submenu_page(
			'idx-broker',
			'Add/Edit Search',
			'Add Search',
			'manage_options',
			'edit-idx-search',
			array(
				$this,
				'idx_searches_edit',
			)
		);

	}

	public function idx_ajax_actions() {
		add_action( 'wp_ajax_idx_search_add', array( $this, 'idx_search_add' ) );
		add_action( 'wp_ajax_idx_lead_search_add', array( $this, 'idx_lead_search_add' ) );
		add_action( 'wp_ajax_idx_search_delete', array( $this, 'idx_search_delete' ) );
	}

	public function idx_search_scripts() {

		// Only load on searches pages.
		$screen_id = get_current_screen();
		if ( 'impress_page_idx-searches' === $screen_id->id || 'impress_page_edit-idx-search' === $screen_id->id || 'searches_page_edit-idx-search' === $screen_id->id || 'toplevel_page_idx-searches' === $screen_id->id ) {

			wp_enqueue_script( 'idx_search_ajax_script', IMPRESS_IDX_URL . 'assets/js/idx-searches.min.js', [ 'jquery' ], '1.0.0', false );
			wp_localize_script(
				'idx_search_ajax_script',
				'IDXSearchAjax',
				array(
					'ajaxurl'     => admin_url( 'admin-ajax.php' ),
					'searchesurl' => admin_url( 'admin.php?page=idx-searches' ),
					'leadurl'     => admin_url( 'admin.php?page=edit-lead&leadID=' ),
					'detailsurl'  => $this->idx_api->details_url(),
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
			wp_enqueue_style( 'select2' );
		}
	}

	/**
	 * Add a search via API
	 * echoes response to /assets/js/idx-searches.js
	 *
	 * @return void
	 */
	public function idx_search_add() {

		$permission = check_ajax_referer( 'idx_search_add_nonce', 'nonce', false );
		if ( $permission == false || ! isset( $_POST['pageTitle'] ) || ! isset( $_POST['linkTitle'] ) ) {
			echo 'missing required fields';
		} else {

			// Add search via API.
			$api_url = IDX_API_URL . '/clients/savedlinks';

			$search_query = array(
				'pt'    => sanitize_text_field( wp_unslash( $_POST['pt'] ) ),
				'ccz'   => sanitize_text_field( wp_unslash( $_POST['ccz'] ) ),
				'lp'    => sanitize_text_field( wp_unslash( $_POST['lp'] ) ),
				'hp'    => sanitize_text_field( wp_unslash( $_POST['hp'] ) ),
				'bd'    => sanitize_text_field( wp_unslash( $_POST['bd'] ) ),
				'ba'    => sanitize_text_field( wp_unslash( $_POST['ba'] ) ),
				'sqft'  => sanitize_text_field( wp_unslash( $_POST['sqft'] ) ),
				'acres' => sanitize_text_field( wp_unslash( $_POST['acres'] ) ),
				'add'   => sanitize_text_field( wp_unslash( $_POST['add'] ) ),
			);

			if ( 'city' === $_POST['ccz'] ) {
				$city_array   = array( 'city' => $_POST['locations'] );
				$search_query = $search_query + $city_array;
			} elseif ( 'county' === $_POST['ccz'] ) {
				$county_array = array( 'county' => $_POST['locations'] );
				$search_query = $search_query + $county_array;
			} elseif ( 'zipcode' === $_POST['ccz'] ) {
				$zipcode_array = array( 'zipcode' => $_POST['locations'] );
				$search_query  = $search_query + $zipcode_array;
			}

			$data = array(
				'pageTitle'          => sanitize_text_field( wp_unslash( $_POST['pageTitle'] ) ),
				'linkName'           => sanitize_text_field( wp_unslash( str_replace( ' ', '-', strtolower( $_POST['linkTitle'] ) ) ) ),
				'linkTitle'          => sanitize_text_field( wp_unslash( $_POST['linkTitle'] ) ),
				'queryString'        => $search_query,
				'useDescriptionMeta' => ( isset( $_POST['useDescriptionMeta'] ) ) ? sanitize_text_field( wp_unslash( $_POST['useDescriptionMeta'] ) ) : '',
				'descriptionMeta'    => ( isset( $_POST['descriptionMeta'] ) ) ? sanitize_text_field( wp_unslash( $_POST['descriptionMeta'] ) ) : '',
				'useKeywordsMeta'    => ( isset( $_POST['useKeywordsMeta'] ) ) ? sanitize_text_field( wp_unslash( $_POST['useKeywordsMeta'] ) ) : '',
				'keywords'           => ( isset( $_POST['keywords'] ) ) ? sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) : '',
				'featured'           => ( isset( $_POST['featured'] ) ) ? sanitize_text_field( wp_unslash( $_POST['featured'] ) ) : '',
				'linkCopy'           => ( isset( $_POST['linkCopy'] ) ) ? sanitize_text_field( wp_unslash( $_POST['linkCopy'] ) ) : '',
				'agentID'            => ( isset( $_POST['agentID'] ) ) ? sanitize_text_field( wp_unslash( $_POST['agentID'] ) ) : '',
			);

			$data = array_merge( $data, $search_query );

			$args     = array(
				'method'    => 'PUT',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => http_build_query( $data ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( ! is_wp_error( $response ) ) {
				// Delete search cache so new search will show in list views immediately.
				delete_option( 'idx_clients_savedlinks_cache' );
				// return new search ID to script.
				echo esc_html( $decoded_response['newID'] );
			} else {
				echo esc_html( $response->get_error_message() );
			}
		}
		die();
	}

	/**
	 * Add a lead search via API
	 * echoes response to /assets/js/idx-searches.js
	 *
	 * @return void
	 */
	public function idx_lead_search_add() {

		$permission = check_ajax_referer( 'idx_lead_search_add_nonce', 'nonce', false );
		if ( false == $permission || empty( $_POST['leadID'] ) ) {
			echo 'missing required fields';
		} else {

			// Add search via API.
			$api_url = IDX_API_URL . '/leads/search/' . $_POST['leadID'];

			$search_query = array(
				'pt'    => sanitize_text_field( wp_unslash( $_POST['pt'] ) ),
				'ccz'   => sanitize_text_field( wp_unslash( $_POST['ccz'] ) ),
				'lp'    => sanitize_text_field( wp_unslash( $_POST['lp'] ) ),
				'hp'    => sanitize_text_field( wp_unslash( $_POST['hp'] ) ),
				'bd'    => sanitize_text_field( wp_unslash( $_POST['bd'] ) ),
				'ba'    => sanitize_text_field( wp_unslash( $_POST['ba'] ) ),
				'sqft'  => sanitize_text_field( wp_unslash( $_POST['sqft'] ) ),
				'acres' => sanitize_text_field( wp_unslash( $_POST['acres'] ) ),
				'add'   => sanitize_text_field( wp_unslash( $_POST['add'] ) ),
			);

			if ( 'city' === $_POST['ccz'] ) {
				$city_array   = array( 'city' => $_POST['locations'] );
				$search_query = $search_query + $city_array;
			} elseif ( 'county' === $_POST['ccz'] ) {
				$county_array = array( 'county' => $_POST['locations'] );
				$search_query = $search_query + $county_array;
			} elseif ( 'zipcode' === $_POST['ccz'] ) {
				$zipcode_array = array( 'zipcode' => $_POST['locations'] );
				$search_query  = $search_query + $zipcode_array;
			}

			$data     = array(
				'searchName'     => $_POST['searchName'],
				'search'         => $search_query,
				'receiveUpdates' => ( isset( $_POST['receiveUpdates'] ) ) ? $_POST['receiveUpdates'] : '',
			);
			$args     = array(
				'method'    => 'PUT',
				'headers'   => array(
					'content-type' => 'application/x-www-form-urlencoded',
					'accesskey'    => get_option( 'idx_broker_apikey' ),
					'outputtype'   => 'json',
				),
				'sslverify' => false,
				'body'      => http_build_query( $data ),
			);
			$response = wp_remote_request( $api_url, $args );

			$decoded_response = json_decode( $response['body'], 1 );

			if ( ! is_wp_error( $response ) ) {
				// Delete search cache so new search will show in list views immediately.
				delete_option( 'idx_leads_search/' . $_POST['leadID'] . '_cache' );
				// return new search ID to script.
				echo esc_html( $decoded_response['newID'] );
			} else {
				echo esc_html( $response->get_error_message() );
			}
		}
		die();
	}

	/**
	 * Delete a saved search via API
	 * echoes response to /assets/js/idx-searches.js
	 *
	 * @return void
	 */
	public function idx_search_delete() {

		$permission = check_ajax_referer( 'idx_search_delete_nonce', 'nonce', false );
		if ( $permission == false || ! isset( $_POST['ssid'] ) ) {
			echo 'error';
		} else {
			// Delete lead saved search via API.
			$api_url  = IDX_API_URL . '/clients/savedlinks/' . $_POST['ssid'];
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
				delete_option( 'idx_clients_savedlinks_cache' );
				echo 'success';
			} else {
				echo 'error';
			}
		}
		die();
	}

	/**
	 * Output searches table
	 *
	 * @return void
	 */
	public function idx_searches_list() {
		// Check that the user is logged in & has proper permissions.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<h3>Saved Links</h3>';

		$searches_array = $this->idx_api->idx_api_get_savedlinks();

		$searches_array = array_reverse( $searches_array );

		$offset = get_option( 'gmt_offset', 0 );

		echo '<table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp searches">';
		echo '
			<a href="#" title="Delete Search" class="delete-selected hide"><i class="material-icons md-18">delete</i> Delete Selected</a>
			<thead>
				<th class="mdl-data-table__cell--non-numeric">Search Name</th>
				<th class="mdl-data-table__cell--non-numeric">Created</th>
				<th class="mdl-data-table__cell--non-numeric">Views</th>
				<th class="mdl-data-table__cell--non-numeric">Actions</th>
			</thead>
			<tbody>
			';

		// prepare searches for display.
		foreach ( $searches_array as $search ) {

			$nonce = wp_create_nonce( 'idx_search_delete_nonce' );

			echo '<tr class="search-row">';
			echo '<td class="mdl-data-table__cell--non-numeric"><a href="' . esc_url( $search->url ) . '" target="_blank">' . esc_html( $search->linkTitle ) . '</a></td>';
			echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( Carbon::parse( $search->created )->addHours( $offset )->toDayDateTimeString() ) . '</td>';
			echo '<td class="mdl-data-table__cell--non-numeric">' . esc_html( $search->timesViewed ) . '</td>';
			echo '<td class="mdl-data-table__cell--non-numeric">
						<a href="' . esc_url( admin_url( 'admin-ajax.php?action=idx_search_delete&ssid=' . $search->id . '&nonce=' . $nonce ) ) . '" id="delete-search-' . esc_attr( $search->id ) . '" class="delete-search" data-ssid="' . esc_attr( $search->id ) . '" data-nonce="' . esc_attr( $nonce ) . '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-search-' . esc_attr( $search->id ) . '">Delete Search</div></a>
						<a href="https://middleware.idxbroker.com/mgmt/addeditsavedlink.php?id=' . esc_attr( $search->id ) . '" id="edit-mw-' . esc_attr( $search->id ) . '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' . esc_attr( $search->id ) . '">Edit Search in Middleware</div></a>
						</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '<dialog id="dialog-search-delete">
				<form method="dialog">
					<h5>Delete Search</h5>
					<p>Are you sure you want to delete this search?</p>
					<button type="submit" value="no" autofocus>No</button>
					<button type="submit" value="yes">Yes</button>
				</form>
			</dialog>';
		echo '
			<a href="' . esc_url( admin_url( 'admin.php?page=edit-idx-search' ) ) . '" id="add-search" class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored mdl-shadow--2dp">
				<i class="material-icons">add</i>
				<div class="mdl-tooltip" data-mdl-for="add-search">Add New Search</div>
			</a>
			<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>
			';
	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	public function search_list_actions() {

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array( 'post_type' => null );

	}

	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
	* call on load-$hook
	*/
	public function search_edit_actions() {

		/* Create an empty post object for dumb plugins like soliloquy */
		global $post;
		$post = (object) array( 'post_type' => null );

	}

	/**
	 * Output edit search page
	 *
	 * @return void
	 */
	public function idx_searches_edit() {
		// Check that the user is logged in & has proper permissions.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		} elseif ( empty( $_GET['searchID'] ) && empty( $_GET['leadID'] ) ) { ?>

			<h3>Add Saved Search</h3>
			<form action="" method="post" id="add-search" class="add-search">
				<!-- Search form -->
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="pt">Property Type</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="pt" name="pt">
							<option value="all">All Property Types</option>
							<option value="sfr">Single Family Residential</option>
							<option value="com">Commercial</option>
							<option value="ld">Lots and Land</option>
							<option value="mfr">Multifamily Residential</option>
							<option value="rnt">Rentals</option>
						</select>
					</div>
				</div>
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="ccz">City, County or Zip</label>
					<div class="" style="width: 300px;">
						<select style="width: 300px;" class="" id="ccz" name="ccz" multiple="multiple">
							<?php self::ccz_select_list(); ?>
						</select>
					</div>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="lp" name="lp">
					<label class="mdl-textfield__label" for="lp">Price Min</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="hp" name="hp">
					<label class="mdl-textfield__label" for="hp">Price Max</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="bd" name="bd">
					<label class="mdl-textfield__label" for="bd">Beds</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="ba" name="ba">
					<label class="mdl-textfield__label" for="ba">Baths</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="sqft" name="sqft">
					<label class="mdl-textfield__label" for="sqft">Square Feet</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-third">
					<input class="mdl-textfield__input" type="text" id="acres" name="acres">
					<label class="mdl-textfield__label" for="acres">Acres</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label one-half">
					<input class="mdl-textfield__input" type="text" id="add" name="add">
					<label class="mdl-textfield__label" for="add">Max Days Listed</label>
				</div>

				<!-- SEO Settings -->
				<h5>SEO Settings</h5>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="pageTitle" name="pageTitle">
					<label class="mdl-textfield__label" for="pageTitle">Page Title Tag <span class="is-required">(required)</span></label>
				</div>
				<!--<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="linkName" name="linkName">
					<label class="mdl-textfield__label" for="linkName">Link URL (Name) <span class="is-required">(required)</span></label>
				</div>--><br />

				<div class="mdl-fieldgroup">
					<label for="useDescriptionMeta" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
						<input type="checkbox" id="useDescriptionMeta" name="useDescriptionMeta" class="mdl-switch__input" checked>
						<span class="mdl-switch__label">Use Meta Tags Description?</span>
					</label>
				</div>

				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label descriptionMeta">
					<textarea class="mdl-textfield__input" type="text" rows="3" id="descriptionMeta" name="descriptionMeta"></textarea>
					<label class="mdl-textfield__label" for="descriptionMeta">Meta Tags Description</label>
				</div><br />

				<div class="mdl-fieldgroup">
					<label for="useKeywordsMeta" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
						<input type="checkbox" id="useKeywordsMeta" name="useKeywordsMeta" class="mdl-switch__input" checked>
						<span class="mdl-switch__label">Use Meta Tags Keywords?</span>
					</label>
				</div>

				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label keywords">
					<textarea class="mdl-textfield__input" type="text" cols="3" id="keywords" name="keywords"></textarea>
					<label class="mdl-textfield__label" for="keywords">Meta Tags Keywords</label>
				</div>

				<!-- Link Settings -->
				<h5>Link Settings</h5>
				<div class="mdl-fieldgroup">
					<label for="featured" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
						<input type="checkbox" id="featured" name="featured" class="mdl-switch__input" checked>
						<span class="mdl-switch__label">Place on Custom Links Showcase?</span>
					</label>
				</div>

				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="linkTitle" name="linkTitle">
					<label class="mdl-textfield__label" for="linkTitle">Link Display Name <span class="is-required">(required)</span></label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<textarea class="mdl-textfield__input" type="text" cols="3" id="linkCopy" name="linkCopy"></textarea>
					<label class="mdl-textfield__label" for="linkCopy">Link Description</label>
				</div>

				<!-- Agent Assignment -->
				<h5>Agent Assignment</h5>
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="agentID">Assign an Agent to this link</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="agentID" name="agentID">
							<?php self::agents_select_list(); ?>
						</select>
					</div>
				</div>
				<br />

				<input type="hidden" name="action" value="idx_search_add" />
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-search" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_search_add_nonce' ) ); ?>" type="submit">Save Search</button>
				<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
				<div class="error-fail" style="display: none;">Saved Search addition failed. Check all required fields or try again later.</div>
				<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>

			</form>
			<?php
		} elseif ( ! empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'idx_lead_add_search_nonce' ) && ! empty( $_GET['leadID'] ) && is_numeric( $_GET['leadID'] ) ) {
			$lead_id = (int) sanitize_text_field( wp_unslash( $_GET['leadID'] ) );
			// Get Lead info.
			$lead = $this->idx_api->idx_api( 'lead/' . $lead_id, IDX_API_DEFAULT_VERSION, 'leads', array(), 60 * 2, 'GET', true );
			?>
			<h3>Add Lead Saved Search for <?php echo ( $lead['firstName'] ) ? esc_html( $lead['firstName'] ) : ''; ?> <?php echo ( $lead['lastName'] ) ? esc_html( $lead['lastName'] ) : ''; ?></h3>
			<form action="" method="post" id="add-lead-search" class="add-lead-search">
				<!-- Search form -->
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="pt">Property Type</label>
					<div class="mdl-selectfield">
						<select class="mdl-selectfield__select" id="pt" name="pt">
							<option value="all">All Property Types</option>
							<option value="sfr">Single Family Residential</option>
							<option value="com">Commercial</option>
							<option value="ld">Lots and Land</option>
							<option value="mfr">Multifamily Residential</option>
							<option value="rnt">Rentals</option>
						</select>
					</div>
				</div>
				<div class="mdl-fieldgroup">
					<label class="mdl-selectfield__label" for="ccz">City, County or Zip</label>
					<div class="" style="width: 300px;">
						<select style="width: 300px;" class="" id="ccz" name="ccz" multiple="multiple">
							<?php self::ccz_select_list(); ?>
						</select>
					</div>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="lp" name="lp">
					<label class="mdl-textfield__label" for="lp">Price Min</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="hp" name="hp">
					<label class="mdl-textfield__label" for="hp">Price Max</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="bd" name="bd">
					<label class="mdl-textfield__label" for="bd">Beds</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="ba" name="ba">
					<label class="mdl-textfield__label" for="ba">Baths</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="sqft" name="sqft">
					<label class="mdl-textfield__label" for="sqft">Square Feet</label>
				</div>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="acres" name="acres">
					<label class="mdl-textfield__label" for="acres">Acres</label>
				</div><br />
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="add" name="add">
					<label class="mdl-textfield__label" for="add">Max Days Listed</label>
				</div>

				<h5>Preferences</h5>
				<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
					<input class="mdl-textfield__input" type="text" id="searchName" name="searchName">
					<label class="mdl-textfield__label" for="searchName">Search Name</label>
				</div>
				<br />
				<div class="mdl-fieldgroup">
					<label for="receiveUpdates" class="mdl-switch mdl-js-switch mdl-js-ripple-effect">
						<input type="checkbox" id="receiveUpdates" name="receiveUpdates" class="mdl-switch__input" checked>
						<span class="mdl-switch__label">Receive property updates? Yes/No</span>
					</label>
				</div>
				<input type="hidden" id="leadID" name="leadID" value="<?php echo esc_attr( $lead_id ); ?>" />
				<br />

				<input type="hidden" name="action" value="idx_lead_search_add" />
				<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored add-lead-search" data-nonce="<?php echo esc_attr( wp_create_nonce( 'idx_lead_search_add_nonce' ) ); ?>" type="submit">Save Search</button>
				<div class="error-incomplete" style="display: none;">Please complete all required fields</div>
				<div class="error-fail" style="display: none;">Saved Search addition failed. Check all required fields or try again later.</div>
				<div class="mdl-spinner mdl-js-spinner mdl-spinner--single-color"></div>

			</form>
			<?php
		} elseif ( $_GET['searchID'] && is_numeric( $_GET['searchID'] ) ) {
			?>
			<h3>Edit Saved Search</h3>

			<?php
		}
	}

	/**
	 * Get CCZ's for combinedActiveMLS
	 * and output as options
	 */
	private function ccz_select_list() {
		$cities = $this->idx_api->idx_api( 'cities/combinedActiveMLS' );

		echo '<optgroup label="Cities" id="city" data-type="city">';
		foreach ( $cities as $city ) {
			echo '<option data-ccz="city" data-value="' . esc_attr( $city->id ) . '" value="' . esc_attr( $city->id ) . '">' . esc_html( $city->name ) . '</option>';
		}
		echo '</optgroup><optgroup label="Counties" id="county" data-type="county">';
		$counties = $this->idx_api->idx_api( 'counties/combinedActiveMLS' );
		foreach ( $counties as $county ) {
			echo '<option data-ccz="county" value="' . esc_attr( $county->id ) . '">' . esc_html( $county->name ) . '</option>';
		}
		echo '</optgroup><optgroup label="Postal Codes" id="postalcode" data-type="zipcode">';
		$zips = $this->idx_api->idx_api( 'postalcodes/combinedActiveMLS' );
		foreach ( $zips as $zip ) {
			echo '<option data-ccz="zipcode" value="' . esc_attr( $zip->id ) . '">' . esc_html( $zip->name ) . '</option>';
		}
		echo '</optgroup>';
	}

	/**
	 * Output Agents as select options.
	 */
	private function agents_select_list( $agent_id = null ) {
		$agents_array = $this->idx_api->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );

		if ( null !== $agent_id && ! is_wp_error( $agents_array ) ) {
			echo '<option value="0" ' . selected( $agent_id, '0', 0 ) . '>None</option>';
			foreach ( $agents_array['agent'] as $agent ) {
				echo '<option value="' . esc_attr( $agent['agentID'] ) . '" ' . selected( $agent_id, $agent['agentID'], 0 ) . '>' . esc_html( $agent['agentDisplayName'] ) . '</option>';
			}
		} elseif ( ! is_wp_error( $agents_array ) ) {
			echo '<option value="0">None</option>';
			if ( ! empty( $agents_array['agent'] ) ) {
				foreach ( $agents_array['agent'] as $agent ) {
					echo '<option value="' . esc_attr( $agent['agentID'] ) . '">' . esc_html( $agent['agentDisplayName'] ) . '</option>';
				}
			}
		}
	}
}
