<?php
/**
 * This file contains the methods for interacting with the IDX API
 * to import agent data
 */

if ( ! defined( 'ABSPATH' ) ) exit;
class IMPress_Agents_Import {

	public $_idx;

	public function __construct() {
	}

	public static function in_array($needle, $haystack, $strict = false) {
		if(!$haystack) return false;
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array($needle, $item, $strict))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Creates a post of employee type using post data from options page
	 * @param  array $agentIDs agentID of the property
	 * @return [type] $featured [description]
	 */
	public static function impress_agents_idx_create_post($agentIDs) {
		if(class_exists( 'IDX_Broker_Plugin')) {

			// Load IDX Broker API Class and retrieve agents
			$_idx_api = new \IDX\Idx_Api();
			$agents = $_idx_api->idx_api(
				'agents',
				$apiversion = '1.2.2',
				$level = 'clients',
				$params = array(),
				$expiration = 7200,
				$request_type = 'GET',
				$json_decode_type = true
			);

			// Load WP options
			$idx_agent_wp_options = get_option('impress_agents_idx_agent_wp_options');
			$impa_options = get_option('plugin_impress_agents_settings');

			foreach($agents as $agent) {

				foreach($agent as $a) {

					if(!in_array($a['agentID'], $agentIDs)) {
						$idx_agent_wp_options[$a['agentID']]['agentID'] = $a['agentID'];
						$idx_agent_wp_options[$a['agentID']]['status'] = '';
					}

					if(isset($idx_agent_wp_options[$a['agentID']]['post_id']) && !get_post($idx_agent_wp_options[$a['agentID']]['post_id'])) {
						unset($idx_agent_wp_options[$a['agentID']]['post_id']);
						unset($idx_agent_wp_options[$a['agentID']]['status']);
				 	}

					if(in_array($a['agentID'], $agentIDs) && !isset($idx_agent_wp_options[$a['agentID']]['post_id'])) {

						$opts = array(
							'post_content' => $a['bioDetails'],
							'post_title' => $a['agentDisplayName'],
							'post_status' => 'publish',
							'post_type' => 'employee'
						);
						$add_post = wp_insert_post($opts, true);
						if (is_wp_error($add_post)) {
							$error_string = $add_post->get_error_message();
							add_settings_error('impress_agents_idx_agent_settings_group', 'insert_post_failed', 'WordPress failed to insert the post. Error ' . $error_string, 'error');
							return;
						} elseif($add_post) {
							$idx_agent_wp_options[$a['agentID']]['post_id'] = $add_post;
							$idx_agent_wp_options[$a['agentID']]['status'] = 'publish';
							self::impress_agents_idx_insert_post_meta($add_post, $a);
						}
					}
					elseif( in_array($a['agentID'], $agentIDs) && $idx_agent_wp_options[$a['agentID']]['status'] != 'publish' ) {
						self::impress_agents_idx_change_post_status($idx_agent_wp_options[$a['agentID']]['post_id'], 'publish');
						$idx_agent_wp_options[$a['agentID']]['status'] = 'publish';
					}
					elseif( !in_array($a['agentID'], $agentIDs) && $idx_agent_wp_options[$a['agentID']]['status'] == 'publish' ) {

						// change to draft or delete agent if the post exists but is not in the agent array based on settings
						if(isset($impa_options['impress_agents_idx_remove']) && $impa_options['impress_agents_idx_remove'] == 'remove-draft') {

							// Change to draft
							self::impress_agents_idx_change_post_status($idx_agent_wp_options[$a['agentID']]['post_id'], 'draft');
							$idx_agent_wp_options[$a['agentID']]['status'] = 'draft';
						} elseif(isset($impa_options['impress_agents_idx_remove']) && $impa_options['impress_agents_idx_remove'] == 'remove-delete') {

							$idx_agent_wp_options[$a['agentID']]['status'] = 'deleted';

							// Delete featured image
							$post_featured_image_id = get_post_thumbnail_id( $idx_agent_wp_options[$a['agentID']]['post_id'] );
							wp_delete_attachment( $post_featured_image_id );

							//Delete post
							wp_delete_post( $idx_agent_wp_options[$a['agentID']]['post_id'] );
						}
					}
				}
			}
			update_option('impress_agents_idx_agent_wp_options', $idx_agent_wp_options);
			return $idx_agent_wp_options;
		}
	}

