<?php
/**
 * REST API: Social_Pro_Settings
 *
 * Adds routes for the Social Pro settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for Social Pro page routes.
 *
 * Supports GET and POST requests that return/set the Social Pro settings.
 */
class Social_Pro_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/social-pro' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'social_pro_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/social-pro' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'social_pro_enabled' ],
				'args'                => [
					'autopublish'        => [
						'type' => 'string',
					],
					'postDay'            => [
						'type' => 'string',
					],
					'postType'           => [
						'type' => 'string',
					],
					'selectedAuthor'     => [
						'type' => 'number',
					],
					'selectedCategories' => [
						'type' => 'array',
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
		$defaults = [
			'autopublish' => 'autopublish',
			'post_day'    => 'tuesday',
			'post_type'   => 'post',
			'categories'  => [],
			'author'      => get_current_user_id(),
		];
		$existing = get_option( 'idx_broker_social_pro_settings', [] );
		$settings = array_merge( $defaults, $existing );

		return rest_ensure_response(
			[
				'autopublish'        => $settings['autopublish'],
				'postDay'            => $settings['post_day'],
				'postType'           => $settings['post_type'],
				'selectedAuthor'     => $settings['author'],
				'authors'            => $this->get_authors(),
				'selectedCategories' => $settings['categories'],
				'categories'         => $this->get_categories(),
			]
		);
	}

	/**
	 * POST request
	 *
	 * @param string $payload Settings to update.
	 * @return WP_REST_Response
	 */
	public function post( $payload ) {
		$settings = get_option( 'idx_broker_social_pro_settings', [] );

		if ( isset( $payload['autopublish'] ) ) {
			$settings['autopublish'] = $payload['autopublish'];
		}

		if ( isset( $payload['postDay'] ) ) {
			$settings['post_day'] = $payload['postDay'];
		}

		if ( isset( $payload['postType'] ) ) {
			$settings['post_type'] = $payload['postType'];
		}

		if ( isset( $payload['selectedAuthor'] ) ) {
			$settings['author'] = $payload['selectedAuthor'];
		}

		if ( isset( $payload['selectedCategories'] ) ) {
			$settings['categories'] = $payload['selectedCategories'];
		}

		update_option( 'idx_broker_social_pro_settings', $settings );

		$social_pro = new \IDX\Social_Pro();
		$social_pro->setup_cron( true );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Get array of WordPress authors.
	 *
	 * @return array
	 */
	private function get_authors() {
		$users = get_users();
		return array_map(
			function ( $user ) {
				return [
					'value' => $user->id,
					'label' => $user->display_name,
				];
			},
			$users
		);
	}

	/**
	 * Get array of WordPress categories.
	 *
	 * @return array
	 */
	private function get_categories() {
		$categories = get_categories(
			[
				'hide_empty' => 0,
			]
		);
		return array_map(
			function ( $category ) {
				return [
					'value' => $category->term_id,
					'label' => $category->name,
				];
			},
			$categories
		);
	}
}

new Social_Pro_Settings();
