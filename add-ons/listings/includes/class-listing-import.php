<?php
/**
 * This file contains the methods for interacting with the IDX API
 * to import listing data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IMPRESS_IDX_DIR . 'add-ons/utilities/background-processing/impress-listings-background-process.php';

class WPL_Idx_Listing {

	public $_idx;

	public function __construct() {
	}

	/**
	 * Function to get the array key (listingID+mlsID).
	 *
	 * @param  [type] $array  [description].
	 * @param  [type] $key    [description].
	 * @param  [type] $needle [description].
	 * @return [type]         [description].
	 */
	public static function get_key( $array, $key, $needle ) {
		if ( ! $array ) {
			return false;
		}
		foreach ( $array as $index => $value ) {
			if ( $needle === $value[$key] ) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * Function to find the key in the array
	 *
	 * @param  [type]  $needle   [description].
	 * @param  [type]  $haystack [description].
	 * @param  boolean $strict   [description].
	 * @return [type]            [description].
	 */
	public static function in_array( $needle, $haystack, $strict = false ) {
		if ( ! $haystack ) {
			return false;
		}
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item === $needle ) || ( is_array( $item ) && self::in_array( $needle, $item, $strict ) ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Creates a post of listing type using post data from options page
	 *
	 * @param  array $listings listingID of the property.
	 */
	public static function wp_listings_idx_create_post( $listings ) {
		update_option( 'wp_listings_import_progress', true );
		update_option( 'impress_listings_import_fail_list', [] );

		// Load IDX Broker API Class and retrieve featured properties.
		$_idx_api   = new \IDX\Idx_Api();
		$properties = $_idx_api->client_properties( 'featured?disclaimers=true' );

		// Load WP options.
		$wpl_import_options = get_option( 'wp_listings_idx_featured_listing_wp_options' );
		$wpl_options        = get_option( 'plugin_wp_listings_settings' );

		if ( is_array( $listings ) && is_array( $properties ) ) {

			$background_process = new IMPress_Listings_Import_Process();
			$item               = [];

			// Loop through featured properties.
			foreach ( $properties as $prop ) {

				// Get the listing ID.
				$key = self::get_key( $properties, 'listingID', $prop['listingID'] );

				// Add options.
				if ( ! in_array( $prop['listingID'], $listings, true ) ) {
					$wpl_import_options[ $prop['listingID'] ]['listingID'] = $prop['listingID'];
					$wpl_import_options[ $prop['listingID'] ]['status'] = '';
				}

				// Unset options if they don't exist.
				if ( isset( $wpl_import_options[ $prop['listingID'] ]['post_id'] ) && ! get_post( $wpl_import_options[ $prop['listingID'] ]['post_id'] ) ) {
					unset( $wpl_import_options[ $prop['listingID'] ]['post_id'] );
					unset( $wpl_import_options[ $prop['listingID'] ]['status'] );
				}

				// Add post and update post meta.
				if ( in_array( $prop['listingID'], $listings, true ) && ! isset( $wpl_import_options[ $prop['listingID'] ]['post_id'] ) ) {

					if ( empty( $properties[ $key ]['address'] ) ) {
						$properties[ $key ]['address'] = 'Address unlisted';
					}
					if ( empty( $properties[ $key ]['remarksConcat'] ) ) {
						$properties[ $key ]['remarksConcat'] = $properties[ $key ]['listingID'];
					}

					if ( empty( $wpl_options['wp_listings_import_title'] ) ) {
						$title_format = $properties[ $key ]['address'];
					} else {
						$title_format = $wpl_options['wp_listings_import_title'];
						$title_format = str_replace( '{{address}}', $properties[ $key ]['address'], $title_format );
						$title_format = str_replace( '{{city}}', $properties[ $key ]['cityName'], $title_format );
						$title_format = str_replace( '{{state}}', $properties[ $key ]['state'], $title_format );
						$title_format = str_replace( '{{zipcode}}', $properties[ $key ]['zipcode'], $title_format );
						$title_format = str_replace( '{{listingid}}', $properties[ $key ]['listingID'], $title_format );
					}

					// Post creation options.
					$opts = array(
						'post_content' => $properties[ $key ]['remarksConcat'],
						'post_title'   => $title_format,
						'post_status'  => 'publish',
						'post_type'    => 'listing',
						'post_author'  => ( isset( $wpl_options['wp_listings_import_author'] ) ) ? $wpl_options['wp_listings_import_author'] : 1,
					);

					$item['opts']     = $opts;
					$item['prop']     = $prop;
					$item['key']      = $key;
					$item['property'] = $properties[ $key ];

					$background_process->push_to_queue( $item );

					update_option( 'wp_listings_idx_featured_listing_wp_options', $wpl_import_options );
				}
				// Change status to publish if it's not already.
				elseif ( in_array( $prop['listingID'], $listings, true ) && $wpl_import_options[ $prop['listingID'] ]['status'] !== 'publish' ) {
					self::wp_listings_idx_change_post_status( $wpl_import_options[ $prop['listingID'] ]['post_id'], 'publish' );
					$wpl_import_options[ $prop['listingID'] ]['status'] = 'publish';
				}
				// Change post status or delete post based on options.
				elseif ( ! in_array( $prop['listingID'], $listings, true ) && isset( $wpl_import_options[ $prop['listingID'] ]['status'] ) && $wpl_import_options[ $prop['listingID'] ]['status'] === 'publish' ) {
					// Change to draft or delete listing if the post exists but is not in the listing array based on settings.
					if ( isset( $wpl_options['wp_listings_idx_sold'] ) && 'sold-draft' === $wpl_options['wp_listings_idx_sold'] ) {
						// Change to draft.
						self::wp_listings_idx_change_post_status( $wpl_import_options[ $prop['listingID'] ]['post_id'], 'draft' );
						$wpl_import_options[ $prop['listingID'] ]['status'] = 'draft';
					} elseif ( isset( $wpl_options['wp_listings_idx_sold'] ) && 'sold-delete' === $wpl_options['wp_listings_idx_sold'] ) {

						$wpl_import_options[ $prop['listingID'] ]['status'] = 'deleted';

						// Delete featured image.
						$post_featured_image_id = get_post_thumbnail_id( $wpl_import_options[ $prop['listingID'] ]['post_id'] );
						wp_delete_attachment( $post_featured_image_id );

						// Delete post.
						wp_delete_post( $wpl_import_options[ $prop['listingID'] ]['post_id'] );
					}
				}
			}
			$background_process->save()->dispatch();
		}

		// Lastly, update our options.
		update_option( 'wp_listings_idx_featured_listing_wp_options', $wpl_import_options );
	}

	/**
	 * Update existing post
	 *
	 * @return true if success.
	 */
	public static function wp_listings_update_post() {
		// Run incase import list is out of sync.
		sync_listing_options();

		// Load IDX Broker API Class and retrieve featured properties.
		$_idx_api = new \IDX\Idx_Api();
		$properties = $_idx_api->client_properties( 'featured?disclaimers=true' );

		// Load WP options
		$idx_featured_listing_wp_options = get_option( 'wp_listings_idx_featured_listing_wp_options' );
		$wpl_options = get_option( 'plugin_wp_listings_settings' );

		foreach ( $properties as $prop ) {
			$key = self::get_key( $properties, 'listingID', $prop['listingID'] );

			if ( isset( $idx_featured_listing_wp_options[ $prop['listingID'] ]['post_id'] ) ) {
				// Update property data.
				if ( ! isset( $wpl_options['wp_listings_idx_update'] )
						|| isset( $wpl_options['wp_listings_idx_update'] )
						&& 'update-none' !== $wpl_options['wp_listings_idx_update'] ) {
						self::wp_listings_idx_insert_post_meta( $idx_featured_listing_wp_options[ $prop['listingID'] ]['post_id'], $properties[ $key ], true, ( ! empty( $wpl_options['wp_listings_idx_update'] ) && 'update-noimage' === $wpl_options['wp_listings_idx_update'] ) ? false : true, false );
				}

				$idx_featured_listing_wp_options[ $prop['listingID'] ]['updated'] = date( 'm/d/Y h:i:sa' );
			}
		}

		// Load and loop through Sold properties.
		$sold_properties = $_idx_api->client_properties( 'soldpending' );
		foreach ( $sold_properties as $sold_prop ) {

			$key = self::get_key( $sold_properties, 'listingID', $sold_prop['listingID'] );

			if ( isset( $idx_featured_listing_wp_options[ $sold_prop['listingID'] ]['post_id'] ) ) {

				// Update property data.
				self::wp_listings_idx_insert_post_meta( $idx_featured_listing_wp_options[ $sold_prop['listingID'] ]['post_id'], $sold_properties[ $key ], true, ( ! empty( $wpl_options['wp_listings_idx_update'] ) && 'update-noimage' === $wpl_options['wp_listings_idx_update'] ) ? false : true, true );

				if ( isset( $wpl_options['wp_listings_idx_sold'] ) && 'sold-draft' === $wpl_options['wp_listings_idx_sold'] ) {

					// Change to draft.
					self::wp_listings_idx_change_post_status( $idx_featured_listing_wp_options[ $sold_prop['listingID'] ]['post_id'], 'draft' );
				} elseif ( isset( $wpl_options['wp_listings_idx_sold'] ) && 'sold-delete' === $wpl_options['wp_listings_idx_sold'] ) {

					// Delete featured image.
					$post_featured_image_id = get_post_thumbnail_id( $idx_featured_listing_wp_options[ $sold_prop['listingID'] ]['post_id'] );
					wp_delete_attachment( $post_featured_image_id );

					// Delete post.
					wp_delete_post( $idx_featured_listing_wp_options[ $sold_prop['listingID'] ]['post_id'] );
				}
			}
		}
		update_option( 'wp_listings_idx_featured_listing_wp_options', $idx_featured_listing_wp_options );
	}

	/**
	 * Change post status
	 *
	 * @param  [type] $post_id [description].
	 * @param  [type] $status  [description].
	 */
	public static function wp_listings_idx_change_post_status( $post_id, $status ) {
		$current_post                = get_post( $post_id, 'ARRAY_A' );
		$current_post['post_status'] = $status;
		wp_update_post( $current_post );
	}

	/**
	 * Inserts post meta based on property data
	 *
	 * API fields are mapped to post meta fields
	 * prefixed with _listing_ and lowercased
	 * @param  [type] $id  [description]
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	public static function wp_listings_idx_insert_post_meta( $id, $idx_featured_listing_data, $update = false, $update_image = true, $sold = false ) {

		$wpl_options = get_option( 'plugin_wp_listings_settings' );

		if ( false === $update || true === $update_image ) {
			$imgs           = '';
			$featured_image = empty( $idx_featured_listing_data['image']['0']['url'] ) ? '' : $idx_featured_listing_data['image']['0']['url'];

			foreach ( $idx_featured_listing_data['image'] as $image_data => $img ) {
				if ( 'totalCount' === $image_data ) {
					continue;
				}
				$img_markup = sprintf( '<img src="%s" alt="%s" />', $img['url'], ( isset( $idx_featured_listing_data['address'] ) ? $idx_featured_listing_data['address'] : $idx_featured_listing_data['listingID'] ) );
				$imgs      .= apply_filters( 'wp_listings_imported_image_markup', $img_markup, $img, $idx_featured_listing_data );
			}
			update_post_meta( $id, '_listing_gallery', apply_filters( 'wp_listings_imported_gallery', $imgs ) );
		}

		$current_status = empty( $idx_featured_listing_data['propStatus'] ) ? $idx_featured_listing_data['status'] : $idx_featured_listing_data['propStatus'];

		if ( 'A' === $current_status ) {
			$propstatus = 'Active';
		} elseif ( 'S' === $current_status ) {
			$propstatus = 'Sold';
		} else {
			$propstatus = ucfirst( $current_status );
		}

		// Add or reset taxonomies for property-types, locations, and status.
		if ( ! empty( $idx_featured_listing_data['idxPropType'] ) ) {
			wp_set_object_terms( $id, $idx_featured_listing_data['idxPropType'], 'property-types', true );
		}
		if ( ! empty( $idx_featured_listing_data['cityName'] ) ) {
			wp_set_object_terms( $id, $idx_featured_listing_data['cityName'], 'locations', true );	
		}
		if ( ! empty( $propstatus ) ) {
			wp_set_object_terms( $id, $propstatus, 'status', false );
		}

		// Acres is used if lotSqFt is not provided.
		$lot_sqft_value = '';
		if ( isset( $idx_featured_listing_data['lotSqFt'] ) ) {
			$lot_sqft_value = $idx_featured_listing_data['lotSqFt'];
		} elseif ( isset( $idx_featured_listing_data['acres'] ) ) {
			$lot_sqft_value = $idx_featured_listing_data['acres'] . ' acres';
		}

		// Add post meta for existing WPL fields.
		update_post_meta( $id, '_listing_lot_sqft', $lot_sqft_value );
		update_post_meta( $id, '_listing_acres', isset( $idx_featured_listing_data['acres'] ) ? $idx_featured_listing_data['acres'] : '' );
		update_post_meta( $id, '_listing_price', isset( $idx_featured_listing_data['listingPrice'] ) ? $idx_featured_listing_data['listingPrice'] : '' );
		update_post_meta( $id, '_listing_address', isset( $idx_featured_listing_data['address'] ) ? $idx_featured_listing_data['address'] : '' );
		update_post_meta( $id, '_listing_city', isset( $idx_featured_listing_data['cityName'] ) ? $idx_featured_listing_data['cityName'] : '' );
		update_post_meta( $id, '_listing_county', isset( $idx_featured_listing_data['countyName'] ) ? $idx_featured_listing_data['countyName'] : '' );
		update_post_meta( $id, '_listing_state', isset( $idx_featured_listing_data['state'] ) ? $idx_featured_listing_data['state'] : '' );
		update_post_meta( $id, '_listing_zip', isset( $idx_featured_listing_data['zipcode'] ) ? $idx_featured_listing_data['zipcode'] : '' );
		update_post_meta( $id, '_listing_subdivision', isset( $idx_featured_listing_data['subdivision'] ) ? $idx_featured_listing_data['subdivision'] : '' );
		update_post_meta( $id, '_listing_mls', isset( $idx_featured_listing_data['listingID'] ) ? $idx_featured_listing_data['listingID'] : '' );
		update_post_meta( $id, '_listing_sqft', isset( $idx_featured_listing_data['sqFt'] ) ? $idx_featured_listing_data['sqFt'] : '' );
		update_post_meta( $id, '_listing_year_built', isset( $idx_featured_listing_data['yearBuilt'] ) ? $idx_featured_listing_data['yearBuilt'] : '' );
		update_post_meta( $id, '_listing_bedrooms', isset( $idx_featured_listing_data['bedrooms'] ) ? $idx_featured_listing_data['bedrooms'] : '' );
		update_post_meta( $id, '_listing_bathrooms', isset( $idx_featured_listing_data['totalBaths'] ) ? $idx_featured_listing_data['totalBaths'] : '' );
		update_post_meta( $id, '_listing_half_bath', isset( $idx_featured_listing_data['partialBaths'] ) ? $idx_featured_listing_data['partialBaths'] : '' );
		update_post_meta( $id, '_listing_latitude', isset( $idx_featured_listing_data['latitude'] ) ? $idx_featured_listing_data['latitude'] : '' );
		update_post_meta( $id, '_listing_longitude', isset( $idx_featured_listing_data['longitude'] ) ? $idx_featured_listing_data['longitude'] : '' );

		// Include advanced fields if setting is enabled.
		if ( ! empty( $wpl_options['wp_listings_import_advanced_fields'] ) && ! empty( $idx_featured_listing_data['advanced'] ) ) {
			// Flatten advanced fields that have arrays for values.
			foreach ( $idx_featured_listing_data['advanced'] as $key => $value ) {
				if ( is_array( $value ) ) {
					$idx_featured_listing_data['advanced'][ $key ] = implode( ', ', $value );
				}
			}
			update_post_meta( $id, '_advanced_fields', isset( $idx_featured_listing_data['advanced'] ) ? $idx_featured_listing_data['advanced'] : [] );
		}

		// Add disclaimers and courtesies.
		if ( isset( $idx_featured_listing_data['disclaimer'] ) && ! empty( $idx_featured_listing_data['disclaimer'] ) ) {
			foreach ( $idx_featured_listing_data['disclaimer'] as $disclaimer ) {
				if ( in_array( 'details', $disclaimer, false ) ) {
					$disclaimer_logo     = ( $disclaimer['logoURL'] ) ? '<br /><img src="' . $disclaimer['logoURL'] . '" alt="MLS Logo" style="opacity: 1 !important; position: static !important;" />' : '';
					$disclaimer_combined = $disclaimer['text'] . $disclaimer_logo;
					update_post_meta( $id, '_listing_disclaimer', $disclaimer_combined );
				}
				if ( in_array( 'widget', $disclaimer, false ) ) {
					$disclaimer_logo     = ( $disclaimer['logoURL'] ) ? '<br /><img src="' . $disclaimer['logoURL'] . '" alt="MLS Logo" style="opacity: 1 !important; position: static !important;" />' : '';
					$disclaimer_combined = $disclaimer['text'] . $disclaimer_logo;
					update_post_meta( $id, '_listing_disclaimer_widget', $disclaimer_combined );
				}
			}
		}

		if ( isset( $idx_featured_listing_data['disclaimer'] ) && ! empty( $idx_featured_listing_data['disclaimer'] ) ) {
			foreach ( $idx_featured_listing_data['courtesy'] as $courtesy ) {
				if ( in_array( 'details', $courtesy, false ) ) {
					update_post_meta( $id, '_listing_courtesy', $courtesy['text'] );
				}
				if ( in_array( 'widget', $courtesy, false ) ) {
					update_post_meta( $id, '_listing_courtesy_widget', $courtesy['text'] );
				}
			}
		}

		/**
		 * Pull featured image if it's not an update or update image is set to true
		 */
		if ( ( false === $update || true === $update_image ) && ! empty( $featured_image ) ) {
			// Delete previously attached image.
			if ( true === $update_image ) {
				$post_featured_image_id = get_post_thumbnail_id( $id );
				wp_delete_attachment( $post_featured_image_id );
			}

			// Add Featured Image to Post.
			$image_url  = $featured_image; // Define the image URL here.
			$upload_dir = wp_upload_dir(); // Set upload folder.
			$image_data = null;

			// Get image data.
			// Handle protocol agnostic image URLs.
			if ( substr( $image_url, 0, 2 ) === '//' ) {
				$response = wp_remote_get( 'https:' . $image_url );
				// If wp_remote_get() fails using https, attempt again using http before continuing.
				if ( is_wp_error( $response ) ) {
					$response = wp_remote_get( 'http:' . $image_url );
				}
				// Check for an error and make sure $response['body'] is populated.
				if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
					$image_data = null;
				} else {
					$image_data = $response['body'];
				}
			} else {
				$response = wp_remote_get( $image_url );
				// Check for an error and make sure $response['body'] is populated.
				if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
					$image_data = null;
				} else {
					$image_data = $response['body'];
				}
			}

			$filename = basename( $image_url . '/' . $idx_featured_listing_data['listingID'] . '.jpg' ); // Create image file name.

			// Check folder permission and define file location.
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Create the image file on the server.
			if ( ! file_exists( $file ) && null !== $image_data ) {
				file_put_contents( $file, $image_data );
			}

			// Check image file type.
			$wp_filetype = wp_check_filetype( $filename, null );

			// Set attachment data.
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => $idx_featured_listing_data['listingID'] . ( isset( $idx_featured_listing_data['address'] ) ? ' - ' . $idx_featured_listing_data['address'] : '' ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

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

function wp_listings_idx_listing_register_settings() {
	register_setting( 'wp_listings_idx_listing_settings_group', 'wp_listings_idx_featured_listing_wp_options', 'wp_listings_idx_create_post_cron' );
}

/**
 * Syncs imported listing posts with the data stored in the 'wp_listings_idx_featured_listing_wp_options' option.
 */
function sync_listing_options() {

	$listing_posts = get_posts(
		[
			'numberposts' => '-1',
			'post_type'   => 'listing',
			'post_status' => [ 'publish', 'pending', 'draft', 'private' ],
		]
	);

	$wpl_import_options = get_option( 'wp_listings_idx_featured_listing_wp_options' );

	if ( is_array( $listing_posts ) && is_array( $wpl_import_options ) ) {

		foreach ( $listing_posts as $key => $value ) {
			$listing_post_meta = get_post_meta( $value->ID );

			// Check if '_listing_mls' key exist in $listing_post_meta and has a value assigned.
			if ( array_key_exists( '_listing_mls', $listing_post_meta ) && ! empty( $listing_post_meta['_listing_mls'] ) ) {
				// If key does not exist in $wpl_import_options -> create it and add all values.
				if ( ! array_key_exists( $listing_post_meta['_listing_mls'][0], $wpl_import_options ) ) {
					$wpl_import_options[ $listing_post_meta['_listing_mls'][0] ] = [
						'listingID' => $listing_post_meta['_listing_mls'][0],
						'updated'   => date( "m/d/Y h:i:sa" ),
						'status'    => $value->post_status,
						'post_id'   => $value->ID,
					];
				} else {
					// If key does exist in $wpl_import_options -> just add missing values.
					$listing_options = $wpl_import_options[ $listing_post_meta['_listing_mls'][0] ];
					// set values if missing.
					// listingID, saved as '_listing_mls' for legacy reasons.
					if ( ! isset( $listing_options['listingID'] ) || empty( $listing_options['listingID'] ) ) {
						$wpl_import_options[ $listing_post_meta['_listing_mls'][0] ]['listingID'] = $listing_post_meta['_listing_mls'][0];
					}
					// updated.
					if ( ! isset( $listing_options['updated'] ) || empty( $listing_options['updated'] ) ) {
						$wpl_import_options[ $listing_post_meta['_listing_mls'][0] ]['updated'] = date("m/d/Y h:i:sa");
					}
					// status.
					if ( ! isset( $listing_options['status'] ) || empty( $listing_options['status'] ) ) {
						$wpl_import_options[ $listing_post_meta['_listing_mls'][0] ]['status'] = $value->post_status;
					}
					// post_id.
					if ( ! isset( $listing_options['post_id'] ) || empty( $listing_options['post_id'] ) ) {
						$wpl_import_options[ $listing_post_meta['_listing_mls'][0] ]['post_id'] = $value->ID;
					}
				}
				update_option( 'wp_listings_idx_featured_listing_wp_options', $wpl_import_options );
			}
		}
	}
}


/**
 * Check if update is scheduled - if not, schedule it to run twice daily.
 * Schedule auto import if option checked
 * Only add if IDX plugin is installed
 * @since 2.0
 */

add_action( 'admin_init', 'wp_listings_idx_update_schedule' );

$wpl_options = get_option( 'plugin_wp_listings_settings' );

if ( ! empty( $wpl_options['wp_listings_auto_import'] ) ) {
	add_action( 'admin_init', 'wp_listings_idx_auto_import_schedule' );
}

function wp_listings_idx_update_schedule() {
	if ( ! wp_next_scheduled( 'wp_listings_idx_update' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_listings_idx_update' );
	}
}
/**
 * On the scheduled update event, run wp_listings_update_post
 */
add_action( 'wp_listings_idx_update', [ 'WPL_Idx_Listing', 'wp_listings_update_post' ] );

/**
 * Schedule auto import task
 */
function wp_listings_idx_auto_import_schedule() {
	if ( ! wp_next_scheduled( 'wp_listings_idx_auto_import' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wp_listings_idx_auto_import' );
	}
}
add_action( 'wp_listings_idx_auto_import', 'wp_listings_idx_auto_import_task' );
/**
 * Get listingIDs and pass to create post cron job
 *
 * @return void
 */
function wp_listings_idx_auto_import_task() {
	$_idx_api    = new \IDX\Idx_Api();
	$properties  = $_idx_api->client_properties( 'featured' );
	$listing_ids = [];
	foreach ( $properties as $prop ) {
		$listing_ids[] = $prop['listingID'];
	}
	WPL_Idx_Listing::wp_listings_idx_create_post( $listing_ids );
	// Advanced field data update.
	$options = get_option( 'plugin_wp_listings_settings' );

	if ( isset( $options['wp_listings_import_advanced_fields'] ) ) {
		if ( ! get_option( 'wp_listings_advanced_field_display_options' ) ) {
			add_option( 'wp_listings_advanced_field_display_options', [] );
		}
		update_advanced_field_options();
	} else {
		purge_advanced_field_options();
	}
}

/**
 * Purges any saved advanced fields/Customizations currently saved.
 */
function purge_advanced_field_options() {
	update_option( 'wp_listings_advanced_field_display_options', [] );
}

/**
 * Gathers all advanced fields present to allow for cuztomization in settings.
 */
function update_advanced_field_options() {

	if ( ! get_option( 'wp_listings_advanced_field_display_options' ) ) {
		add_option( 'wp_listings_advanced_field_display_options', [] );
	}

	$adv_field_options = get_option( 'wp_listings_advanced_field_display_options' );
	if ( ! is_array( $adv_field_options ) ) {
		$adv_field_options = [];
	}

	$adv_fields = [];
	$listing_posts = get_posts(
		[
			'numberposts' => '-1',
			'post_type'   => 'listing',
		]
	);

	if ( ! is_array( $listing_posts ) ) {
		return;
	}

	foreach ( $listing_posts as $key => $value ) {
		$listing_post_meta = get_post_meta( $value->ID );
		// Get advanced fields from all listings and remove any duplicates.
		if ( ! empty( $listing_post_meta['_advanced_fields'][0] ) ) {
			$adv_fields = array_unique( array_merge( $adv_fields, array_keys( maybe_unserialize( $listing_post_meta['_advanced_fields'][0] ) ) ) );
		}
	}
	if ( ! empty( $adv_fields ) ) {
		sort( $adv_fields );
		foreach ( $adv_fields as $value ) {
			if ( ! array_key_exists( $value, $adv_field_options ) ) {
				$adv_field_options[ $value ] = [
					'custom_name'  => '',
					'display_field' => 'show',
				];
			}
		}
	}

	update_option( 'wp_listings_advanced_field_display_options', $adv_field_options );
}

