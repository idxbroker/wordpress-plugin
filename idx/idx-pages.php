<?php
namespace IDX;

/**
 * Idx_Pages class.
 */
class Idx_Pages {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new Idx_Api();
		// deletes all IDX pages for troubleshooting purposes.
		// $this->delete_all_idx_pages();.
		add_option( 'idx_cron_schedule', 'threeminutes' );
		register_setting( 'idx-platinum-settings-group', 'idx_cron_schedule' );
		add_action( 'admin_init', array( $this, 'show_idx_pages_metabox_by_default' ) );
		add_filter( 'post_type_link', array( $this, 'post_type_link_filter_func' ), 10, 2 );
		add_filter( 'cron_schedules', array( $this, 'add_custom_schedule' ) );

		// register hooks for WP Cron to use to update IDX Pages.
		add_action( 'idx_create_idx_pages', array( $this, 'create_idx_pages' ) );
		add_action( 'idx_delete_idx_pages', array( $this, 'delete_idx_pages' ) );

		add_action( 'init', array( $this, 'register_idx_page_type' ) );
		add_action( 'admin_init', array( $this, 'manage_idx_page_capabilities' ) );
		add_action( 'save_post', array( $this, 'save_idx_page' ), 1 );
		add_action( 'save_post', array( $this, 'set_wrapper_page' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// schedule an IDX page update via WP cron.
		$this->schedule_idx_page_update();

		// for testing.
		// add_action('wp_loaded', array($this, 'create_idx_pages'));.
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Add_custom_schedule function.
	 *
	 * @access public
	 * @param array $schedules - Current shedules.
	 * @return array
	 */
	public function add_custom_schedule( $schedules ) {
		$schedules['threeminutes'] = array(
			'interval' => 60 * 3, // three minutes in seconds.
			'display'  => 'Three Minutes',
		);

		return $schedules;
	}

	/** Schedule IDX Page update regularly. **/
	public function schedule_idx_page_update() {
		$idx_cron_schedule = get_option( 'idx_cron_schedule' );

		// Construct next_create_event/next_delete_event objects to replicate output from wp_get_scheduled_event() until we raise the supported WP version to 5.1.0 or above.
		$next_create_event            = new \stdClass();
		$next_create_event->schedule  = wp_get_schedule( 'idx_create_idx_pages' );
		$next_create_event->timestamp = wp_next_scheduled( 'idx_create_idx_pages' );

		$next_delete_event            = new \stdClass();
		$next_delete_event->schedule  = wp_get_schedule( 'idx_delete_idx_pages' );
		$next_delete_event->timestamp = wp_next_scheduled( 'idx_delete_idx_pages' );

		// If disabled, clear hooks and return early.
		if ( 'disabled' === $idx_cron_schedule ) {
			if ( $next_create_event ) {
				wp_unschedule_event( $next_create_event->timestamp, 'idx_create_idx_pages' );
			}
			if ( $next_delete_event ) {
				wp_unschedule_event( $next_delete_event->timestamp, 'idx_delete_idx_pages' );
			}
			return;
		}

		// If current event interval/schedule is different than the saved value, update events and return early.
		if ( $next_create_event && $next_create_event->schedule !== $idx_cron_schedule ) {
			wp_clear_scheduled_hook( 'idx_create_idx_pages' );
			wp_clear_scheduled_hook( 'idx_delete_idx_pages' );
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_create_idx_pages' );
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_delete_idx_pages' );
			return;
		}

		// Schedule any missing events.
		if ( ! wp_next_scheduled( 'idx_create_idx_pages' ) ) {
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_create_idx_pages' );
		}

		if ( ! wp_next_scheduled( 'idx_delete_idx_pages' ) ) {
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_delete_idx_pages' );
		}
	}

	/**
	 * Unschedule_idx_page_update function.
	 * To be called on plugin deactivation.
	 *
	 * @access public
	 * @return void
	 */
	public static function unschedule_idx_page_update() {
		wp_clear_scheduled_hook( 'idx_create_idx_pages' );
		wp_clear_scheduled_hook( 'idx_delete_idx_pages' );
	}

	/**
	 * Register_idx_page_type function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_idx_page_type() {

		// post_type labels.
		$labels = array(
			'name'               => 'IDX Pages',
			'singular_name'      => 'IDX Page',
			'add_new'            => 'Add IDX Page',
			'add_new_item'       => 'Add New IDX Page',
			'edit_item'          => 'Edit IDX Page',
			'new_item'           => 'New IDX Page',
			'view_item'          => 'View IDX Page',
			'search_items'       => 'Search IDX Pages',
			'not_found'          => 'No IDX Pages found',
			'not_found_in_trash' => 'No IDX Pages found in Trash',
			'parent_item_colon'  => '',
			'parent'             => 'Parent IDX Page',
		);

		// disable ability to add new or delete IDX Pages.
		$capabilities = array(
			'publish_posts'       => 'publish_idx_pages',
			'edit_posts'          => 'edit_idx_pages',
			'edit_others_posts'   => 'edit_others_idx_pages',
			'delete_posts'        => false,
			'delete_others_posts' => false,
			'read_private_posts'  => 'read_private_idx_pages',
			'edit_post'           => 'edit_idx_page',
			'delete_post'         => 'edit_idx_page',
			'read_post'           => 'read_idx_pages',
			'create_posts'        => false,
		);

		$args = array(
			'label'             => 'IDX Pages',
			'labels'            => $labels,
			'public'            => true,
			'show_in_menu'      => 'idx-broker',
			'show_in_nav_menus' => true,
			'rewrite'           => false,
			'capabilities'      => $capabilities,
			'capability_type'   => array( 'idx_page', 'idx_pages' ),
			'supports'          => array( 'excerpt', 'thumbnail' ),
		);
		// register IDX Pages Post Type.
		register_post_type( 'idx_page', $args );

	}

	/**
	 * Manage_idx_page_capabilities function.
	 *
	 * @access public
	 * @return void
	 */
	public function manage_idx_page_capabilities() {
		// gets the role to add capabilities to.
		if ( current_user_can( 'edit_others_posts' ) ) {
			$current_user = wp_get_current_user();
			// replicate all the remapped capabilites from the custom post type.
			$caps = array(
				'edit_idx_page',
				'edit_idx_pages',
				'edit_others_idx_pages',
				'publish_idx_pages',
				'read_idx_pages',
			);
			// give all the capabilities to the administrator.
			foreach ( $caps as $cap ) {
				$current_user->add_cap( $cap );
			}
		}
	}

	/**
	 * Create_idx_pages function.
	 *
	 * @access public
	 * @return mixed
	 */
	public function create_idx_pages() {
		global $wpdb;
		
		// Only schedule update once IDX pages have UID.
		$uid_added = get_option( 'idx_added_uid_to_idx_pages' );
		
		// see if there's data from <=v1.3 in the database by checking for the existence of a posts_idx table
		$table_name =  $wpdb->prefix . 'posts_idx';
		$old_idx_table_found = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

		if ( empty( $uid_added ) && $old_idx_table_found ) {
			return wp_schedule_single_event( time(), 'idx_add_uid_to_idx_pages' );
		}

		// if we didn't see any posts_idx table, assume there's nothing to migrate
		if (!$old_idx_table_found) {
			update_option( 'idx_migrated_old_table', true, false );
			update_option( 'idx_add_uid_to_idx_pages', true, false );
		}

		$all_idx_pages = $this->get_all_api_idx_pages();
		if ( empty( $all_idx_pages ) ) {
			return;
		}

		$idx_page_chunks = array_chunk( $all_idx_pages, 200 );

		$existing_page_ids = $this->get_existing_idx_page_ids();

		foreach ( $idx_page_chunks as $idx_page_chunk ) {
			// for each chunk, create all idx pages within.
			$this->create_pages_from_chunk( $idx_page_chunk, $existing_page_ids );
		}
	}

	/**
	 * Create_pages_from_chunk function.
	 * Use the chunk to create all the pages within (chunk is 200).
	 *
	 * @access public
	 * @param array $idx_page_chunk - IDX Page Chunk.
	 * @param array $existing_page_ids - Existing page IDs.
	 *
	 * @return mixed
	 */
	public function create_pages_from_chunk( $idx_page_chunk, $existing_page_ids ) {
		
		// Prefetch an array of posts that might have to be updated or removed
		$posts = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
				'orderby' => 'date',
				'order' => 'ASC'
			)
		);

		foreach ( $idx_page_chunk as $link ) {
			if ( ! empty( $link->name ) ) {
				$name = $link->name;
			} elseif ( $link->linkTitle ) {
				$name = $link->linkTitle;
			}

			if ( ! in_array( $link->uid, $existing_page_ids ) ) {

				$url = apply_filters( 'impress_idx_page_insert_post_name', $link->url, $link );

				$post_info = array(
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      => $url,
					'post_content'   => '',
					'post_status'    => 'publish',
					'post_title'     => $name,
					'post_type'      => 'idx_page',
				);

				// filter sanitize_tite so it returns the raw title.
				add_filter( 'sanitize_title', array( $this, 'sanitize_title_filter' ), 10, 2 );
				$wp_id = wp_insert_post( $post_info );

				update_post_meta( $wp_id, 'idx_uid', $link->uid );
			} else {
				$this->find_and_update_post( $link, $name, $posts );
			}
		}
	}


