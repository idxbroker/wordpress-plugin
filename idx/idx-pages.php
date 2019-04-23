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
		// deletes all IDX pages for troubleshooting purposes
		// $this->delete_all_idx_pages();
		add_option( 'idx_cron_schedule', 'threeminutes' );
		register_setting( 'idx-platinum-settings-group', 'idx_cron_schedule' );
		add_action( 'admin_init', array( $this, 'show_idx_pages_metabox_by_default' ) );
		add_filter( 'post_type_link', array( $this, 'post_type_link_filter_func' ), 10, 2 );
		add_filter( 'cron_schedules', array( $this, 'add_custom_schedule' ) );

		// register hooks for WP Cron to use to update IDX Pages
		add_action( 'idx_create_idx_pages', array( $this, 'create_idx_pages' ) );
		add_action( 'idx_delete_idx_pages', array( $this, 'delete_idx_pages' ) );

		add_action( 'init', array( $this, 'register_idx_page_type' ) );
		add_action( 'admin_init', array( $this, 'manage_idx_page_capabilities' ) );
		add_action( 'save_post', array( $this, 'save_idx_page' ), 1 );
		add_action( 'save_post', array( $this, 'set_wrapper_page' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// schedule an IDX page update via WP cron
		$this->schedule_idx_page_update();

		// for testing
		// add_action('wp_loaded', array($this, 'create_idx_pages'));
	}

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * add_custom_schedule function.
	 *
	 * @access public
	 * @param mixed $schedules
	 * @return void
	 */
	public function add_custom_schedule( $schedules ) {
		$schedules['threeminutes'] = array(
			'interval' => 60 * 3, // three minutes in seconds
			'display'  => 'Three Minutes',
		);

		return $schedules;
	}

	/** Schedule IDX Page update regularly. **/
	public function schedule_idx_page_update() {
		$idx_cron_schedule = get_option( 'idx_cron_schedule' );
		$next_create_event = wp_next_scheduled( 'idx_create_idx_pages' );
		$next_delete_event = wp_next_scheduled( 'idx_delete_idx_pages' );

		if ( wp_next_scheduled( 'idx_create_idx_pages' ) !== $idx_cron_schedule ) {
			wp_clear_scheduled_hook( 'idx_create_idx_pages' );
			wp_clear_scheduled_hook( 'idx_delete_idx_pages' );
			wp_unschedule_event( $next_create_event, 'idx_create_idx_pages' );
			wp_unschedule_event( $next_delete_event, 'idx_delete_idx_pages' );
		}

		if ( 'disabled' === $idx_cron_schedule ) {
			wp_unschedule_event( $next_create_event, 'idx_create_idx_pages' );
			wp_unschedule_event( $next_delete_event, 'idx_delete_idx_pages' );
			return;
		}
		if ( ! wp_next_scheduled( 'idx_create_idx_pages' ) ) {
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_create_idx_pages' );
		}
		if ( ! wp_next_scheduled( 'idx_delete_idx_pages' ) ) {
			wp_schedule_event( time(), $idx_cron_schedule, 'idx_delete_idx_pages' );
		}
	}
	// to be called on plugin deactivation
	public static function unschedule_idx_page_update() {
		wp_clear_scheduled_hook( 'idx_create_idx_pages' );
		wp_clear_scheduled_hook( 'idx_delete_idx_pages' );
	}

	/**
	 * register_idx_page_type function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_idx_page_type() {

		// post_type labels
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

		// disable ability to add new or delete IDX Pages
		$capabilities = array(
			'publish_posts'       => false,
			'edit_posts'          => 'edit_idx_pages',
			'edit_others_posts'   => 'edit_others_idx_pages',
			'delete_posts'        => false,
			'delete_others_posts' => false,
			'read_private_posts'  => 'read_private_idx_pages',
			'edit_post'           => 'edit_idx_page',
			'delete_post'         => false,
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
		// register IDX Pages Post Type
		register_post_type( 'idx_page', $args );

	}

	/**
	 * manage_idx_page_capabilities function.
	 *
	 * @access public
	 * @return void
	 */
	public function manage_idx_page_capabilities() {
		// gets the role to add capabilities to
		if ( current_user_can( 'edit_others_posts' ) ) {
			$current_user = wp_get_current_user();
			// replicate all the remapped capabilites from the custom post type
			$caps = array(
				'edit_idx_page',
				'edit_idx_pages',
				'edit_others_idx_pages',
				'publish_idx_pages',
				'read_idx_pages',
			);
			// give all the capabilities to the administrator
			foreach ( $caps as $cap ) {
				$current_user->add_cap( $cap );
			}
		}
	}

	/**
	 * create_idx_pages function.
	 *
	 * @access public
	 * @return void
	 */
	public function create_idx_pages() {
		// Only schedule update once IDX pages have UID
		$uid_added = get_option( 'idx_added_uid_to_idx_pages' );
		if ( empty( $uid_added ) ) {
			return wp_schedule_single_event( time(), 'idx_add_uid_to_idx_pages' );
		}

		$all_idx_pages = $this->get_all_api_idx_pages();
		if ( empty( $all_idx_pages ) ) {
			return;
		}

		$idx_page_chunks = array_chunk( $all_idx_pages, 200 );

		$existing_page_ids = $this->get_existing_idx_page_ids();

		foreach ( $idx_page_chunks as $idx_page_chunk ) {
			// for each chunk, create all idx pages within
			$this->create_pages_from_chunk( $idx_page_chunk, $existing_page_ids );
		}
	}

	// use the chunk to create all the pages within (chunk is 200)
	public function create_pages_from_chunk( $idx_page_chunk, $existing_page_ids ) {
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

				// filter sanitize_tite so it returns the raw title
				add_filter( 'sanitize_title', array( $this, 'sanitize_title_filter' ), 10, 2 );
				$wp_id = wp_insert_post( $post_info );

				update_post_meta( $wp_id, 'idx_uid', $link->uid );
			} else {
				$this->find_and_update_post( $link, $name );
			}
		}
	}

	/**
	 * find_and_update_post function.
	 *
	 * @access public
	 * @param mixed $link
	 * @param mixed $name
	 * @return void
	 */
	public function find_and_update_post( $link, $name ) {
		$posts = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
			)
		);
		foreach ( $posts as $post ) {
			if ( get_post_meta( $post->ID, 'idx_uid', true ) === $link->uid ) {

				$this->update_post( $post->ID, $link, $name );
			}
		}
	}

	// update the wp post info if it does not match api
	public function update_post( $id, $link, $name ) {
		$post = get_post( $id );
		// If name or URL are different, update them.
		if ( ( $post->post_name !== $link->url ) || ( $post->post_title !== $name ) ) {
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

			wp_update_post( $post_info );
		}
	}

	/**
	 * Removes sanitization on the post_name
	 *
	 * Without this the ":","/", and "." will be removed from post slugs
	 *
	 * @return string $raw_title title without sanitization applied
	 */
	public function sanitize_title_filter( $title, $raw_title ) {
		return $raw_title;
	}

	/**
	 * get_all_api_idx_pages function.
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
	 * get_all_api_idx_uids function.
	 *
	 * @access public
	 * @param mixed $idx_pages
	 * @return void
	 */
	public function get_all_api_idx_uids( $idx_pages ) {
		$uids = array();
		foreach ( $idx_pages as $idx_page ) {
			$uids[] = $idx_page->uid;
		}
		return $uids;
	}

	/**
	 * Deletes IDX pages that dont have a url or title matching a systemlink url or title
	 */
	public function delete_idx_pages() {
		// Only schedule update once IDX pages have UID
		$uid_added = get_option( 'idx_added_uid_to_idx_pages' );
		if ( empty( $uid_added ) ) {
			return $this->app->make( '\IDX\Backward_Compatibility\Add_Uid_To_Idx_Pages' );
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
			// the updated page will be repopulated automatically
			$wp_page_uid = get_post_meta( $post->ID, 'idx_uid', true );
			if ( ! in_array( $wp_page_uid, $idx_page_uids ) ) {
				wp_delete_post( $post->ID );
			}
		}
	}

	// Keep post name (the idx url) from having slashes stripped out on save in UI
	public function save_idx_page( $post_id ) {
		$post = get_post( $post_id );
		// only affect idx_page post type
		if ( $post->post_type !== 'idx_page' ) {
			return;
		}
		// prevent infinite loop
		remove_action( 'save_post', array( $this, 'save_idx_page' ), 1 );
		// force post_name to not lose slashes
		$update_to_post = array(
			'ID'        => $post_id,
			'post_name' => $post->guid,
		);

		add_filter( 'sanitize_title', array( $this, 'sanitize_title_filter' ), 10, 2 );
		// manually save post
		wp_update_post( $update_to_post );

	}

	/**
	 * Disables appending of the site url to the post permalink
	 *
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
	 * show_idx_pages_metabox_by_default function.
	 *
	 * @access public
	 * @return void
	 */
	public function show_idx_pages_metabox_by_default() {

		$user = wp_get_current_user();

		$user_first_login = get_user_meta( $user->ID, 'idx_user_first_login', true );

		// Only update the user meta on the first login (after IDX features have been enabled).
		// This ensures that the user can hide the IDX Pages metabox again if they want
		if ( ! empty( $user_first_login ) ) {
			return;
		}

		$hidden_metaboxes_on_nav_menus_page = (array) get_user_meta( $user->ID, 'metaboxhidden_nav-menus', true );

		foreach ( $hidden_metaboxes_on_nav_menus_page as $key => $value ) {

			if ( $value == 'add-idx_page' ) {
				unset( $hidden_metaboxes_on_nav_menus_page[ $key ] );
			}
		}

		update_user_meta( $user->ID, 'metaboxhidden_nav-menus', $hidden_metaboxes_on_nav_menus_page );

		// add a meta field to keep track of the first login
		update_user_meta( $user->ID, 'idx_user_first_login', 'user_first_login_false' );
	}

	/**
	 * display_wrapper_dropdown function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_wrapper_dropdown() {
		// only show dropdown if Platinum account or not saved link
		// (Lite does not support saved link wrappers)
		if ( $this->idx_api->platinum_account_type() ||
			! $this->is_saved_link( get_the_ID() ) ) {
			return true;
		}
	}

	/**
	 * wrapper_page_dropdown function.
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
			echo "<option value=\"$id\"" .
			selected( $id, get_post_meta( $post_id, 'idx-wrapper-page', true ) ) .
			">$name</option>";
		}
		echo '</select>';
	}

	// dynamic wrapper requires the pageID, not the UID,
	// so we must strip out the account number from the UID
	public function convert_uid_to_id( $uid ) {
		return substr( $uid, strpos( $uid, '-' ) + 1 );
	}

	/**
	 * wrapper_page_ui function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_page_ui() {
		// add metabox interface when editing an IDX page (with none as an option)
		// This UI should display the current wrapper set
		// when saving a post, save the meta of which wrapper is set
		wp_nonce_field( 'idx-wrapper-page', 'idx-wrapper-page-nonce' );
		$this->wrapper_page_dropdown();
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), '4.0.5', 'all' );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array( 'jquery' ), '4.0.5', true );
		wp_enqueue_script( 'idx-wrapper', plugins_url( '../assets/js/idx-wrappers.min.js', __FILE__ ) );
	}

	/**
	 * find_idx_url function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
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
	 * is_saved_link function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
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
	 * add_meta_box function.
	 *
	 * @access public
	 * @param mixed $post_type
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array( 'idx_page' ); // limit meta box to certain post types
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
	 * set_wrapper_page function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	public function set_wrapper_page( $post_id ) {
		// saved idx page ID
		if ( empty( $_POST ) ) {
			return;
		}
		if ( empty( $_POST['idx-wrapper-page'] ) ) {
			return;
		}
		if ( ! $this->verify_permissions() ) {
			return $post_id;
		}

		$meta_value = $_POST['idx-wrapper-page'];
		$meta_value = sanitize_text_field( $meta_value );
		// Find the IDX Page ID by matching URLs
		$idx_page_id = $this->find_idx_url( $post_id );

		// do not update wrapper if wrapper is none
		if ( $meta_value === 'none' ) {
			return;
		} elseif ( $meta_value === 'global' ) {
			$this->idx_api->set_wrapper( $idx_page_id, '' );
		}

		$wrapper_page_url = get_permalink( $meta_value );

		// logic for what type of idx page is in Idx_Api class
		$this->idx_api->set_wrapper( $idx_page_id, $wrapper_page_url );
		update_post_meta( $post_id, 'idx-wrapper-page', $meta_value );
	}

	/**
	 * verify_permissions function.
	 *
	 * @access public
	 * @return void
	 */
	public function verify_permissions() {
		// Check if our nonce is set.
		if ( ! isset( $_POST['idx-wrapper-page-nonce'] ) ) {
			return false;
		}
		$nonce = $_POST['idx-wrapper-page-nonce'];
		if ( ! wp_verify_nonce( $nonce, 'idx-wrapper-page' ) ) {
			return false;
		}
		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		return true;
	}
}
