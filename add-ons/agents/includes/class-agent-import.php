<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IMPRESS_IDX_DIR . 'add-ons/utilities/background-processing/impress-agents-background-process.php';

/**
 * IMPress Agents Import
 */
class IMPress_Agents_Import {

	/**
	 * Creates a post of employee type using post data from options page
	 *
	 * @param  array $agent_ids agentID of the property.
	 * @return [type] $featured [description]
	 */
	public static function impress_agents_idx_create_post( $agent_ids ) {

		// Load IDX Broker API Class and retrieve agents.
		$idx    = new \IDX\Idx_Api();
		$agents = $idx->idx_api(
			'agents',
			'1.7.0',
			'clients',
			[],
			7200,
			'GET',
			true
		);

		// If no agent data is returned from IDXB, return early.
		if ( ! is_array( $agents['agent'] ) ) {
			return;
		}

		// Find already imported agents.
		$imported_agents = [];
		$agent_posts     = new \WP_Query(
			[
				'post_type'      => 'employee',
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'pending', 'draft', 'private' ],
			]
		);

		if ( is_array( $agent_posts->posts ) ) {
			foreach ( $agent_posts->posts as $post ) {
				$post_meta = get_post_meta( $post->ID );
				if ( ! empty( $post_meta['_employee_agentid'][0] ) ) {
					array_push( $imported_agents, $post_meta['_employee_agentid'][0] );
				}
			}
		}

		$background_process = new IMPress_Agents_Import_Process();

		// Loop through agents and import data.
		foreach ( $agents['agent'] as $agent ) {
			if ( in_array( $agent['agentID'], $imported_agents ) || ! in_array( $agent['agentID'], $agent_ids ) ) {
				continue;
			}

			$import_data = [];

			$import_data['post_data'] = [
				'post_content' => $agent['bioDetails'],
				'post_title'   => $agent['agentDisplayName'],
				'post_status'  => 'publish',
				'post_type'    => 'employee',
			];

			$import_data['meta_data'] = $agent;

			$background_process->push_to_queue( $import_data );

		}