	/**
	 * Find_and_update_post function.
	 *
	 * @access public
	 * @param mixed $link - Link.
	 * @param mixed $name - Name.
	 * @param WP_POST[] $post - an array of posts to search through from WP get_posts()
	 * @return void
	 */
	public function find_and_update_post( $link, $name, $posts ) {

		// It's possible to have duplicated pages in the database but only one will have the correct page name (slug) due to WordPress constraints.
		// Find all matching posts for this link and delete any useless duplicates
		$matchingPosts = array();

		foreach ( $posts as $post ) {
			if ( get_post_meta( $post->ID, 'idx_uid', true ) === $link->uid ) {
				array_push($matchingPosts, $post);
			}
		}

		$haveMatchingPost = false;
		// If we only found one matching post, try to update it
		if (count($matchingPosts) == 1)  {
			$haveMatchingPost = $this->update_post( $matchingPosts[0]->ID, $link, $name );
		}

		// If we found multiple matching posts, get rid of any duplicates before making an update
		if (count($matchingPosts) > 1) {
			$matchingPostWithName = null;
			foreach ( $matchingPosts as $matchingPost ) {
				// It's possible for a post to already have the expected post name (slug), if we find one with this characteristic it should be kept over others
				$nameMatches = $matchingPost->name == $link->url;
				if ($nameMatches) {
					error_log("impress find_and_update_post found a matching post for $link, $name: " . $matchingPost->ID);
					$matchingPostWithName = $matchingPost;
				}
			}

			// If we found an already post-name matching post, delete the rest
			if ($matchingPostWithName != null) {
				foreach ($matchingPosts as $matchingPost) {
					if ($matchingPost->ID != $matchingPostWithName->ID) {
						if (wp_delete_post($matchingPost->ID, true) == false) {
							error_log("impress find_and_update_post could not delete post " . $matchingPost->ID);
						};
					}
				}

				// Now update the matching post if necessary
				return $this->update_post( $matchingPost->ID, $link, $name );
			} else {
				// If we found duplicate matching posts but none with a matching slug, keep and update the first one and delete the rest.
				$chosenPost = array_shift($matchingPosts);
				foreach ($matchingPosts as $matchingPost) {
					if (wp_delete_post($matchingPost->ID, true) == false) {
						error_log("impress find_and_update_post could not delete post " . $matchingPost->ID);
					}
				}
				return $this->update_post( $chosenPost->ID, $link, $name );
			}

		}
	}