	/**
	 * Update existing post
	 * @return true if success
	 */
	public static function impress_agents_update_post() {

		// Load IDX Broker API Class and retrieve agents
		$_idx_api = new \IDX\Idx_Api();
		$agents = $_idx_api->idx_api(
			'agents',
			$apiversion = '1.2.2',
			$level = 'clients',
			$params = array(),
			$expiration = 7200,
			$request_type = 'GET',
			$json_decode_type = true
		);

		// Load WP options
		$idx_agent_wp_options = get_option('impress_agents_idx_agent_wp_options');
		$impa_options = get_option('plugin_impress_agents_settings');

		foreach ( $agents as $agent ) {
			foreach($agent as $a) {

				if( isset($idx_agent_wp_options[$a['agentID']]['post_id']) ) {
					// Update agent data
					if(!isset($impa_options['impress_agents_idx_update']) || isset($impa_options['impress_agents_idx_update']) && $impa_options['impress_agents_idx_update'] != 'update-none')
						self::impress_agents_idx_insert_post_meta($idx_agent_wp_options[$a['agentID']]['post_id'], $a, true, false );
					$idx_agent_wp_options[$a['agentID']]['updated'] = date("m/d/Y h:i:sa");
				}
			}

		}

		update_option('impress_agents_idx_agent_wp_options', $idx_agent_wp_options);

	}

	/**
	 * Change post status
	 * @param  [type] $post_id [description]
	 * @param  [type] $status  [description]
	 * @return [type]          [description]
	 */
	public static function impress_agents_idx_change_post_status($post_id, $status){
	    $current_post = get_post( $post_id, 'ARRAY_A' );
	    $current_post['post_status'] = $status;
	    wp_update_post($current_post);
	}

	/**
	 * Inserts post meta based on property data
	 * API fields are mapped to post meta fields
	 * prefixed with _employee_ and lowercased
	 * @param  [type] $id  [description]
	 * @return [type]      [description]
	 */
	public static function impress_agents_idx_insert_post_meta($id, $idx_agent_data, $update = false, $update_image = true) {

		// Add or reset taxonomies terms for job-types = agentTitle
		wp_set_object_terms($id, $idx_agent_data['agentTitle'], 'job-types');

		// Add post meta for existing fields
		if(get_post_meta($id, '_employee_title') == false) { update_post_meta($id, '_employee_title', $idx_agent_data['agentTitle']); }
		if(get_post_meta($id, '_employee_first_name') == false) { update_post_meta($id, '_employee_first_name', $idx_agent_data['agentFirstName']); }
		if(get_post_meta($id, '_employee_last_name') == false) { update_post_meta($id, '_employee_last_name', $idx_agent_data['agentLastName']); }
		if(get_post_meta($id, '_employee_agent_id') == false) { update_post_meta($id, '_employee_agent_id', $idx_agent_data['agentID']); }
		if(get_post_meta($id, '_employee_phone') == false) { update_post_meta($id, '_employee_phone', $idx_agent_data['agentContactPhone']); }
		if(get_post_meta($id, '_employee_mobile') == false) { update_post_meta($id, '_employee_mobile', $idx_agent_data['agentCellPhone']); }
		if(get_post_meta($id, '_employee_email') == false) { update_post_meta($id, '_employee_email', $idx_agent_data['agentEmail']); }
		if(get_post_meta($id, '_employee_website') == false) { update_post_meta($id, '_employee_website', $idx_agent_data['agentURL']); }
		if(get_post_meta($id, '_employee_address') == false) { update_post_meta($id, '_employee_address', $idx_agent_data['address']); }
		if(get_post_meta($id, '_employee_city') == false) { update_post_meta($id, '_employee_city', $idx_agent_data['city']); }
		if(get_post_meta($id, '_employee_state') == false) { update_post_meta($id, '_employee_state', $idx_agent_data['stateProvince']); }
		if(get_post_meta($id, '_employee_zip') == false) { update_post_meta($id, '_employee_zip', $idx_agent_data['zipCode']); }

		foreach ($idx_agent_data as $metakey => $metavalue) {
			if(isset($metavalue) && !is_array($metavalue) && $metavalue != '') {
				if(get_post_meta($id, '_employee_' . strtolower($metakey)) == false) {
					update_post_meta($id, '_employee_' . strtolower($metakey), $metavalue);
				}
			} elseif(isset( $metavalue ) && is_array( $metavalue )) {
				foreach ($metavalue as $key => $value) {
					if(get_post_meta($id, '_employee_' . strtolower($metakey))) {
						$oldvalue = get_post_meta($id, '_employee_' . strtolower($metakey), true);
						$newvalue = $value . ', ' . $oldvalue;
						update_post_meta($id, '_employee_' . strtolower($metakey), $newvalue);
					} else {
						update_post_meta($id, '_employee_' . strtolower($metakey), $value);
					}
				}
			}
		}

		/**
		 * Pull featured image if it's not an update or update image is set to true
		 */
		$featured_image = $idx_agent_data['agentPhotoURL'];

		if(isset($featured_image) && $featured_image != null) {
			if($update == false || $update_image == true) {
				// Delete previously attached image
				$post_featured_image_id = get_post_thumbnail_id( $id );
				wp_delete_attachment( $post_featured_image_id );

				// Add Featured Image to Post
				$image_url  = $featured_image; // Define the image URL here
				$upload_dir = wp_upload_dir(); // Set upload folder
				$image_data = file_get_contents($image_url); // Get image data
				$filename   = basename(sanitize_file_name(strtolower( $idx_agent_data['agentDisplayName'] )) . '.jpg'); // Create image file name

				// Check folder permission and define file location
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}

				// Create the image file on the server
				if(!file_exists($file))
					file_put_contents( $file, $image_data );

				// Check image file type
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => $idx_agent_data['agentDisplayName'] . ' - ' . $idx_agent_data['agentID'],
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Create the attachment
				$attach_id = wp_insert_attachment( $attachment, $file, $id );

				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );

				// Assign featured image to post
				set_post_thumbnail( $id, $attach_id );
			}

