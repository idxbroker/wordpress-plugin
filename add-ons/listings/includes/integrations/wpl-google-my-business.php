<?php

/**
 * Class WPL_Google_My_Business.
 *
 * Class used to automate posting to Google My Business.
 */
class WPL_Google_My_Business {

	/**
	 * Singleton instance variable.
	 *
	 * @var WPL_Google_My_Business.
	 */
	private static $instance = null;

	/**
	 * Get_Instance.
	 * Returns singleton instance of class.
	 *
	 * @return WPL_Google_My_Business
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new WPL_Google_My_Business();
		}
		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// Set hook for cron event and custom schedules.
		add_filter( 'cron_schedules', [ $this, 'wpl_gmb_event_schedules' ], 10, 2 );
		// Set actions.
		add_action( 'wp_listings_gmb_auto_post', [ $this, 'wpl_gmb_scheduled_post' ] );
		add_action( 'wp_ajax_wpl_gmb_set_initial_tokens', [ $this, 'wpl_gmb_set_initial_tokens' ] );
		add_action( 'wp_ajax_impress_gmb_update_location_settings', [ $this, 'impress_gmb_update_location_settings' ] );
		add_action( 'wp_ajax_wpl_reset_next_post_time_request', [ $this, 'wpl_reset_next_post_time_request' ] );
		add_action( 'wp_ajax_impress_gmb_post_now', [ $this, 'impress_gmb_post_now' ] );
		add_action( 'wp_ajax_impress_gmb_update_scheduled_posts', [ $this, 'impress_gmb_update_scheduled_posts' ] );
		add_action( 'wp_ajax_wpl_clear_last_post_status', [ $this, 'wpl_clear_last_post_status' ] );
		add_action( 'wp_ajax_impress_gmb_remove_from_schedule', [ $this, 'impress_gmb_remove_from_schedule' ] );
		add_action( 'wp_ajax_impress_gmb_get_listing_posts', [ $this, 'impress_gmb_get_listing_posts' ] );
		add_action( 'wp_ajax_impress_gmb_change_posting_frequency', [ $this, 'impress_gmb_change_posting_frequency' ] );
		add_action( 'wp_ajax_impress_gmb_dismiss_banner', [ $this, 'impress_gmb_dismiss_banner'] );
		add_action( 'wp_ajax_impress_gmb_save_custom_post', [ $this, 'impress_gmb_save_custom_post'] );
		add_action( 'wp_ajax_impress_gmb_delete_custom_post', [ $this, 'impress_gmb_delete_custom_post'] );
		add_action( 'wp_ajax_impress_gmb_get_posts_data', [ $this, 'impress_gmb_get_posts_data'] );
		add_action( 'wp_ajax_impress_gmb_logout', [ $this, 'impress_gmb_logout' ] );
		// Create custom post type.
		$this->create_gmb_posttype();
	}

	/**
	 * IMPress_GMB_Get_Posts_Data.
	 * Used to get custom and scheduled posts information.
	 *
	 * @return void
	 */
	public function impress_gmb_get_posts_data() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_get_posts_data_nonce' ) ) {
			$options = $this->wpl_get_gmb_settings_options();
			$data    = [
				'byId'         => [],
				'allIds'       => [],
				'scheduledIds' => array_values( $options['scheduled_posts'] ),
			];

			$custom_gmb_posts = get_posts(
				[
					'post_type'   => 'impress_gmb_post',
					'post_status' => 'draft',
					'numberposts' => -1,
					'order'       => 'DESC',
				]
			);

