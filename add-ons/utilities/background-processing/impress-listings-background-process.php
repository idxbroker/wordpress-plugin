<?php

require_once IMPRESS_IDX_DIR . 'add-ons/utilities/background-processing/wp-background-processing/wp-background-processing.php';
/**
 * Class for handling background importing of listings.
 */
class IMPress_Listings_Import_Process extends WP_Background_Process {

	/**
	 * Protected ID that is single title (for working with super::)
	 *
	 * @var protected 'background-processing-listings'
	 */
	protected $action = 'background-processing-listings';

	/**
	 * Task to be run each iteration
	 *
	 * @param  string $data     Information of listing to be imported.
	 * @return mixed                False if done, $data if to be re-run.
	 */
	protected function task( $data ) {
		// Get important data.
		$wpl_import_options = get_option( 'wp_listings_idx_featured_listing_wp_options' );

		$property = $data['property'];
		$prop     = $data['prop'];
		$key      = $data['key'];
		$opts     = $data['opts'];

		$wpl_options = get_option( 'plugin_wp_listings_settings' );

		$listing_posts = get_posts(
			[
				'numberposts' => '-1',
				'post_type'   => 'listing',
				'post_status' => [ 'publish', 'pending', 'draft', 'private' ],
			]
		);

		foreach ( $listing_posts as $listing_post ) {
			if ( get_post_meta( $listing_post->ID, '_listing_mls', true ) === $property['listingID'] ) {
				return false;
			}
		}

		// Add the post.
		$add_post = wp_insert_post( $opts, true );

		// Show error if wp_insert_post fails
		// add post meta and update options if success.
		if ( is_wp_error( $add_post ) ) {
			// WordPress failed to insert the post.
			// Get listing key for failed import and the failed import list.
			$failed_import_item = ( is_string( $key ) ? $key : 'Invalid Key' );
			$failed_import_list = get_option( 'impress_listings_import_fail_list' );

			// Verify that the list is an array before pushing to it.
			if ( is_array( $failed_import_list ) ) {
				array_push( $failed_import_list, str_replace( '!%', ':', $failed_import_item ) );
			} else {
				$failed_import_list = [];
				array_push( $failed_import_list, $failed_import_item );
			}

			update_option( 'impress_listings_import_fail_list', $failed_import_list );
		} elseif ( $add_post ) {
			$wpl_import_options[ $prop['listingID'] ]['post_id'] = $add_post;
			$wpl_import_options[ $prop['listingID'] ]['status'] = 'publish';

			update_post_meta( $add_post, '_listing_details_url', $property['fullDetailsURL'] );

			update_option( 'wp_listings_idx_featured_listing_wp_options', $wpl_import_options );

			// Use custom default template if set.
			if ( isset( $wpl_options['wp_listings_default_template'] ) && $wpl_options['wp_listings_default_template'] != '' ) {
				update_post_meta( $add_post, '_wp_post_template', $wpl_options['wp_listings_default_template'] );
			}

			WPL_Idx_Listing::wp_listings_idx_insert_post_meta( $add_post, $property );
		}

		return false;
	}

	protected function complete() {
		parent::complete();
		// update_option( 'wp_listings_import_progress', false );
		die();
	}
}

new IMPress_Listings_Import_Process();
