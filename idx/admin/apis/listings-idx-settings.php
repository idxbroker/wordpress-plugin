<?php
/**
 * REST API: Listings_IDX_Settings
 *
 * Adds routes for the listings idx settings page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for settings/listings/idx page routes.
 *
 * Supports GET and POST requests that return/set the listings IDX settings.
 */
class Listings_IDX_Settings extends \IDX\Admin\Rest_Controller {
	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/idx' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'settings/listings/idx' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
				'args'                => [
					'updateListings'                 => [
						'type' => 'string',
					],
					'soldListings'                   => [
						'type' => 'string',
					],
					'automaticImport'                => [
						'type' => 'boolean',
					],
					'importedListingsAuthorSelected' => [
						'type' => 'integer',
					],
					'defaultListingTemplateSelected' => [
						'type' => 'string',
					],
					'displayIDXLink'                 => [
						'type' => 'boolean',
					],
					'importTitle'                    => [
						'type' => 'string',
					],
					'advancedFieldData'              => [
						'type' => 'boolean',
					],
					'displayAdvancedFields'          => [
						'type' => 'boolean',
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
			'wp_listings_idx_update'              => 'update-all',
			'wp_listings_idx_sold'                => 'sold-keep',
			'wp_listings_auto_import'             => 0,
			'wp_listings_default_template'        => '',
			'wp_listings_display_idx_link'        => 0,
			'wp_listings_import_author'           => 0,
			'wp_listings_import_title'            => '{{address}}',
			'wp_listings_import_advanced_fields'  => 0,
			'wp_listings_display_advanced_fields' => 0,
		];
		$existing = get_option( 'plugin_wp_listings_settings', [] );
		$settings = array_merge( $defaults, $existing );

		$this->generate_template_list();
		return rest_ensure_response(
			[
				'updateListings'                 => $settings['wp_listings_idx_update'],
				'soldListings'                   => $settings['wp_listings_idx_sold'],
				'automaticImport'                => boolval( $settings['wp_listings_auto_import'] ),
				'defaultListingTemplateOptions'  => $this->generate_template_list(),
				'defaultListingTemplateSelected' => $settings['wp_listings_default_template'],
				'displayIDXLink'                 => boolval( $settings['wp_listings_display_idx_link'] ),
				'importedListingsAuthorOptions'  => $this->generate_authors_list(),
				'importedListingsAuthorSelected' => (int) $settings['wp_listings_import_author'],
				'importTitle'                    => $settings['wp_listings_import_title'],
				'advancedFieldData'              => boolval( $settings['wp_listings_import_advanced_fields'] ),
				'displayAdvancedFields'          => boolval( $settings['wp_listings_display_advanced_fields'] ),
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
		$settings = get_option( 'plugin_wp_listings_settings', [] );

		if ( isset( $payload['updateListings'] ) ) {
			$settings['wp_listings_idx_update'] = sanitize_text_field( $payload['updateListings'] );
		}

		if ( isset( $payload['soldListings'] ) ) {
			$settings['wp_listings_idx_sold'] = sanitize_text_field( $payload['soldListings'] );
		}

		if ( isset( $payload['automaticImport'] ) ) {
			$settings['wp_listings_auto_import'] = (int) filter_var( $payload['automaticImport'], FILTER_VALIDATE_BOOLEAN );
			// Clear schedule if disabled.
			if ( ! $payload['automaticImport'] ) {
				wp_clear_scheduled_hook( 'wp_listings_idx_auto_import' );
			}
		}

		if ( isset( $payload['defaultListingTemplateSelected'] ) ) {
			$settings['wp_listings_default_template'] = sanitize_text_field( $payload['defaultListingTemplateSelected'] );
		}

		if ( isset( $payload['displayIDXLink'] ) ) {
			$settings['wp_listings_display_idx_link'] = (int) filter_var( $payload['displayIDXLink'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['importedListingsAuthorSelected'] ) ) {
			$settings['wp_listings_import_author'] = (int) filter_var( $payload['importedListingsAuthorSelected'], FILTER_VALIDATE_INT );
		}

		if ( isset( $payload['importTitle'] ) ) {
			$settings['wp_listings_import_title'] = sanitize_text_field( $payload['importTitle'] );
		}

		if ( isset( $payload['advancedFieldData'] ) ) {
			$settings['wp_listings_import_advanced_fields'] = (int) filter_var( $payload['advancedFieldData'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $payload['displayAdvancedFields'] ) ) {
			$settings['wp_listings_display_advanced_fields'] = (int) filter_var( $payload['displayAdvancedFields'], FILTER_VALIDATE_BOOLEAN );
		}

		update_option( 'plugin_wp_listings_settings', $settings );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Generate_Author_List
	 * Helper method to generate author list.
	 *
	 * @return array
	 */
	private function generate_authors_list() {
		$available_authors = [];
		$users             = get_users();
		foreach ( $users as $user ) {
			$available_authors[] = [
				'value' => $user->ID,
				'label' => $user->data->user_nicename,
			];
		}
		return $available_authors;
	}

	/**
	 * Generate_Template_List
	 * Helper method to generate listing template list.
	 *
	 * @return array
	 */
	private function generate_template_list() {
		$template_list            = [
			[
				'value' => '',
				'label' => 'Default',
			],
		];
		$listing_template_manager = new \Single_Listing_Template();

		foreach ( $listing_template_manager->get_listing_templates() as $path => $name ) {
			$template_list[] = [
				'value' => $path,
				'label' => $name,
			];
		}
		return $template_list;
	}
}

new Listings_IDX_Settings();