			return true;
		}
	}
}


/**
 * Admin settings page
 * Outputs cleints/agents api data to import
 * Enqueues scripts for display
 * Deletes post and post thumbnail via ajax
 */
add_action( 'admin_menu', 'impress_agents_idx_agent_register_menu_page');
function impress_agents_idx_agent_register_menu_page() {
	add_submenu_page( 'edit.php?post_type=employee', __( 'Import Agents', 'impress_agents' ), __( 'Import Agents', 'impress_agents' ), 'manage_options', 'impa-idx-agent', 'impress_agents_idx_agent_setting_page' );
	add_action( 'admin_init', 'impress_agents_idx_agent_register_settings' );
}

function impress_agents_idx_agent_register_settings() {
	register_setting('impress_agents_idx_agent_settings_group', 'impress_agents_idx_agent_options', array('IMPress_Agents_Import', 'impress_agents_idx_create_post'));
}

add_action( 'admin_enqueue_scripts', 'impress_agents_idx_agent_scripts' );
function impress_agents_idx_agent_scripts() {
	$screen = get_current_screen();
	if($screen->id != 'employee_page_impa-idx-agent')
		return;

	wp_enqueue_script( 'impress_agents_idx_agent_delete_script', IMPRESS_AGENTS_URL . 'includes/js/admin-agent-import.js', array( 'jquery' ), true );
	wp_enqueue_script( 'jquery-masonry' );
	wp_enqueue_script( 'images-loaded', 'https://unpkg.com/imagesloaded@4.1/imagesloaded.pkgd.min.js' );
	wp_localize_script( 'impress_agents_idx_agent_delete_script', 'DeleteAgentAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_style( 'impress_agents_idx_agent_style', IMPRESS_AGENTS_URL . 'includes/css/impress-agents-import.css' );
}
add_action( 'wp_ajax_impa_idx_agent_delete', 'impa_idx_agent_delete' );
function impa_idx_agent_delete(){

	$permission = check_ajax_referer( 'impa_idx_agent_delete_nonce', 'nonce', false );
	if( $permission == false ) {
		echo 'error';
	}
	else {
		// Delete featured image
		$post_featured_image_id = get_post_thumbnail_id( $_REQUEST['id'] );
		wp_delete_attachment( $post_featured_image_id );

		//Delete post
		wp_delete_post( $_REQUEST['id'] );
		echo 'success';
	}
	die();
}

function impress_agents_idx_agent_setting_page() {
	?>
			<h1>Import Agents</h1>
			<p>Select the agents to import.</p>
			<form id="impa-idx-agent-import" method="post" action="options.php">
				<label for="selectall"><input type="checkbox" id="selectall"/>Select/Deselect All<br/><em>If importing all agents, it may take some time. <strong class="error">Please be patient.</strong></em></label>
				<?php submit_button('Import Agents'); ?>

			<?php
			// Show popup if IDX Broker plugin not active or installed
			if( !class_exists( 'IDX_Broker_Plugin') ) {
				echo 'You must have the IMPress for IDX Broker plugin and an active IDX Broker account to import agents.';
				// thickbox like content
				// echo '
				// 	<img class="idx-import bkg" src="' . IMPRESS_AGENTS_URL . 'images/import-bg.jpg' . '" /></a>
				// 	<div class="idx-import thickbox">
				// 	     <a href="http://www.idxbroker.com/features/idx-wordpress-plugin" target="_blank"><img src="' . IMPRESS_AGENTS_URL . 'images/idx-ad.png' . '" alt="Sign up for IDX now!"/></a>
				// 	</div>';

				return;
			}

			settings_errors('impress_agents_idx_agent_settings_group');
			?>

			<ol id="selectable" class="grid">
			<div class="grid-sizer"></div>

			<?php
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$plugin_data = get_plugins();

			// Get agents from IDX Broker plugin
			if (class_exists( 'IDX_Broker_Plugin' )) {
				// bail if IDX plugin version is not at least 2.0
				if($plugin_data['idx-broker-platinum/idx-broker-platinum.php']['Version'] < 2.0 ) {
					add_settings_error('impress_agents_idx_agent_settings_group', 'idx_agent_update', 'You must update to <a href="' . admin_url( 'update-core.php' ) . '">IMPress for IDX Broker</a> version 2.0.0 or higher to import listings.', 'error');
					settings_errors('impress_agents_idx_agent_settings_group');
					return;
				}

				$_idx_api = new \IDX\Idx_Api();
				$agents = $_idx_api->idx_api(
					'agents',
					$apiversion = '1.2.2',
					$level = 'clients',
					$params = array(),
					$expiration = 7200,
					$request_type = 'GET',
					$json_decode_type = true
				);
				//$agents = $_idx_api->idx_api('agents');
			} else {
				return;
			}

			$idx_agent_wp_options = get_option('impress_agents_idx_agent_options');

			settings_fields( 'impress_agents_idx_agent_settings_group' );
			do_settings_sections( 'impress_agents_idx_agent_settings_group' );

			// No agents found
			if(!$agents) {
				echo 'No agents found.';
				return;
			}

			// Loop through agents
			foreach ($agents as $agent) {
				foreach ($agent as $a) {

					if(!isset($idx_agent_wp_options[$a['agentID']]['post_id']) || !get_post($idx_agent_wp_options[$a['agentID']]['post_id']) ) {
						$idx_agent_wp_options[$a['agentID']] = array(
							'agentID' => $a['agentID']
							);
					}

					if(isset($idx_agent_wp_options[$a['agentID']]['post_id']) && get_post($idx_agent_wp_options[$a['agentID']]['post_id']) ) {
						$pid = $idx_agent_wp_options[$a['agentID']]['post_id'];
						$nonce = wp_create_nonce('impa_idx_agent_delete_nonce');
						$delete_agent = sprintf('<a href="%s" data-id="%s" data-nonce="%s" class="delete-post">Delete</a>',
							admin_url( 'admin-ajax.php?action=impa_idx_agent_delete&id=' . $pid . '&nonce=' . $nonce),
								$pid,
								$nonce
						 );
					}

					printf('<div class="grid-item post"><label for="%s" class="idx-agent"><li class="%s agent"><img class="agent" src="%s"><input type="checkbox" id="%s" class="checkbox" name="impress_agents_idx_agent_options[]" value="%s" %s /><p><span class="agent-name">%s</span><br/><span class="agent-title">%s</span><br/><span class="agent-phone">%s</span><br/><span class="agent-id">Agent ID: %s</span></p><div class="controls">%s %s</div></li></label></div>',
						$a['agentID'],
						isset($idx_agent_wp_options[$a['agentID']]['status']) ? ($idx_agent_wp_options[$a['agentID']]['status'] == 'publish' ? "imported" : '') : '',
						isset($a['agentPhotoURL']) && $a['agentPhotoURL'] != '' ? $a['agentPhotoURL'] : IMPRESS_AGENTS_URL . 'images/impress-agents-nophoto.png',
						$a['agentID'],
						$a['agentID'],
						isset($idx_agent_wp_options[$a['agentID']]['status']) ? ($idx_agent_wp_options[$a['agentID']]['status'] == 'publish' ? "checked" : '') : '',
						$a['agentDisplayName'],
						$a['agentTitle'],
						isset($a['agentContactPhone']) ? $a['agentContactPhone'] : '',
						$a['agentID'],
						isset($idx_agent_wp_options[$a['agentID']]['status']) ? ($idx_agent_wp_options[$a['agentID']]['status'] == 'publish' ? "<span class='imported'>Imported</span>" : '') : '',
						isset($idx_agent_wp_options[$a['agentID']]['status']) ? ($idx_agent_wp_options[$a['agentID']]['status'] == 'publish' ? $delete_agent : '') : ''
						);

				}

			}
			echo '</ol>';
			submit_button('Import Agents');
			?>
			</form>
	<?php
}

/**
 * Check if update is scheduled - if not, schedule it to run twice daily.
 * Only add if IDX plugin is installed
 * @since 2.0
 */
if( class_exists( 'IDX_Broker_Plugin') ) {
	add_action( 'admin_init', 'impress_agents_idx_update_schedule' );
}
function impress_agents_idx_update_schedule() {
	if ( ! wp_next_scheduled( 'impress_agents_idx_update' ) ) {
		wp_schedule_event( time(), 'daily', 'impress_agents_idx_update');
	}
}
/**
 * On the scheduled update event, run impress_agents_update_post with activation status
 *
 * @since 2.0
 */
add_action( 'impress_agents_idx_update', array('IMPress_Agents_Import', 'impress_agents_update_post') );
