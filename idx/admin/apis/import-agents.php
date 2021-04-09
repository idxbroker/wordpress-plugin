<?php
/**
 * REST API: Import_Agents
 *
 * Adds routes for the agent import page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for agent import page routes.
 *
 * Supports GET and POST requests for agent import operations.
 */
class Import_Agents extends \IDX\Admin\Rest_Controller {

	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'import/agents' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'agents_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'import/agents/import' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'agents_enabled' ],
				'args'                => [
					'ids' => [
						'required' => true,
						'type'     => 'array',
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'import/agents/delete' ),
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'agents_enabled' ],
				'args'                => [
					'ids' => [
						'required' => true,
						'type'     => 'array',
					],
				],
			]
		);
	}

	/**
	 * GET request
	 *
	 * @return WP_REST_Response
	 */
	public function get() {
		if ( get_site_transient( 'wp_background-processing-agents_process_lock' ) ) {
			return rest_ensure_response(
				[
					'inProgress' => true,
				]
			);
		}

		$agent_import_lists = $this->generate_agent_import_lists();

		return rest_ensure_response(
			[
				'inProgress' => false,
				'imported'   => array_values( $agent_import_lists['imported'] ),
				'unimported' => array_values( $agent_import_lists['unimported'] ),
			]
		);
	}

	/**
	 * POST request
	 *
	 * @param [mixed] $payload - request parameters.
	 * @return WP_REST_Response
	 */
	public function post( $payload ) {

		$selected_ids = [];
		if ( ! empty( $payload['ids'] ) && is_array( $payload['ids'] ) ) {
			$selected_ids = filter_var_array( $payload['ids'], FILTER_SANITIZE_STRING );
		}

		$agent_importer = new \IMPress_Agents_Import();
		$agent_importer->impress_agents_idx_create_post( $selected_ids );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * DELETE request
	 *
	 * @param [mixed] $payload - request parameters.
	 * @return WP_REST_Response
	 */
	public function delete( $payload ) {

		$selected_ids = [];
		if ( ! empty( $payload['ids'] ) && is_array( $payload['ids'] ) ) {
			$selected_ids = filter_var_array( $payload['ids'], FILTER_SANITIZE_NUMBER_INT );
		}

		foreach ( $selected_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( ! is_wp_error( $post ) && ! empty( $post->post_type ) && 'employee' === $post->post_type ) {

				if ( has_post_thumbnail( $post->ID ) ) {
					$image_id = get_post_thumbnail_id( $post->ID );
					wp_delete_attachment( $image_id, true );
				}
				wp_delete_post( $post->ID );
			}
		}

		$agent_import_lists = $this->generate_agent_import_lists();

		return rest_ensure_response(
			[
				'imported'   => array_values( $agent_import_lists['imported'] ),
				'unimported' => array_values( $agent_import_lists['unimported'] ),
			]
		);
	}

	/**
	 * Generate_Agent_Import_Lists
	 * Helper method to generate imported/unimported agent lists.
	 *
	 * @return array
	 */
	public function generate_agent_import_lists() {
		$imported        = [];
		$unimported      = [];

		$agent_posts = new \WP_Query(
			[
				'post_type'      => 'employee',
				'posts_per_page' => -1,
			]
		);

		if ( is_array( $agent_posts->posts ) ) {
			foreach ( $agent_posts->posts as $post ) {
				$post_meta = get_post_meta( $post->ID );
				if ( ! empty( $post_meta['_employee_agentid'][0] ) ) {
					$imported[ $post_meta['_employee_agentid'][0] ] = [
						'agentId' => $post_meta['_employee_agentid'][0],
						'name'    => $post_meta['_employee_agentdisplayname'][0],
						'title'   => $post_meta['_employee_title'][0],
						'image'   => $post_meta['_employee_agentphotourl'][0],
						'email'   => $post_meta['_employee_email'][0],
						'postId'  => $post->ID,
					];
				}
			}
		}

		$unimported = $this->get_agents_from_idxb();

		if ( is_array( $unimported ) ) {
			foreach ( $unimported as $id => $agent ) {
				if ( array_key_exists( $id, $imported ) ) {
					unset( $unimported[ $id ] );
				}
			}

		}

		return [
			'imported'   => $imported,
			'unimported' => $unimported,
		];

	}

	/**
	 * Get_Agents_from_IDXB
	 * Helper method to gather agent data from the IDXB API.
	 *
	 * @return array
	 */
	public function get_agents_from_idxb() {
		$idx_api   = new \IDX\Idx_Api();
		$idxb_data = $idx_api->idx_api(
			'agents',
			'1.7.0',
			'clients',
			[],
			7200,
			'GET',
			true
		);
		$agents    = [];

		if ( ! is_array( $idxb_data['agent'] ) ) {
			return $agents;
		}

		foreach ( $idxb_data['agent'] as $lead ) {
			$agents[ $lead['agentID'] ] = [
				'agentId' => $lead['agentID'],
				'name'    => $lead['agentDisplayName'],
				'title'   => $lead['agentTitle'],
				'image'   => $lead['agentPhotoURL'],
				'email'   => $lead['agentEmail'],
			];
		}
		return $agents;
	}

}

new Import_Agents();