	/**
	 * Find_and_update_post function.
	 * Update the wp post info if it does not match api.
	 *
	 * @access public
	 * @param mixed $id - ID.
	 * @param mixed $link - Link.
	 * @param mixed $name - Name.
	 * @return false if no matching post was found, true if a post exists that matches the $link and $name or if a post was updated to match the saved link.
	 */
	public function update_post( $id, $link, $name ) {
		$post = get_post( $id );

		if ($post == null) {
			error_log( "impress update_post: Couldn't find a matching post for " . $link . " " . $name );
			return false;
		}

		// If name or URL are different, update them.
		// WordPress encodes some special characters in the post title, so decode the title to check
		$titleMatches = wp_specialchars_decode($post->post_title) == $name;

		if ( ( $post->post_name !== $link->url ) ||  !$titleMatches ) {
			// Keep old url from resurrecting.
			remove_action( 'save_post', array( $this, 'save_idx_page' ), 1 );

			$url = apply_filters( 'impress_idx_page_insert_post_name', $link->url, $link );

			$post_info = array(
				'ID'         => $id,
				'post_name'  => $url,
				'guid'       => $url,
				'post_title' => $name,
			);

			// Prevent WP URL from appearing in IDX page URL.
			add_filter( 'sanitize_title', array( $this, 'sanitize_title_filter' ), 10, 2 );

			if (wp_update_post( $post_info ) == 0) { 
				error_log( 'WP error when updating idx_page post ' . $post->post_name . ' url: ' . $link->url . ' ' . ' post_title: ' . $post->post_title . ' name: ' . $name );
				return false;
			} else {
				// Alternatively, could just let the program flow continue to the ending return statement but I feel this is more clear...
				return true;
			}
		}

		return true;
	}

