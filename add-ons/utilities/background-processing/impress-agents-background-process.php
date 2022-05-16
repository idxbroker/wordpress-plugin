<?php

require_once IMPRESS_IDX_DIR . 'add-ons/utilities/background-processing/wp-background-processing/wp-background-processing.php';

/**
 * IMPress Agents Import Process
 */
class IMPress_Agents_Import_Process extends WP_Background_Process {

	/**
	 * Protected ID that is single title (for working with super::)
	 *
	 * @var protected 'background-processing-agents'
	 */
	protected $action = 'background-processing-agents';

	/**
	 * Task to be run each iteration
	 *
	 * @param  string $data     Information of listing to be imported.
	 * @return mixed                False if done, $data if to be re-run.
	 */
	protected function task( $data ) {
		$agent_post = wp_insert_post( $data['post_data'], true );

		if ( ! is_wp_error( $agent_post ) ) {
			IMPress_Agents_Import::impress_agents_idx_insert_post_meta( $agent_post, $data['meta_data'] );
		}
		return false;
	}

	/**
	 * Runs on job completion
	 */
	protected function complete() {
		parent::complete();
		die();
	}
}

new IMPress_Agents_Import_Process();