			foreach ( $custom_gmb_posts as $key => $custom_post ) {
				// Add ID to allIds array.
				$data['allIds'][] = $custom_post->ID;

				$post_meta = get_post_meta( $custom_post->ID );

				$data['byId'][ $custom_post->ID ] = [
					'id'            => $custom_post->ID,
					'postUrl'       => $post_meta['post_link_url'][0],
					'imageUrl'      => $post_meta['post_photo_url'][0],
					'summary'       => substr( wp_strip_all_tags( $custom_post->post_content ), 0, 1499 ),
					'title'         => $custom_post->post_title,
					'lastPublished' => ( ! empty( $post_meta['last_published'][0] ) ? $post_meta['last_published'][0] : '' ),
				];
			}
			wp_send_json( $data, 200 );
		}
		wp_die();
	}

	/**
	 * IMPress_GMB_Dismiss_Banner.
	 * Dismisses initial help banner.
	 *
	 * @return void
	 */
	public function impress_gmb_dismiss_banner() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_dismiss_banner_nonce' ) ) {
			$options                     = $this->wpl_get_gmb_settings_options();
			$options['banner_dismissed'] = true;
			update_option( 'wp_listings_google_my_business_options', $options );
		}
		wp_die();
	}

	/**
	 * IMPress_GMB_Change_Posting_Frequency.
	 * Change auto posting frequency.
	 *
	 * @return void
	 */
	public function impress_gmb_change_posting_frequency() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['interval'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_change_posting_frequency_nonce' ) ) {
			$options = $this->wpl_get_gmb_settings_options();

			switch ( intval( $_POST['interval'] ) ) {
				case 0:
					$new_value = 'weekly';
					break;
				case 1:
					$new_value = 'biweekly';
					break;
				case 2:
					$new_value = 'monthly';
					break;
				default:
					$new_value = 'weekly';
			}

			if ( $new_value !== $options['posting_frequency'] ) {
				$options['posting_frequency'] = $new_value;
				update_option( 'wp_listings_google_my_business_options', $options );
				$this->wpl_gmb_update_scheduled_posting_interval( $new_value );
			}

			wp_send_json( $new_value, 200 );
		}

		wp_die();
	}

	/**
	 * IMPress_GMB_Remove_From_Schedule.
	 * Remove item from schedule.
	 *
	 * @return void
	 */
	public function impress_gmb_remove_from_schedule() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['index'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_remove_from_schedule_nonce' ) ) {
			$options = $this->wpl_get_gmb_settings_options();
			if ( ! empty( $options['scheduled_posts'][ $_POST['index'] ] ) ) {
				// If post is removed from schedule, replace is placeholder. If placeholder is removed, delete entry from schedule_posts.
				if ( $options['scheduled_posts'][ $_POST['index'] ] === '-' ) {
					unset( $options['scheduled_posts'][ $_POST['index'] ] );
				} else {
					$options['scheduled_posts'][ $_POST['index'] ] = '-';
				}
				// Re-index after removal.
				$options['scheduled_posts'] = array_values( $options['scheduled_posts'] );
				update_option( 'wp_listings_google_my_business_options', $options );
				wp_send_json( 'success', 200 );
			}
		}
		wp_die();
	}

	/**
	 * IMPress_GMB_Get_Listing_Posts.
	 * Get impress listing posts.
	 *
	 * @return void
	 */
	public function impress_gmb_get_listing_posts() {
		// User capability check.
		if ( ! current_user_can( 'read' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_get_listing_posts_nonce' ) ) {

			$listing_posts = get_posts(
				[
					'post_type'   => 'listing',
					'post_status' => 'publish',
					'numberposts' => -1,
					'order'       => 'DESC',
				]
			);

			$parsed_data = [];
			foreach ( $listing_posts as $key => $listing ) {
				$parsed_data[] = [
					'id'       => $listing->ID,
					'postUrl'  => get_permalink( $listing ),
					'imageUrl' => get_the_post_thumbnail_url( $listing, 'full' ),
					'summary'  => substr( wp_strip_all_tags( $listing->post_content ), 0, 1499 ),
					'title'    => $listing->post_title,
				];
			}
			wp_send_json( $parsed_data, 200 );
		}
		wp_die();
	}

	/**
	 * Create_GMB_Posttype.
	 * Creates custom IMPress GMB post type.
	 *
	 * @return void
	 */
	public function create_gmb_posttype() {
		register_post_type(
			'impress_gmb_post',
			[
				'labels'       => [
					'name'          => __( 'IMPress_GMB_Posts', 'wp-listings' ),
					'singular_name' => __( 'IMPress_GMB_Post', 'wp-listings' ),
				],
				'has_archive'  => false,
				'rewrite'      => [ 'slug' => 'impress_gmb_post' ],
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * IMPress_GMB_Save_Custom_Post.
	 * Creates or updates a IMPress GMB custom post.
	 *
	 * @return void
	 */
	public function impress_gmb_save_custom_post() {
		// User capability check.
		if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['title'], $_POST['postUrl'], $_POST['imageUrl'], $_POST['summary'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_save_custom_post_nonce' ) ) {

			$post_data = [
				'post_title'   => sanitize_text_field( wp_unslash( $_POST['title'] ) ),
				'post_type'    => 'impress_gmb_post',
				'post_content' => sanitize_text_field( wp_unslash( $_POST['summary'] ) ),
				'meta_input'   => [
					'post_link_url'  => sanitize_text_field( wp_unslash( $_POST['postUrl'] ) ),
					'post_photo_url' => sanitize_text_field( wp_unslash( $_POST['imageUrl'] ) ),
					'last_published' => '',
				],
			];

			if ( ! empty( $_POST['id'] ) ) {
				$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
				// Verify custom post type before setting ID to prevent editing of non-impress_gmb_post typed posts.
				if ( 'impress_gmb_post' === get_post_type( $post_id ) ) {
					$post_data['ID'] = $post_id;
				}
			}

			// If ID is set, update post, otherwise create new.
			$add_to_schedule = false;
			if ( empty( $post_data['ID'] ) ) {
				$post_output     = wp_insert_post( $post_data );
				$add_to_schedule = true;
			} else {
				$post_output = wp_update_post( $post_data );
			}

			if ( ! is_wp_error( $post_output ) ) {
				$new_post = [
					'id'            => $post_output,
					'title'         => $post_data['post_title'],
					'summary'       => $post_data['post_content'],
					'postUrl'       => $post_data['meta_input']['post_link_url'],
					'imageUrl'      => $post_data['meta_input']['post_photo_url'],
					'lastPublished' => $post_data['meta_input']['last_published'],
				];

				if ( $add_to_schedule ) {
					$options = $this->wpl_get_gmb_settings_options();
					// Replace first placeholder entry is exists, otherwise append to end.
					$first_placeholder_index = array_search( '-', $options['scheduled_posts'], true );
					if ( $first_placeholder_index !== false ) {
						$options['scheduled_posts'][ $first_placeholder_index ] = $post_output;
					} else {
						$options['scheduled_posts'][] = $post_output;
					}
					update_option( 'wp_listings_google_my_business_options', $options );
				}
				wp_send_json( $new_post, 200 );
			}

		}
		wp_die();
	}

	/**
	 * IMPress_GMB_Delete_Custom_Post.
	 * Deletes custom GMB post.
	 *
	 * @return void
	 */
	public function impress_gmb_delete_custom_post() {
		// User capability check.
		if ( ! current_user_can( 'delete_posts' ) || ! current_user_can( 'delete_others_posts' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['postId'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_delete_custom_post_nonce' ) ) {
			$post_id = sanitize_text_field( wp_unslash( $_POST['postId'] ) );
			$options = $this->wpl_get_gmb_settings_options();

			// Verify custom post type before deleting.
			if ( 'impress_gmb_post' !== get_post_type( $post_id ) ) {
				echo 'Incorrect post type';
				wp_die();
			}

			$deleted_post = wp_delete_post( $post_id, true );

			// Remove all entries from scheduled posts.
			foreach ( $options['scheduled_posts'] as $key => $value ) {
				if ( $value == $post_id ) {
					unset( $options['scheduled_posts'][ $key ] );
				}
			}

			update_option( 'wp_listings_google_my_business_options', $options );

			if ( $deleted_post ) {
				wp_send_json( $post_id, 200 );
			} else {
				echo 'Custom GMB post deletion failed';
			}
		}

		wp_die();
	}


	/**
	 * Get_GMB_Settings_Options.
	 * Getter for GMB options/settings, also sets default values.
	 */
	public function wpl_get_gmb_settings_options() {
		$options  = get_option( 'wp_listings_google_my_business_options', [] );
		$defaults = [
			'access_token'      => '',
			'refresh_token'     => '',
			'locations'         => [],
			'banner_dismissed'  => 0,
			'posting_frequency' => 'weekly',
			'scheduled_posts'   => [],
			'posting_logs'     => [
				'last_post_status_message' => '',
				'last_post_timestamp'      => '',
			],
		];
		return array_merge( $defaults, $options );

	}

	/**
	 * Update_Logs.
	 * Used to update posting_logs portion of wp_listings_google_my_business_options.
	 *
	 * @param  string $log_key - Included Error_Message/Used_Post_IDs/Last_Post_Timestamp.
	 * @param  mixed  $log_value - Value to be assigned to one of the 3 supported keys.
	 * @return void
	 */
	public function wpl_gmb_update_logs( $log_key, $log_value ) {
		$options = $this->wpl_get_gmb_settings_options();

		if ( 'last_post_status_message' === $log_key && is_string( $log_value ) ) {
			$options['posting_logs']['last_post_status_message'] = $log_value;
		}

		if ( 'used_post_ids' === $log_key && is_int( $log_value ) ) {
			array_push( $options['posting_logs']['used_post_ids'], $log_value );
			// Only keep track of 50 most recently posts.
			if ( count( $options['posting_logs']['used_post_ids'] ) > 50 ) {
				array_shift( $options['posting_logs']['used_post_ids'] );
			}
		}
		// Handle user_post_id getting wiped upon sharing all available listings.
		if ( 'used_post_ids' === $log_key && is_array( $log_value ) ) {
			$options['posting_logs']['used_post_ids'] = [];
		}

		if ( 'last_post_timestamp' === $log_key && is_string( $log_value ) ) {
			$options['posting_logs']['last_post_timestamp'] = $log_value;
		}

		update_option( 'wp_listings_google_my_business_options', $options );
	}

	/**
	 * Set_initial_tokens.
	 * Sets initial access and refresh tokens upon authenticating to Google.
	 */
	public function wpl_gmb_set_initial_tokens() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['access_token'], $_POST['refresh_token'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpl_gmb_set_initial_tokens_nonce' ) ) {
			$refresh_token = sanitize_text_field( wp_unslash( $_POST['refresh_token'] ) );
			$access_token  = sanitize_text_field( wp_unslash( $_POST['access_token'] ) );
			$this->save_authentication_keys( $access_token, $refresh_token );
			$this->wpl_schedule_posting_event();
		}

		wp_die();
	}

	/**
	 * Get_Google_Access_Token.
	 * Gets current access token from transient data, if expired it will request a new code from Google using the refresh token.
	 */
	public function get_google_access_token() {

		$auth_transient = get_transient( 'wp_listings_google_my_business_auth_cache' );
		if ( $auth_transient ) {
			return $auth_transient;
		}

		$auth_settings = $this->wpl_get_gmb_settings_options();

		$response = wp_remote_get( 'https://hheqsfm21f.execute-api.us-west-2.amazonaws.com/v1/token-refresh?refresh_token=' . $auth_settings['refresh_token'] );
		if ( ! is_wp_error( $response ) ) {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$jsondata         = json_decode( preg_replace( '/("\w+"):(\d+(\.\d+)?)/', '\\1:"\\2"', $response['body'] ), true );
				$new_access_token = sanitize_text_field( $jsondata['body']['access_token'] );
				$this->save_authentication_keys( $new_access_token );
				return $new_access_token;
			}
		}

		// Log error and return false.
		$this->wpl_gmb_update_logs( 'last_post_status_message', 'Failed - Required token missing.' );
		return false;
	}

	/**
	 * Save_Authentication_Keys.
	 * Saves access_token and optionally a refresh_token to the options table.
	 */
	public function save_authentication_keys( $access_token, $refresh_token = '' ) {
		$options = $this->wpl_get_gmb_settings_options();

		$options['access_token'] = $access_token;
		if ( '' !== $refresh_token ) {
			$options['refresh_token'] = $refresh_token;
		}

		update_option( 'wp_listings_google_my_business_options', $options );
		set_transient( 'wp_listings_google_my_business_auth_cache', $access_token, MINUTE_IN_SECONDS * 45 );
	}


	/**
	 * Update_GMB_Preferences.
	 * Set preferences via Ajax call from the Integrations settings page.
	 */
	public function impress_gmb_update_location_settings() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['locations'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_update_location_settings_nonce' ) ) {
			$options = $this->wpl_get_gmb_settings_options();
			// Parse location settings.
			$location_share_settings = [];
			if ( ! empty( $_POST['locations'] ) && is_array( $_POST['locations'] ) ) {
				$location_share_settings = filter_var_array( wp_unslash( $_POST['locations'] ), FILTER_SANITIZE_NUMBER_INT );
			}

			foreach ( $location_share_settings as $key => $value ) {
				if ( array_key_exists( $key, $options['locations'] ) && ! empty( $value['share_to_location'] ) ) {
					$options['locations'][ $key ]['share_to_location'] = 1;
				} else {
					$options['locations'][ $key ]['share_to_location'] = 0;
				}
			}
			// Update options, echo success, and kill connection.
			update_option( 'wp_listings_google_my_business_options', $options );
			echo 'success';
			wp_die();
		}

		echo 'request failed';
		wp_die();
	}

	/**
	 * Clear_GMB_Settings.
	 * Clears all saved GMB settings, sets feature back to unlogged-in/default state.
	 */
	public function impress_gmb_logout() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'impress_gmb_logout_nonce' ) ) {
			// Clear options.
			delete_option( 'wp_listings_google_my_business_options' );
			// Clear transients.
			delete_transient( 'wp_listings_google_my_business_auth_cache' );
			delete_transient( 'wp_listings_google_my_business_account_cache' );
			delete_transient( 'wp_listings_google_my_business_location_settings' );
			wp_clear_scheduled_hook( 'wp_listings_gmb_auto_post' );
			echo 'success';
			wp_die();
		}

		echo 'request failed';
		wp_die();
	}

	/**
	 * Get_GMB_Accounts.
	 * Gets raw/full Google My Business account information required for making local posts.
	 *
	 * @return array
	 */
	public function get_gmb_accounts() {

		$account_transient = get_transient( 'wp_listings_google_my_business_account_cache' );

		if ( $account_transient ) {
			return $account_transient;
		}

		// Make sure token is available before making request.
		if ( ! $this->get_google_access_token() ) {
			return;
		}

		$response = wp_remote_get(
			'https://mybusiness.googleapis.com/v4/accounts',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_google_access_token(),
				],
			]
		);

		if ( ! is_wp_error( $response ) ) {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$json     = json_decode( $response['body'], true );
				$accounts = $json['accounts'];
				set_transient( 'wp_listings_google_my_business_account_cache', $accounts, WEEK_IN_SECONDS );
				return $accounts;
			}
		}

		return [];
	}

	/**
	 * Get_Selected_GMB_Accounts.
	 * Gets selected Google My Business account.
	 * This is currently a placeholder function as there is no user-facing option to select an account.
	 * The first account returned from Google will be used for creating posts.
	 *
	 * @return mixed
	 */
	public function get_selected_gmb_account() {
		$options = $this->wpl_get_gmb_settings_options();

		if ( ! empty( $options['account_name'] ) ) {
			return $options['account_name'];
		}

		$accounts = $this->get_gmb_accounts();
		if ( ! empty( $accounts ) && ! empty( $accounts[0]['name'] ) ) {
			return $accounts[0]['name'];
		}

		return false;
	}

	/**
	 * Get__GMB_Locations.
	 * Gets raw/full Google My Business location information required for making local posts.
	 *
	 * @return array
	 */
	public function get_gmb_locations() {
		// Check for transient data first.
		$locations_transient = get_transient( 'wp_listings_google_my_business_location_settings' );
		if ( $locations_transient ) {
			return $locations_transient;
		}

		$locations = [];

		$account = $this->get_selected_gmb_account();

		// Make sure token is available before making request.
		if ( ! $this->get_google_access_token() ) {
			return;
		}

		$response = wp_remote_get(
			'https://mybusiness.googleapis.com/v4/'. $account . '/locations',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_google_access_token(),
				],
			]
		);

		if ( ! is_wp_error( $response ) ) {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$json = json_decode( $response['body'], true );
				$locations = $json['locations'];
				set_transient( 'wp_listings_google_my_business_location_settings', $locations, WEEK_IN_SECONDS );
				return $locations;
			}
		}
		return $locations;
	}

	/**
	 * Get_Saved_GMB_Locations.
	 * Gets saved location data which includes sharing preferences for locations.
	 *
	 * @return mixed
	 */
	public function get_saved_gmb_locations() {
		// Get all locations, return false if none.
		$all_locations = $this->get_gmb_locations();
		if ( empty( $all_locations ) ) {
			return false;
		}

		// Get current GMB options. 
		$options = $this->wpl_get_gmb_settings_options();
		// // Add any locations from all_locations that are not already in saved_locations.
		foreach ( $all_locations as $current_location ) {
			if ( ! isset( $options['locations'][ $current_location['name'] ] ) ) {
				$options['locations'][ $current_location['name'] ] = [
					'location_name'     => $current_location['locationName'],
					'street_address'    => $current_location['address']['addressLines'][0],
					'share_to_location' => 1,
				];
			}
		}

		update_option( 'wp_listings_google_my_business_options', $options );
		return $options['locations'];
	}

	/**
	 * Get_Full_Location_Information.
	 * Gets all info for a given location from its name.
	 *
	 * @return mixed
	 */
	public function get_full_location_information( $location_name ) {
		$options = $this->wpl_get_gmb_settings_options();

		$locations = $this->get_gmb_locations();

		if ( is_array( $locations ) ) {
			foreach ( $locations as $location ) {
				if ( $location_name === $location['name'] ) {
					return $location;
				}
			}
		}

		return false;
	}


	// Posting Functions.

	/**
	 * Get_Data_From_Post_ID.
	 * Gathers info from a listing post and passed the required values to publish_post_to_gmb().
	 *
	 * @param int $post_id - Post ID.
	 *
	 * @return void
	 */
	public function wpl_gmb_get_data_from_post_id( $post_id ) {
		$options = $this->wpl_get_gmb_settings_options();

		$post = get_post( $post_id );
		$post_meta = get_post_meta( $post->ID );

		// Just in case get_post fails.
		if ( ! $post ) {
			$this->wpl_gmb_update_logs( 'last_post_status_message', 'Failed - Issue with locating listing post from ID.' );
			return;
		}

		$summary   = $post->post_content;
		$page_url  = $post_meta['post_link_url'];
		$photo_url = $post_meta['post_photo_url'];

		// Check if all values are populated and submit post.
		if ( isset( $summary, $photo_url, $page_url ) ) {
			$this->publish_post_to_gmb( $summary, $photo_url, $page_url, $post_id );
			return;
		}

	}

	/**
	 * Publish_Post_To_GMB.
	 * Used to create a "What's New - Learn More" Local Post on Google My Business using the passed in values.
	 *
	 * @param string $summary - Post summary string.
	 * @param string $photo_url - Post photo URL.
	 * @param string $page_url - Post page URL.
	 * @param string $post_id - Post ID is optional, only used for logging post success/failure.
	 *
	 * @return void
	 */
	public function publish_post_to_gmb( $summary, $photo_url, $page_url, $post_id = null ) {
		// Make sure summary is below 1500 characters. Strip tags just incase HTML came through in a listing summary.
		$summary = substr( strip_tags( $summary ), 0, 1499 );

		// Validate URLs.
		$photo_url = wp_http_validate_url( $photo_url );
		$page_url  = wp_http_validate_url( $page_url );

		// Final validation before attempting to post.
		if ( empty( $photo_url ) || empty( $page_url ) || empty( $summary ) ) {
			$this->wpl_gmb_update_logs( 'last_post_status_message', 'Final check before posting failed, verify both photo and page URL links work and that a summary is included.' );
			return;
		}

		$post_body = [
			'languageCode' => get_locale(),
			'summary'      => $summary,
			'callToAction' => [
				'url'        => $page_url,
				'actionType' => 'LEARN_MORE',
			],
			'media'        => [
				'sourceUrl'   => $photo_url,
				'mediaFormat' => 'PHOTO',
			],
		];

		// Encode $post_body before sending.
		$post_body = json_encode( $post_body );

		// Get locations to post.
		$locations = $this->get_saved_gmb_locations();

		// If no locations available, log error and return.
		if ( empty( $locations ) ) {
			$this->wpl_gmb_update_logs( 'last_post_status_message', 'No posting locations available.' );
			return;
		}

		// Make sure token is available before making requests.
		if ( ! $this->get_google_access_token() ) {
			return;
		}

		foreach ( $locations as $key => $location ) {

			if ( ! $location['share_to_location'] ) {
				continue;
			}

			$response = wp_remote_post(
				'https://mybusiness.googleapis.com/v4/' . $key . '/localPosts',
				[
					'headers' => [
						'Authorization' => 'Bearer ' . $this->get_google_access_token(),
						'Content-Type'  => 'application/json; charset=utf-8',
					],
					'body'    => $post_body,
				]
			);

			if ( ! is_wp_error( $response ) ) {

				$json          = json_decode( $response['body'], true );
				$response_code = wp_remote_retrieve_response_code( $response );
				$options       = $this->wpl_get_gmb_settings_options();

				if ( 200 === $response_code ) {
					// If a post ID was included in the function call, remove it from the schedule and update posting log.
					if ( $post_id ) {
						$this->wpl_gmb_update_logs( 'used_post_ids', $post_id );
						update_post_meta( $post_id, 'last_published', date( 'm/d/Y' ) );

						$scheduled_key = array_search( $post_id, $options['scheduled_posts'], true );
						if ( false !== $scheduled_key ) {
							array_splice( $options['scheduled_posts'], $scheduled_key, 1 );
							update_option( 'wp_listings_google_my_business_options', $options );
						}
					}
					$this->wpl_gmb_update_logs( 'last_post_status_message', 'Post Successful' );
					return;
				}

				// Posting failed, schedule re-attempt.
				$this->wpl_reset_next_scheduled_post_time( true );

				// Invalid link or photo URL.
				if ( 400 === $response_code ) {
					$this->wpl_gmb_update_logs( 'last_post_status_message', 'Oops! Post Unsuccessful - Invalid photo or page URL provided.' . ( ! empty( $post_id ) ? " Post ID: $post_id" : '' ) );
					return;
				}

				// Location not authorized by Google to accept location posts.
				if ( 403 === $response_code ) {
					// Check for unverified location error.
					if ( 'Creating/Updating a local post is not authorized for this location.' === $json['error']['message'] ) {
						$this->wpl_gmb_update_logs( 'last_post_status_message', 'Oops! Post Unsuccessful - Creating/Updating a local post is not authorized for this location. Check with Google on the status of verifying your business location.' . ( ! empty( $post_id ) ? " Post ID: $post_id" : '' ) );
						return;
					}
				}

				// Catch any other remaining errors, include status code in .
				$this->wpl_gmb_update_logs( 'last_post_status_message', 'Oops! Post Unsuccessful - Response code received from Google: ' . $response_code . '.' . ( ! empty( $post_id ) ? " Post ID: $post_id" : '' ) );
				return;
			}

			// WP_Error found, log error.
			$this->wpl_gmb_update_logs( 'last_post_status_message', 'Oops! Post Unsuccessful - WP_Error returned.' );
			return;
		}

		// Only reachable if no locations are found with sharing enabled.
		$this->wpl_gmb_update_logs( 'last_post_status_message', 'Oops! Post Unsuccessful - No locations selected.' );
	}

	// Scheduling Functions.

	/**
	 * WPL_GMB_Scheduled_Post.
	 * Actual cron task used to post to Google My Business.
	 *
	 * @return void
	 */
	public function wpl_gmb_scheduled_post() {
		// Clear last post status message in preparation for this attempt's message.
		$this->wpl_gmb_update_logs( 'last_post_status_message', '' );

		$options = $this->wpl_get_gmb_settings_options();

		// If post is scheduled.
		if ( ! empty( $options['scheduled_posts'] ) ) {
			// If scheduled task is placeholder, remove the entry.
			if ( '-' === $options['scheduled_posts'][0] ) {
				array_shift( $options['scheduled_posts'] );
				update_option( 'wp_listings_google_my_business_options', $options );
			} else {
				$this->wpl_gmb_get_data_from_post_id( $options['scheduled_posts'][0] );
			}
		}
	}

	/**
	 * Schedule_Posting_Event.
	 * Used to schedule first import upon successful login to Google.
	 *
	 * @return void
	 */
	public function wpl_schedule_posting_event() {
		if ( ! wp_next_scheduled( 'wp_listings_gmb_auto_post' ) ) {
			// Fire first post in 12 hours from enabling.
			wp_schedule_event( ( time() + ( HOUR_IN_SECONDS * 12 ) ), 'weekly', 'wp_listings_gmb_auto_post' );
		}
	}

	/**
	 * Update_Scheduled_Posting_Interval.
	 * Upon updating settings, wipes out existing job and reschedules using the new interval, existing timestamp is preserved.
	 *
	 * @param string $interval - Reoccurance interval for cron job.
	 *
	 * @return void
	 */
	public function wpl_gmb_update_scheduled_posting_interval( $interval ) {
		$current_event = wp_get_scheduled_event( 'wp_listings_gmb_auto_post' );
		// If interval the same, return.
		if ( $interval === $current_event->schedule ) {
			return;
		}
		// Clear current event before scheduling a new one to prevent any duplication.
		wp_clear_scheduled_hook( 'wp_listings_gmb_auto_post' );
		switch ( $interval ) {
			case 'weekly':
				wp_schedule_event( $current_event->timestamp, 'weekly', 'wp_listings_gmb_auto_post' );
				break;
			case 'biweekly':
				wp_schedule_event( $current_event->timestamp, 'biweekly', 'wp_listings_gmb_auto_post' );
				break;
			case 'monthly':
				wp_schedule_event( $current_event->timestamp, 'monthly', 'wp_listings_gmb_auto_post' );
				break;
			default:
				// Something is askew if this happens, manually set timestamp just in case.
				wp_schedule_event( ( time() + WEEK_IN_SECONDS ), 'weekly', 'wp_listings_gmb_auto_post' );
		}
	}

	/**
	 * Event_Schedules.
	 * Used to add custom time intervals to the cron_schedules filter.
	 *
	 * @param array $schedules - Current array of schedules.
	 *
	 * @return array
	 */
	public function wpl_gmb_event_schedules( $schedules ) {

		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = [
				'display'  => __( 'Every Week', 'wp-listings' ),
				'interval' => WEEK_IN_SECONDS,
			];
		}

		if ( ! isset( $schedules['biweekly'] ) ) {
			$schedules['biweekly'] = [
				'display'  => __( 'Every 2 Weeks', 'wp-listings' ),
				'interval' => ( WEEK_IN_SECONDS * 2 ),
			];
		}

		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'display'  => __( 'Every Month', 'wp-listings' ),
				'interval' => MONTH_IN_SECONDS,
			];
		}

		return $schedules;
	}

	/**
	 * Reset_Next_Post_Time_Request.
	 * Handles Ajax request from WP dashboard to reset the next posting time, once request is verified the actual request occurs in wpl_reset_next_scheduled_post_time().
	 *
	 * @return void
	 */
	public function wpl_reset_next_post_time_request() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'wpl_reset_next_post_time_request_nonce' ) ) {
			$current_event = wp_get_scheduled_event( 'wp_listings_gmb_auto_post' );
			// Wipe out current event and reschedule for 12 hours from now.
			$this->wpl_reset_next_scheduled_post_time( true );
			echo esc_attr( $this->wpl_gmb_get_next_post_time() );
			wp_die();
		}

		wp_die();
	}

	/**
	 * Reset_Next_Scheduled_Post_Time.
	 * Resets next scheduled post time to either the next date based on posting frequency or 12 hours from now in cases of a posting failure or a user initiated reschedule.
	 *
	 * @param bool $retry_post - Used to 
	 * @return void
	 */
	public function wpl_reset_next_scheduled_post_time( $retry_post = false ) {
		$options = $this->wpl_get_gmb_settings_options();

		wp_clear_scheduled_hook( 'wp_listings_gmb_auto_post' );

		if ( $retry_post ) {
			wp_schedule_event( ( time() + ( HOUR_IN_SECONDS * 12 ) ), $options['posting_frequency'], 'wp_listings_gmb_auto_post' );
			return;
		}

		$current_schedules   = wp_get_schedules();
		$posting_frequency   = $options['posting_frequency'];
		$frequency_timestamp = $current_schedules[ $posting_frequency ]['interval'];
		wp_schedule_event( ( time() + $frequency_timestamp ), $posting_frequency, 'wp_listings_gmb_auto_post' );
	}

	/**
	 * Get_Next_Post_Time.
	 * Helper function to get approximate next post time as a string.
	 *
	 * @return string
	 */
	public function wpl_gmb_get_next_post_time() {
		$current_event = wp_get_scheduled_event( 'wp_listings_gmb_auto_post' );
		if ( ! empty( $current_event->timestamp ) ) {
			return date_i18n( 'l, F j', $current_event->timestamp );
		}
		return 'Unscheduled';
	}

	/**
	 * Post_Now.
	 * Posts with provided data.
	 *
	 * @return void
	 */
	public function impress_gmb_post_now() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['postUrl'], $_POST['imageUrl'], $_POST['summary'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_post_now_nonce' ) ) {
			$post_url  = sanitize_text_field( wp_unslash( $_POST['postUrl'] ) );
			$image_url = sanitize_text_field( wp_unslash( $_POST['imageUrl'] ) );
			$summary   = sanitize_text_field( wp_unslash( $_POST['summary'] ) );

			$post_id = null;
			if ( ! empty( $_POST['id'] ) ) {
				$post_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			}
			$this->publish_post_to_gmb( $summary, $image_url, $post_url, $post_id );
			wp_send_json( 'success', 200 );
		}

		wp_die();
	}

	/**
	 * Update_Scheduled_Posts.
	 * Updates scheduled posts list.
	 *
	 * @return void
	 */
	public function impress_gmb_update_scheduled_posts() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}

		// Validate and process request.
		if ( isset( $_POST['nonce'], $_POST['scheduled_posts'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'impress_gmb_update_scheduled_posts_nonce' ) ) {
			$submitted_schedule = explode( ',', sanitize_text_field( wp_unslash( $_POST['scheduled_posts'] ) ) );
			$options            = $this->wpl_get_gmb_settings_options();

			if ( ! empty( $submitted_schedule ) && is_array( $submitted_schedule ) ) {
				$options['scheduled_posts'] = $submitted_schedule;
				update_option( 'wp_listings_google_my_business_options', $options );
			}

			wp_send_json( $submitted_schedule, 200 );
		}

		wp_die();
	}

	/**
	 * Clear_Last_Post_Status.
	 * Clears last post status msg.
	 *
	 * @return void
	 */
	public function wpl_clear_last_post_status() {
		// User capability check.
		if ( ! current_user_can( 'manage_categories' ) ) {
			echo 'check permissions';
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpl_clear_last_post_status_nonce' ) ) {
			$options = $this->wpl_get_gmb_settings_options();
			$options['posting_logs']['last_post_status_message'] = '';
			update_option( 'wp_listings_google_my_business_options', $options );
		}

		wp_die();
	}

	/**
	 * Get_Error_Log.
	 * Helper function to get the stored error message.
	 *
	 * @return string
	 */
	public function wpl_gmb_get_error_log() {
		$options = $this->wpl_get_gmb_settings_options();
		if ( ! empty( $option['posting_logs']['last_post_status_message'] ) ) {
			return $option['posting_logs']['last_post_status_message'];
		}
		return '';
	}



}