	/**
	 * Sanitize_title_filter function.
	 * Removes sanitization on the post_name
	 *
	 * Without this the ":","/", and "." will be removed from post slugs
	 *
	 * @param string $title - Title.
	 * @param string $raw_title - Raw Title.
	 *
	 * @return string $raw_title title without sanitization applied
	 */
	public function sanitize_title_filter( $title, $raw_title ) {
		return $raw_title;
	}

	/**
	 * Get_all_api_idx_pages function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_all_api_idx_pages() {
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		$system_links = $this->idx_api->idx_api_get_systemlinks();

		if ( ! is_array( $system_links ) || ! is_array( $saved_links ) ) {
			return;
		}

		$idx_pages = array_merge( $saved_links, $system_links );
		return $idx_pages;
	}

	/**
	 * Get_all_api_idx_uids function.
	 *
	 * @access public
	 * @param array $idx_pages - Array of IDX Pages.
	 * @return array
	 */
	public function get_all_api_idx_uids( $idx_pages ) {
		$uids = [];
		if ( ! empty( $idx_pages ) && is_array( $idx_pages ) ) {
			foreach ( $idx_pages as $idx_page ) {
				$uids[] = $idx_page->uid;
			}
		}
		return $uids;
	}

	/**
	 * Deletes IDX pages that dont have a url or title matching a systemlink url or title
	 */
	public function delete_idx_pages() {
		// Only schedule update once IDX pages have UID.
		$uid_added = get_option( 'idx_added_uid_to_idx_pages' );
		if ( empty( $uid_added ) ) {
			return new Backward_Compatibility\Add_Uid_To_Idx_Pages();
		}

		$posts = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		$all_idx_pages = $this->get_all_api_idx_pages();
		$idx_page_uids = $this->get_all_api_idx_uids( $all_idx_pages );

		if ( empty( $all_idx_pages ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			// post_name oddly refers to permalink in the db
			// if an idx hosted page url or title has been changed,
			// delete the page from the wpdb
			// the updated page will be repopulated automatically.
			$wp_page_uid = get_post_meta( $post->ID, 'idx_uid', true );
			if ( ! in_array( $wp_page_uid, $idx_page_uids ) ) {
				wp_delete_post( $post->ID );
			}
		}
	}

	/**
	 * Keep post name (the idx url) from having slashes stripped out on save in UI
	 *
	 * @param string $post_id - Post ID.
	 * @return mixed
	 */
	public function save_idx_page( $post_id ) {
		$post = get_post( $post_id );
		// only affect idx_page post type.
		if ( 'idx_page' !== $post->post_type ) {
			return;
		}
		// prevent infinite loop.
		remove_action( 'save_post', array( $this, 'save_idx_page' ), 1 );
		// force post_name to not lose slashes.
		$update_to_post = array(
			'ID'        => $post_id,
			'post_name' => $post->guid,
		);

		add_filter( 'sanitize_title', array( $this, 'sanitize_title_filter' ), 10, 2 );
		// manually save post.
		wp_update_post( $update_to_post );

	}

	/**
	 * Disables appending of the site url to the post permalink
	 *
	 * @param mixed  $post_link - Post Link.
	 * @param object $post - Post.
	 * @return string $post_link
	 */
	public function post_type_link_filter_func( $post_link, $post ) {

		if ( 'idx_page' == $post->post_type ) {
			return $post->post_name;
		}

		return $post_link;
	}

	/**
	 * Deletes all posts of the "idx_page" post type
	 * This is called on uninstall of the plugin and when troubleshooting
	 *
	 * @return void
	 */
	public static function delete_all_idx_pages() {

		$posts = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID );
		}
	}

	/**
	 * Returns an array of existing idx page urls
	 *
	 * These are the page urls in the WordPress database
	 * not from the IDX dashboard
	 *
	 * @return array $existing urls of existing idx pages if any
	 */
	public function get_existing_idx_page_ids() {

		$posts = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);

		$existing = array();

		if ( empty( $posts ) ) {
			return $existing;
		}

		foreach ( $posts as $post ) {
			$existing[] = get_post_meta( $post->ID, 'idx_uid', true );
		}

		return $existing;
	}

	/**
	 * Show_idx_pages_metabox_by_default function.
	 *
	 * @access public
	 * @return void
	 */
	public function show_idx_pages_metabox_by_default() {

		$user = wp_get_current_user();

		$user_first_login = get_user_meta( $user->ID, 'idx_user_first_login', true );

		// Only update the user meta on the first login (after IDX features have been enabled).
		// This ensures that the user can hide the IDX Pages metabox again if they want.
		if ( ! empty( $user_first_login ) ) {
			return;
		}

		$hidden_metaboxes_on_nav_menus_page = (array) get_user_meta( $user->ID, 'metaboxhidden_nav-menus', true );

		foreach ( $hidden_metaboxes_on_nav_menus_page as $key => $value ) {

			if ( 'add-idx_page' === $value ) {
				unset( $hidden_metaboxes_on_nav_menus_page[ $key ] );
			}
		}

		update_user_meta( $user->ID, 'metaboxhidden_nav-menus', $hidden_metaboxes_on_nav_menus_page );

		// add a meta field to keep track of the first login.
		update_user_meta( $user->ID, 'idx_user_first_login', 'user_first_login_false' );
	}

	/**
	 * Display_wrapper_dropdown function.
	 *
	 * @access public
	 * @return mixed
	 */
	public function display_wrapper_dropdown() {
		// only show dropdown if Platinum account or not saved link
		// (Lite does not support saved link wrappers).
		if ( $this->idx_api->engage_account_type() ||
			! $this->is_saved_link( get_the_ID() ) ) {
			return true;
		}
	}

	/**
	 * Wrapper_page_dropdown function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_page_dropdown() {
		$post_id = get_the_ID();
		echo '<select class="idx-wrapper-page" name="idx-wrapper-page" style="width: 100%;">';
		echo '<option value="none"' .
		selected( 'none', get_post_meta( $post_id, 'idx-wrapper-page', true ) ) .
		'>None</option>';
		$args     = array(
			'numberposts' => -1,
			'post_type'   => 'idx-wrapper',
		);
		$wrappers = get_posts( $args );
		foreach ( $wrappers as $wrapper ) {
			$id   = $wrapper->ID;
			$name = $wrapper->post_title;
			echo '<option value="' . esc_attr( $id ) . '" ' . selected( $id, get_post_meta( $post_id, 'idx-wrapper-page', true ) ) . '>' . esc_html( $name ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Convert_uid_to_id function.
	 * dynamic wrapper requires the pageID, not the UID,
	 * so we must strip out the account number from the UID.
	 *
	 * @access public
	 * @param string $uid - UID.
	 * @return string
	 */
	public function convert_uid_to_id( $uid ) {
		return substr( $uid, strpos( $uid, '-' ) + 1 );
	}

	/**
	 * Wrapper_page_ui function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_page_ui() {
		// add metabox interface when editing an IDX page (with none as an option)
		// This UI should display the current wrapper set
		// when saving a post, save the meta of which wrapper is set.
		wp_nonce_field( 'idx-wrapper-page', 'idx-wrapper-page-nonce' );
		$this->wrapper_page_dropdown();
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'idx-wrapper', IMPRESS_IDX_URL . 'assets/js/idx-wrappers.min.js', [], '1.0.0', false );
	}

	/**
	 * Find_idx_url function.
	 *
	 * @access public
	 * @param mixed $post_id - Post ID.
	 * @return mixed
	 */
	public function find_idx_url( $post_id ) {
		$post         = get_post( $post_id );
		$url          = $post->post_name;
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		foreach ( $system_links as $link ) {
			if ( $link->url === $url ) {
				$uid  = $link->uid;
				$name = $link->name;
				$id   = $this->convert_uid_to_id( $uid );
				return $id;
			}
		}
		foreach ( $saved_links as $link ) {
			if ( $link->url === $url ) {
				$id   = $link->id;
				$name = $link->linkTitle;
				return $id;
			}
		}
	}

	/**
	 * Is_saved_link function.
	 *
	 * @access public
	 * @param mixed $post_id - Post ID.
	 * @return mixed
	 */
	public function is_saved_link( $post_id ) {
		$post        = get_post( $post_id );
		$url         = $post->post_name;
		$saved_links = $this->idx_api->idx_api_get_savedlinks();
		foreach ( $saved_links as $link ) {
			if ( $link->url === $url ) {
				return true;
			}
		}
	}

	/**
	 * Add_meta_box function.
	 *
	 * @access public
	 * @param mixed $post_type - Post Type.
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array( 'idx_page' ); // limit meta box to certain post types.
		if ( in_array( $post_type, $post_types ) && $this->display_wrapper_dropdown() ) {
			add_meta_box(
				'set_wrapper_page',
				'Apply Page Wrapper',
				array( $this, 'wrapper_page_ui' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * Set_wrapper_page function.
	 *
	 * @access public
	 * @param mixed $post_id - Post ID.
	 * @return mixed
	 */
	public function set_wrapper_page( $post_id ) {
		// saved idx page ID.
		if ( empty( $_POST ) || ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST['idx-wrapper-page-nonce'] )  ) {
			return $post_id;
		}

		// Validate and process request.
		if ( isset( $_POST['idx-wrapper-page-nonce'], $_POST['idx-wrapper-page'] ) || wp_verify_nonce( sanitize_key( $_POST['idx-wrapper-page-nonce'] ), 'idx-wrapper-page' ) ) {
			$meta_value = sanitize_text_field( wp_unslash( $_POST['idx-wrapper-page'] ) );
			// Find the IDX Page ID by matching URLs.
			$idx_page_id = $this->find_idx_url( $post_id );

			// do not update wrapper if wrapper is none.
			if ( 'none' === $meta_value ) {
				return;
			} elseif ( 'global' === $meta_value ) {
				$this->idx_api->set_wrapper( $idx_page_id, '' );
			}
			$wrapper_page_url = get_permalink( $meta_value );
			// logic for what type of idx page is in Idx_Api class.
			$this->idx_api->set_wrapper( $idx_page_id, $wrapper_page_url );
			update_post_meta( $post_id, 'idx-wrapper-page', $meta_value );
		}

	}

}
