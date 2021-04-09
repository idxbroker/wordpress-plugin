<?php
/**
 * REST API: Import_Listings
 *
 * Adds routes for the listing import page.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX\Admin\Apis;

/**
 * Class for listing import page routes.
 *
 * Supports GET and POST requests for listing import operations.
 */
class Import_Listings extends \IDX\Admin\Rest_Controller {

	/**
	 * Registers routes.
	 */
	public function __construct() {
		register_rest_route(
			$this->namespace,
			$this->route_name( 'import/listings' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
			]
		);

		register_rest_route(
			$this->namespace,
			$this->route_name( 'import/listings/import' ),
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'post' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
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
			$this->route_name( 'import/listings/delete' ),
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'listings_enabled' ],
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
		if ( get_site_transient( 'wp_background-processing-listings_process_lock' ) ) {
			return rest_ensure_response(
				[
					'inProgress' => true,
				]
			);
		}

		$import_lists = $this->generate_import_lists();

		return rest_ensure_response(
			[
				'inProgress' => false,
				'imported'   => array_values( $import_lists['imported'] ),
				'unimported' => array_values( $import_lists['unimported'] ),
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

		$listing_importer = new \WPL_Idx_Listing();
		$listing_importer->wp_listings_idx_create_post( $selected_ids );

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

		$listings_options  = get_option( 'plugin_wp_listings_settings' );

		foreach ( $selected_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( ! is_wp_error( $post ) && ! empty( $post->post_type ) && 'listing' === $post->post_type ) {
				if ( has_post_thumbnail( $post->ID ) ) {
					$image_id = get_post_thumbnail_id( $post->ID );
					wp_delete_attachment( $image_id, true );
				}
				wp_delete_post( $post->ID );
			}
		}

		$import_lists = $this->generate_import_lists();

		return rest_ensure_response(
			[
				'imported'   => array_values( $import_lists['imported'] ),
				'unimported' => array_values( $import_lists['unimported'] ),
			]
		);
	}

	/**
	 * Generate_Import_Lists
	 * Helper method to generate imported/unimported lists.
	 *
	 * @return array
	 */
	public function generate_import_lists() {
		$imported   = [];
		$unimported = [];

		$listing_posts = new \WP_Query(
			[
				'post_type'      => 'listing',
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'pending', 'draft', 'private' ],
			]
		);

		if ( is_array( $listing_posts->posts ) ) {
			foreach ( $listing_posts->posts as $post ) {
				$post_meta = get_post_meta( $post->ID );
				if ( ! empty( $post_meta['_listing_mls'][0] ) ) {
					$listing_price = empty( $post_meta['_listing_price'][0] ) ? '' : $post_meta['_listing_price'][0];
					$address       = empty( $post_meta['_listing_address'][0] ) ? '' : $post_meta['_listing_address'][0];
					$image         = get_the_post_thumbnail_url( $post );
					// If no thumbnail set, change value to empty string.
					if ( ! $image ) {
						$image = '';
					}

					$imported[ $post_meta['_listing_mls'][0] ] = [
						'listingId'    => $post_meta['_listing_mls'][0],
						'listingPrice' => $listing_price,
						'image'        => $image,
						'address'      => $address,
						'postId'       => $post->ID,
					];
				}
			}
		}

		$unimported = $this->get_listings_from_idxb();

		if ( is_array( $unimported ) ) {
			foreach ( $unimported as $id => $listing ) {
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
	 * Get_Listings_from_IDXB
	 * Helper method to gather listing data from the IDXB API.
	 *
	 * @return array
	 */
	public function get_listings_from_idxb() {
		$idx_api   = new \IDX\Idx_Api();
		$idxb_data = $idx_api->client_properties( 'featured' );
		$listings  = [];

		if ( ! is_array( $idxb_data ) ) {
			return $listings;
		}

		foreach ( $idxb_data as $listing ) {
			$listings[ $listing['listingID'] ] = [
				'listingId'    => $listing['listingID'],
				'address'      => $listing['address'],
				'listingPrice' => $listing['listingPrice'],
				'image'        => empty( $listing['image']['0']['url'] ) ? $listing['image']['1']['url'] : $listing['image']['0']['url'] ,
			];
		}
		return $listings;
	}

}

new Import_Listings();