		$background_process->save()->dispatch();
	}

	/**
	 * Update existing post
	 *
	 * @return true if success
	 */
	public static function impress_agents_update_post() {

		// Load IDX Broker API Class and retrieve agents.
		$idx    = new \IDX\Idx_Api();
		$agents = $idx->idx_api(
			'agents',
			'1.7.0',
			'clients',
			[],
			7200,
			'GET',
			true
		);

		if ( is_wp_error( $agents ) || empty( $agents['agent'] ) ) {
			return;
		}

		$imported_agents = [];
		$agent_posts     = new \WP_Query(
			[
				'post_type'      => 'employee',
				'posts_per_page' => -1,
			]
		);

		if ( is_array( $agent_posts->posts ) ) {
			foreach ( $agent_posts->posts as $post ) {
				$post_meta = get_post_meta( $post->ID );
				if ( ! empty( $post_meta['_employee_agentid'][0] ) ) {
					array_push( $imported_agents, [ $post_meta['_employee_agentid'][0] => $post->ID ] );
				}
			}
		}
		// Loop through agents and update any that are already imported.
		foreach ( $agents['agent'] as $agent ) {
			if ( ! empty( $imported_agents[ $agent['agentID'] ] ) ) {
				self::impress_agents_idx_insert_post_meta( $imported_agents[ $agent['agentID'] ], $agent, true, false );
			}
		}

	}

	/**
	 * Inserts post meta based on property data
	 * API fields are mapped to post meta fields
	 * prefixed with _employee_ and lowercased
	 *
	 * @param  [int]   $id - Post ID.
	 * @param  [array] $idx_agent_data - Agent data from IDXB.
	 * @param  [bool]  $update - Update existing agent flag.
	 * @param  [bool]  $update_image - Update image flag.
	 * @return [bool]
	 */
	public static function impress_agents_idx_insert_post_meta( $id, $idx_agent_data, $update = false, $update_image = true ) {

		// Add or reset taxonomies terms for job-types = agentTitle.
		wp_set_object_terms( $id, $idx_agent_data['agentTitle'], 'job-types' );

		// Add post meta for existing fields.
		// Title.
		if ( get_post_meta( $id, '_employee_title' ) == false ) {
			update_post_meta( $id, '_employee_title', $idx_agent_data['agentTitle'] );
		}
		// First Name.
		if ( get_post_meta( $id, '_employee_first_name' ) == false ) {
			update_post_meta( $id, '_employee_first_name', $idx_agent_data['agentFirstName'] );
		}
		// Last Name.
		if ( get_post_meta( $id, '_employee_last_name' ) == false ) {
			update_post_meta( $id, '_employee_last_name', $idx_agent_data['agentLastName'] );
		}
		// Agent ID.
		if ( get_post_meta( $id, '_employee_agent_id' ) == false ) {
			update_post_meta( $id, '_employee_agent_id', $idx_agent_data['agentID'] );
		}
		// Main Phone.
		if ( get_post_meta( $id, '_employee_phone' ) == false ) {
			update_post_meta( $id, '_employee_phone', $idx_agent_data['agentContactPhone'] );
		}
		// Cell Phone.
		if ( get_post_meta( $id, '_employee_mobile' ) == false ) {
			update_post_meta( $id, '_employee_mobile', $idx_agent_data['agentCellPhone'] );
		}
		// Email.
		if ( get_post_meta( $id, '_employee_email' ) == false ) {
			update_post_meta( $id, '_employee_email', $idx_agent_data['agentEmail'] );
		}
		// Website URL.
		if ( get_post_meta( $id, '_employee_website' ) == false ) {
			update_post_meta( $id, '_employee_website', $idx_agent_data['agentURL'] );
		}
		// Street Address.
		if ( get_post_meta( $id, '_employee_address' ) == false ) {
			update_post_meta( $id, '_employee_address', $idx_agent_data['address'] );
		}
		// City.
		if ( get_post_meta( $id, '_employee_city' ) == false ) {
			update_post_meta( $id, '_employee_city', $idx_agent_data['city'] );
		}
		// State.
		if ( get_post_meta( $id, '_employee_state' ) == false ) {
			update_post_meta( $id, '_employee_state', $idx_agent_data['stateProvince'] );
		}
		// Zip Code.
		if ( get_post_meta( $id, '_employee_zip' ) == false ) {
			update_post_meta( $id, '_employee_zip', $idx_agent_data['zipCode'] );
		}

		foreach ( $idx_agent_data as $metakey => $metavalue ) {
			if ( isset( $metavalue ) && ! is_array( $metavalue ) && $metavalue != '' ) {
				if ( get_post_meta( $id, '_employee_' . strtolower( $metakey ) ) == false ) {
					update_post_meta( $id, '_employee_' . strtolower( $metakey ), $metavalue );
				}
			} elseif ( isset( $metavalue ) && is_array( $metavalue ) ) {
				foreach ( $metavalue as $key => $value ) {
					if ( get_post_meta( $id, '_employee_' . strtolower( $metakey ) ) ) {
						$oldvalue = get_post_meta( $id, '_employee_' . strtolower( $metakey ), true );
						$newvalue = $value . ', ' . $oldvalue;
						update_post_meta( $id, '_employee_' . strtolower( $metakey ), $newvalue );
					} else {
						update_post_meta( $id, '_employee_' . strtolower( $metakey ), $value );
					}
				}
			}
		}

		/**
		 * Pull featured image if it's not an update or update image is set to true
		 */
		$featured_image = $idx_agent_data['agentPhotoURL'];

		if ( isset( $featured_image ) && $featured_image != null ) {
			if ( $update == false || $update_image == true ) {
				// Delete previously attached image.
				$post_featured_image_id = get_post_thumbnail_id( $id );
				wp_delete_attachment( $post_featured_image_id );

				// Add Featured Image to Post.
				$image_url  = $featured_image; // Define the image URL here.
				$upload_dir = wp_upload_dir(); // Set upload folder.
				$image_data = file_get_contents( $image_url ); // Get image data.
				$filename   = basename( sanitize_file_name( strtolower( $idx_agent_data['agentDisplayName'] ) ) . '.jpg' ); // Create image file name.

				// Check folder permission and define file location.
				if ( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}

				// Create the image file on the server.
				if ( ! file_exists( $file ) ) {
					file_put_contents( $file, $image_data );
				}

				// Check image file type.
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data.
				$attachment = [
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => $idx_agent_data['agentDisplayName'] . ' - ' . $idx_agent_data['agentID'],
					'post_content'   => '',
					'post_status'    => 'inherit',
				];

				// Create the attachment.
				$attach_id = wp_insert_attachment( $attachment, $file, $id );

				// Include image.php.
				require_once ABSPATH . 'wp-admin/includes/image.php';

				// Define attachment metadata.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				// Assign metadata to attachment.
				wp_update_attachment_metadata( $attach_id, $attach_data );

				// Assign featured image to post.
				set_post_thumbnail( $id, $attach_id );
			}

			return true;
		}
	}
}

/**
 * Check if update is scheduled - if not, schedule it to run twice daily.
 * Only add if IDX plugin is installed
 *
 * @since 2.0
 */
add_action( 'admin_init', 'impress_agents_idx_update_schedule' );

/**
 * IMPress Agents IDX Update Schedule
 * Schedules agent update task.
 *
 * @return void
 */
function impress_agents_idx_update_schedule() {
	if ( ! wp_next_scheduled( 'impress_agents_idx_update' ) ) {
		wp_schedule_event( time(), 'daily', 'impress_agents_idx_update' );
	}
}

/**
 * On the scheduled update event, run impress_agents_update_post with activation status
 *
 * @since 2.0
 */
add_action( 'impress_agents_idx_update', [ 'IMPress_Agents_Import', 'impress_agents_update_post' ] );
